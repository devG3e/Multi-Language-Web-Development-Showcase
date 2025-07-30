# XAMPP Setup Guide for Multi-Language Web Development Showcase

## Overview
This guide will help you set up XAMPP to run the PHP components of your Multi-Language Web Development Showcase project.

## Prerequisites
- Windows 10/11
- XAMPP installed (version 8.2.x recommended)
- Your project files

## Installation Steps

### 1. Install XAMPP
1. Download XAMPP from: https://www.apachefriends.org/download.html
2. Run the installer as Administrator
3. Choose installation directory (default: `C:\xampp`)
4. Select components: Apache, MySQL, PHP, phpMyAdmin
5. Complete the installation

### 2. Start XAMPP Services
1. Open XAMPP Control Panel
2. Start Apache and MySQL services
3. Verify both services are running (green status)

### 3. Project Setup
Your project has been automatically copied to: `C:\xampp\htdocs\dev-showcase`

### 4. Database Setup
The MySQL database `dev_showcase` has been created with the required schema.

## Access URLs

### Main Showcase
- **URL**: http://localhost/dev-showcase/
- **Description**: Complete showcase with all sections

### PHP Blog System
- **URL**: http://localhost/dev-showcase/php/blog/
- **Description**: PHP-powered blog with MySQL database

### Python Flask Dashboard
- **URL**: http://127.0.0.1:5000/
- **Description**: Python Flask analytics dashboard

### Individual Sections
- **Portfolio**: http://localhost/dev-showcase/#portfolio
- **Gallery**: http://localhost/dev-showcase/#gallery
- **Game**: http://localhost/dev-showcase/#game
- **Blog**: http://localhost/dev-showcase/#blog
- **Contact**: http://localhost/dev-showcase/#contact

## File Structure in XAMPP
```
C:\xampp\htdocs\dev-showcase\
├── index.html              # Main showcase page
├── css/                    # Stylesheets
├── js/                     # JavaScript files
├── images/                 # Image assets
├── php/                    # PHP components
│   ├── blog/              # Blog system
│   ├── config.php         # Database configuration
│   └── database.sql       # Database schema
└── python/                # Python Flask app
    ├── app.py             # Flask application
    ├── templates/         # Flask templates
    └── static/            # Flask static files
```

## Database Configuration
- **Host**: localhost
- **Database**: dev_showcase
- **Username**: root
- **Password**: (empty)
- **Port**: 3306

## Troubleshooting

### Apache Not Starting
1. Check if port 80 is in use
2. Run XAMPP as Administrator
3. Check Windows Firewall settings

### MySQL Not Starting
1. Check if port 3306 is in use
2. Ensure no other MySQL service is running
3. Check XAMPP error logs

### PHP Errors
1. Check PHP error logs in `C:\xampp\php\logs\`
2. Verify database connection in `php/config.php`
3. Ensure MySQL service is running

### Access Denied Errors
1. Run XAMPP Control Panel as Administrator
2. Check file permissions in `C:\xampp\htdocs\`
3. Verify Apache configuration

## Development Workflow

### For PHP Development
1. Edit files in `C:\xampp\htdocs\dev-showcase\php\`
2. Access via http://localhost/dev-showcase/php/
3. Use phpMyAdmin at http://localhost/phpmyadmin/ for database management

### For Python Development
1. Edit files in your original project directory
2. Run Flask app: `cd python && python app.py`
3. Access via http://127.0.0.1:5000/

### For Frontend Development
1. Edit files in your original project directory
2. Copy changes to `C:\xampp\htdocs\dev-showcase\` when ready to test
3. Access via http://localhost/dev-showcase/

## Security Notes
- This setup is for development only
- Change default passwords in production
- Disable error reporting in production
- Use HTTPS in production

## Next Steps
1. Test all sections of the showcase
2. Verify PHP blog functionality
3. Test database operations
4. Customize content and styling
5. Deploy to production server when ready

## Support
If you encounter issues:
1. Check XAMPP error logs
2. Verify service status in XAMPP Control Panel
3. Test database connectivity
4. Review PHP error logs 