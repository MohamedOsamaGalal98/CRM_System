# CRM System

<p align="center">
<img src="https://img.shields.io/badge/Laravel-12.21.0-red?style=for-the-badge&logo=laravel" alt="Laravel Version">
<img src="https://img.shields.io/badge/Filament-v3.3-orange?style=for-the-badge&logo=php" alt="Filament Version">
<img src="https://img.shields.io/badge/PHP-8.2+-blue?style=for-the-badge&logo=php" alt="PHP Version">
<img src="https://img.shields.io/badge/Database-SQLite-green?style=for-the-badge&logo=sqlite" alt="Database">
</p>

## 🚀 About This Project

CRM is a modern Customer Relationship Management system built with **Laravel 12** and **Filament v3**. This system provides a powerful admin panel for managing customers, leads, sales, and business operations with an intuitive and elegant interface.

## ✨ Features

- 🔐 **Secure Authentication System** - Built-in login and user management
- 📊 **Modern Admin Dashboard** - Powered by Filament v3
- 👥 **User Management** - Complete user roles and permissions
- 🎨 **Beautiful UI/UX** - Clean and responsive design
- 📱 **Mobile Responsive** - Works seamlessly on all devices
- 🔍 **Advanced Filtering** - Powerful search and filter capabilities
- 📈 **Analytics & Reports** - Comprehensive business insights
- 🛡️ **Security First** - Built with Laravel's security best practices

## 🛠️ Tech Stack

- **Backend**: Laravel 12.21.0
- **Admin Panel**: Filament v3.3
- **Database**: SQLite (Development) / MySQL (Production)
- **Frontend**: Blade Templates with Alpine.js
- **Styling**: Tailwind CSS
- **Authentication**: Laravel's built-in authentication

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQLite extension for PHP

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/MohamedOsamaGalal98/CRM_System.git
cd CRM_System
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
php artisan migrate
```

### 5. Create Admin User
```bash
php artisan make:filament-user
```

### 6. Start Development Server
```bash
php artisan serve
```

## 🔑 Admin Access

After installation, you can access the admin panel:

- **Admin Panel**: `http://localhost:8000/admin`
- **Login**: `http://localhost:8000/admin/login`

**Default Admin Credentials:**
- **Email**: `admin@admin.com`
- **Password**: `admin123`

## 📁 Project Structure

```
├── app/
│   ├── Filament/          # Filament Resources, Pages, Widgets
│   ├── Http/              # Controllers, Middleware, Requests
│   ├── Models/            # Eloquent Models
│   └── Providers/         # Service Providers
├── database/
│   ├── migrations/        # Database Migrations
│   └── seeders/          # Database Seeders
├── resources/
│   ├── views/            # Blade Templates
│   └── css/              # Stylesheets
└── routes/               # Route Definitions
```

## 🚀 Development

### Running the Application
```bash
# Start the development server
php artisan serve

# Run database migrations
php artisan migrate

# Clear application cache
php artisan optimize:clear
```

### Building Assets
```bash
# Development
npm run dev

# Production
npm run build
```

## 🤝 Contributing

We welcome contributions to improve the CRM system:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Development Team

- **Backend Team**: MohamedOsamaGalal98
- **Repository**: [CRM_System](https://github.com/MohamedOsamaGalal98/CRM_System)

## 📞 Support

For support and questions, please open an issue on GitHub or contact the development team.

---

<p align="center">
Made with ❤️ by Eng/Mohamed Osama Galal
</p>
