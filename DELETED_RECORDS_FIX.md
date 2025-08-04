# Fix: Deleted Records Not Showing in Tables

## المشكلة
كان عدد السجلات المحذوفة يظهر في الـ badges الخاصة بالتابات، لكن السجلات نفسها لم تكن تظهر في الجدول عند الضغط على تاب "Deleted".

## السبب
1. **Query Issue**: الـ table method كان يستخدم `query(Model::query())` بدلاً من الاعتماد على getEloquentQuery
2. **Global Scope Issue**: List pages لم تكن تحتوي على getEloquentQuery لإزالة SoftDeletingScope و ActiveScope

## الحل المطبق

### 1. إزالة Query الثابت من Table Method
**قبل الإصلاح:**
```php
public function table(Table $table): Table
{
    return $table
        ->query(User::query()) // ❌ مشكلة: query ثابت
        ->columns([...])
}
```

**بعد الإصلاح:**
```php
public function table(Table $table): Table
{
    return $table
        ->columns([...]) // ✅ لا query ثابت - يعتمد على getEloquentQuery
}
```

### 2. إضافة getEloquentQuery في List Pages
تم إضافة method في كل List page:

```php
public function getEloquentQuery(): Builder
{
    return Model::query()
        ->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class)
        ->withoutGlobalScope(\App\Models\ActiveScope::class);
}
```

### 3. الملفات المُحدثة

#### UserResource/Pages/ListUsers.php:
- ✅ حذف `->query(User::query())`
- ✅ إضافة `getEloquentQuery()` method
- ✅ إضافة SoftDeletingScope import

#### RoleResource/Pages/ListRoles.php:
- ✅ حذف `->query(Role::query())`
- ✅ إضافة `getEloquentQuery()` method
- ✅ إضافة SoftDeletingScope import

#### PermissionResource/Pages/ListPermissions.php:
- ✅ حذف `->query(Permission::query())`
- ✅ إضافة `getEloquentQuery()` method
- ✅ إضافة SoftDeletingScope import

## كيف يعمل الآن

### عملية عرض السجلات:
1. **Tab Selection**: عند اختيار تاب معين
2. **Query Modification**: الـ `modifyQueryUsing` في getTabs() يعدل الـ query
3. **Global Scope Removal**: getEloquentQuery() يزيل الـ global scopes
4. **Result**: السجلات المحذوفة تظهر في تاب "Deleted"

### تفصيل التابات:
- **Active Tab**: `->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at'))`
- **Deleted Tab**: `->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())`

## النتيجة النهائية
- ✅ **Users**: الآن السجلات المحذوفة تظهر في تاب "Deleted Users"
- ✅ **Roles**: الآن الأدوار المحذوفة تظهر في تاب "Deleted Roles"  
- ✅ **Permissions**: الآن الصلاحيات المحذوفة تظهر في تاب "Deleted Permissions"

## تأكيد الإصلاح
للتأكد من أن الإصلاح نجح:
1. احذف أي record من Users/Roles/Permissions
2. اذهب للـ Resource المناسب
3. اضغط على تاب "Deleted"
4. يجب أن ترى الـ record المحذوف مع تاريخ الحذف ومن قام بحذفه

## Status: ✅ FIXED
المشكلة تم حلها في جميع الـ Resources الثلاثة.
