# Spatie Laravel Permission Setup

## Overview
The Spatie Laravel Permission package has been successfully installed and configured for the CRM system.

## What was installed:
1. **Package**: `spatie/laravel-permission` (already in composer.json)
2. **Config file**: `config/permission.php` - Configuration for the permission system
3. **Database tables**: Permission tables created via migration
4. **User model updated**: Added `HasRoles` trait to `App\Models\User`

## Roles Created:
The following roles have been seeded into the database:
- Super Admin
- Admin  
- Sales Manager
- Sales
- Dataentry Manager
- Dataentry

## Default Users Created:
Two admin users have been created for testing:
1. **Admin User**
   - Email: admin@example.com
   - Password: password
   - Role: Admin

2. **Super Admin**
   - Email: superadmin@example.com  
   - Password: password
   - Role: Super Admin

## Usage Examples:

### Checking if a user has a role:
```php
$user = User::find(1);
if ($user->hasRole('Admin')) {
    // User is an admin
}
```

### Assigning a role to a user:
```php
$user = User::find(1);
$user->assignRole('Sales Manager');
```

### Getting users with specific role:
```php
$admins = User::role('Admin')->get();
```

### Creating permissions (if needed):
```php
use Spatie\Permission\Models\Permission;

Permission::create(['name' => 'edit users']);
Permission::create(['name' => 'delete users']);
```

### Assigning permissions to roles:
```php
$role = Role::findByName('Sales Manager');
$role->givePermissionTo('edit users');
```

## Database Tables Created:
- `permissions` - Stores permission definitions
- `roles` - Stores role definitions  
- `model_has_permissions` - Links users to permissions
- `model_has_roles` - Links users to roles
- `role_has_permissions` - Links roles to permissions

## Next Steps:
1. Define specific permissions for your CRM system
2. Assign permissions to appropriate roles
3. Use middleware to protect routes based on roles/permissions
4. Integrate with your Filament admin panel for role management

The permission system is now ready for use!
