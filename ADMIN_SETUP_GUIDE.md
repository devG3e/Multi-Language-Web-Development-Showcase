# 🔧 Admin System Setup Guide

## 🚨 **404 Error Resolution**

If you're getting a "404 Not Found" error when trying to access the admin login page, follow this step-by-step guide to fix it.

## 📋 **Prerequisites**

### 1. **Web Server Required**
You **MUST** access the admin panel through a web server, not by opening files directly in your browser.

**Options:**
- **XAMPP** (Recommended for Windows)
- **WAMP** (Windows)
- **MAMP** (Mac)
- **Built-in PHP Server** (Development only)

### 2. **MySQL Database**
- MySQL server must be running
- Database `dev_showcase` must exist
- Admin tables must be created

## 🚀 **Step-by-Step Setup**

### **Step 1: Install XAMPP (if not already installed)**

1. Download XAMPP from: https://www.apachefriends.org/
2. Install XAMPP to `C:\xampp\`
3. Start XAMPP Control Panel
4. Start **Apache** and **MySQL** services

### **Step 2: Deploy Your Project**

1. **Copy your project** to: `C:\xampp\htdocs\dev-showcase\`
2. **Verify the structure:**
   ```
   C:\xampp\htdocs\dev-showcase\
   ├── index.html
   ├── css\
   ├── js\
   ├── php\
   │   ├── admin\
   │   │   ├── login.php
   │   │   ├── dashboard.php
   │   │   ├── admin_auth.php
   │   │   └── ...
   │   ├── config.php
   │   └── database.sql
   └── ...
   ```

### **Step 3: Set Up Database**

1. **Open phpMyAdmin:** http://localhost/phpmyadmin
2. **Create database:**
   - Click "New" → Database name: `dev_showcase`
   - Click "Create"
3. **Import database schema:**
   - Select `dev_showcase` database
   - Click "Import"
   - Choose file: `C:\xampp\htdocs\dev-showcase\php\database.sql`
   - Click "Go"

### **Step 4: Test the Setup**

1. **Access the test page:** http://localhost/dev-showcase/php/admin/test.php
2. **Check all tests pass** (should show ✅ for all items)
3. **If any ❌ errors appear**, follow the troubleshooting steps below

### **Step 5: Access Admin Panel**

1. **Admin Login:** http://localhost/dev-showcase/php/admin/login.php
2. **Default Credentials:**
   - Username: `admin`
   - Password: `admin123`

## 🔍 **Troubleshooting**

### **404 Error Still Appears?**

1. **Check file paths:**
   ```
   http://localhost/dev-showcase/php/admin/login.php
   ```
   NOT:
   ```
   file:///C:/path/to/your/project/php/admin/login.php
   ```

2. **Verify XAMPP is running:**
   - Apache service must be started
   - Check XAMPP Control Panel

3. **Check file permissions:**
   - Ensure files are readable by web server

### **Database Connection Errors?**

1. **Check MySQL is running:**
   - XAMPP Control Panel → MySQL → Start

2. **Verify database exists:**
   - phpMyAdmin → Check for `dev_showcase` database

3. **Check database credentials in `php/config.php`:**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'dev_showcase');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

### **Admin Tables Missing?**

1. **Run database setup:**
   ```sql
   -- In phpMyAdmin, select dev_showcase database and run:
   SOURCE C:/xampp/htdocs/dev-showcase/php/database.sql;
   ```

2. **Or import manually:**
   - phpMyAdmin → Import → Select `database.sql` file

### **Default Admin User Missing?**

1. **Check if admin user exists:**
   ```sql
   SELECT * FROM users WHERE username = 'admin';
   ```

2. **Create admin user manually if missing:**
   ```sql
   INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
   ('admin', 'admin@devshowcase.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj4J/HS.iQeO', 'System Administrator', 'admin');
   ```

## 🌐 **Alternative: Built-in PHP Server**

If you don't want to use XAMPP, you can use PHP's built-in server:

### **Start PHP Server:**
```bash
# Navigate to your project directory
cd "C:\Users\Sinoxolo Jeremiah\Cursor.Source\Multi-Language Web Development Showcase"

# Start PHP server
php -S localhost:8000
```

### **Access URLs:**
- **Test Page:** http://localhost:8000/php/admin/test.php
- **Admin Login:** http://localhost:8000/php/admin/login.php

## 📱 **Access URLs Summary**

### **With XAMPP:**
- **Main Site:** http://localhost/dev-showcase/
- **Admin Test:** http://localhost/dev-showcase/php/admin/test.php
- **Admin Login:** http://localhost/dev-showcase/php/admin/login.php
- **Admin Dashboard:** http://localhost/dev-showcase/php/admin/dashboard.php

### **With Built-in PHP Server:**
- **Main Site:** http://localhost:8000/
- **Admin Test:** http://localhost:8000/php/admin/test.php
- **Admin Login:** http://localhost:8000/php/admin/login.php
- **Admin Dashboard:** http://localhost:8000/php/admin/dashboard.php

## 🔐 **Security Notes**

1. **Change default password** immediately after first login
2. **Use HTTPS** in production
3. **Secure your database** with strong passwords
4. **Regular backups** of admin data

## 📞 **Still Having Issues?**

1. **Run the test page:** http://localhost/dev-showcase/php/admin/test.php
2. **Check error logs:** XAMPP → Apache → Logs → error.log
3. **Verify all files exist** in correct locations
4. **Ensure MySQL is running** and accessible

---

**✅ Once setup is complete, you'll have a fully functional admin system with:**
- 3-admin constraint
- Industry-standard security
- Modern responsive interface
- Complete audit trail
- Activity logging 