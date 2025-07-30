#!/usr/bin/env python3
# ===== PROJECT SETUP SCRIPT =====

import os
import sys
import subprocess
import platform
from pathlib import Path

def print_header():
    """Print setup header"""
    print("=" * 60)
    print("Multi-Language Web Development Showcase - Setup")
    print("=" * 60)
    print()

def check_python_version():
    """Check if Python version is compatible"""
    if sys.version_info < (3, 8):
        print("❌ Python 3.8 or higher is required")
        print(f"Current version: {sys.version}")
        sys.exit(1)
    print(f"✅ Python {sys.version.split()[0]} detected")

def check_node_js():
    """Check if Node.js is installed"""
    try:
        result = subprocess.run(['node', '--version'], capture_output=True, text=True)
        if result.returncode == 0:
            print(f"✅ Node.js {result.stdout.strip()} detected")
            return True
        else:
            print("❌ Node.js not found")
            return False
    except FileNotFoundError:
        print("❌ Node.js not found")
        return False

def install_python_dependencies():
    """Install Python dependencies"""
    print("\n📦 Installing Python dependencies...")
    
    requirements_file = Path("python/requirements.txt")
    basic_requirements_file = Path("python/requirements-basic.txt")
    
    if not requirements_file.exists():
        print("❌ Python requirements.txt not found")
        return False
    
    try:
        # First, upgrade pip and setuptools to avoid compatibility issues
        print("🔄 Upgrading pip and setuptools...")
        subprocess.run([sys.executable, "-m", "pip", "install", "--user", "--upgrade", "pip", "setuptools", "wheel"], check=True)
        
        # Try installing dependencies
        print("📦 Installing project dependencies...")
        subprocess.run([sys.executable, "-m", "pip", "install", "--user", "-r", str(requirements_file)], check=True)
        print("✅ Python dependencies installed successfully")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ Failed to install Python dependencies: {e}")
        print("\n💡 Troubleshooting options:")
        print("1. Try upgrading pip manually:")
        print("   python -m pip install --user --upgrade pip setuptools wheel")
        print("2. Then install requirements manually:")
        print(f"   python -m pip install --user -r {requirements_file}")
        print("3. If numpy/pandas fails, try installing basic requirements:")
        if basic_requirements_file.exists():
            print(f"   python -m pip install --user -r {basic_requirements_file}")
            print("   (This installs only core Flask dependencies)")
        print("4. For Windows, you might need Visual Studio Build Tools")
        print("5. Try installing numpy separately first:")
        print("   python -m pip install --user numpy")
        print("6. Or use the manual installation script:")
        print("   python install_dependencies.py")
        
        # Offer to try basic requirements
        if basic_requirements_file.exists():
            try_basic = input("\nWould you like to try installing basic dependencies only? (y/n): ").lower().strip()
            if try_basic == 'y':
                try:
                    print("📦 Installing basic dependencies...")
                    subprocess.run([sys.executable, "-m", "pip", "install", "--user", "-r", str(basic_requirements_file)], check=True)
                    print("✅ Basic Python dependencies installed successfully")
                    print("⚠️  Note: Data visualization features will be limited")
                    return True
                except subprocess.CalledProcessError as e:
                    print(f"❌ Failed to install basic dependencies: {e}")
        
        return False

def setup_database():
    """Setup database"""
    print("\n🗄️  Setting up database...")
    
    # Check if MySQL is available
    try:
        subprocess.run(['mysql', '--version'], capture_output=True, check=True)
        print("✅ MySQL detected")
        
        # Import and run database setup
        try:
            import mysql.connector
            from mysql.connector import Error
            
            # Read database schema
            schema_file = Path("php/database.sql")
            if schema_file.exists():
                with open(schema_file, 'r') as f:
                    schema = f.read()
                
                # Split into individual statements
                statements = [stmt.strip() for stmt in schema.split(';') if stmt.strip()]
                
                # Connect to MySQL
                try:
                    connection = mysql.connector.connect(
                        host='localhost',
                        user='root',
                        password=''
                    )
                    
                    if connection.is_connected():
                        cursor = connection.cursor()
                        
                        # Execute each statement
                        for statement in statements:
                            if statement and not statement.startswith('--'):
                                try:
                                    cursor.execute(statement)
                                    print(f"✅ Executed: {statement[:50]}...")
                                except Error as e:
                                    if "database exists" not in str(e).lower():
                                        print(f"⚠️  Warning: {e}")
                        
                        connection.commit()
                        print("✅ Database setup completed")
                        
                except Error as e:
                    print(f"❌ Database connection failed: {e}")
                    print("Please ensure MySQL is running and credentials are correct")
                    
                finally:
                    if connection.is_connected():
                        cursor.close()
                        connection.close()
            else:
                print("❌ Database schema file not found")
                
        except ImportError:
            print("⚠️  mysql-connector-python not installed. Skipping database setup.")
            print("You can manually run the SQL file in php/database.sql")
            
    except (subprocess.CalledProcessError, FileNotFoundError):
        print("⚠️  MySQL not detected. Skipping database setup.")
        print("You can manually run the SQL file in php/database.sql")

def create_env_file():
    """Create environment file for Python Flask app"""
    print("\n🔧 Creating environment configuration...")
    
    env_file = Path("python/.env")
    if not env_file.exists():
        env_content = """# Flask Environment Configuration
FLASK_APP=app.py
FLASK_ENV=development
SECRET_KEY=your-secret-key-here-change-in-production
DATABASE_URL=sqlite:///dashboard.db

# Optional: MySQL configuration
# DATABASE_URL=mysql://username:password@localhost/dev_showcase
"""
        
        with open(env_file, 'w') as f:
            f.write(env_content)
        
        print("✅ Created python/.env file")
    else:
        print("✅ Environment file already exists")

def check_web_server():
    """Check if a web server is available"""
    print("\n🌐 Checking web server...")
    
    # Check for common web servers
    servers = {
        'apache': 'apache2',
        'nginx': 'nginx',
        'php': 'php'
    }
    
    found_servers = []
    for name, command in servers.items():
        try:
            subprocess.run([command, '--version'], capture_output=True, check=True)
            found_servers.append(name)
        except (subprocess.CalledProcessError, FileNotFoundError):
            pass
    
    if found_servers:
        print(f"✅ Web server detected: {', '.join(found_servers)}")
    else:
        print("⚠️  No web server detected")
        print("For PHP components, you'll need Apache/Nginx with PHP support")
        print("For Python components, Flask has a built-in development server")

def create_startup_scripts():
    """Create startup scripts for different components"""
    print("\n🚀 Creating startup scripts...")
    
    # Python Flask startup script
    if platform.system() == "Windows":
        flask_script = """@echo off
echo Starting Python Flask Dashboard...
cd python
python app.py
pause
"""
        with open("start_flask.bat", 'w') as f:
            f.write(flask_script)
        print("✅ Created start_flask.bat")
    else:
        flask_script = """#!/bin/bash
echo "Starting Python Flask Dashboard..."
cd python
python3 app.py
"""
        with open("start_flask.sh", 'w') as f:
            f.write(flask_script)
        os.chmod("start_flask.sh", 0o755)
        print("✅ Created start_flask.sh")

def print_next_steps():
    """Print next steps for the user"""
    print("\n" + "=" * 60)
    print("🎉 Setup Complete!")
    print("=" * 60)
    print("\nNext steps:")
    print("1. Test your installation:")
    print("   - Run: python python/test_installation.py")
    print("\n2. Start the Python Flask dashboard:")
    if platform.system() == "Windows":
        print("   - Run: start_flask.bat")
    else:
        print("   - Run: ./start_flask.sh")
    print("   - Or manually: cd python && python app.py")
    print("\n3. Access the applications:")
    print("   - Main site: http://localhost (via web server)")
    print("   - Python Dashboard: http://localhost:5000")
    print("   - PHP Blog: http://localhost/php/blog/")
    print("\n4. Database setup:")
    print("   - If MySQL setup failed, manually run php/database.sql")
    print("   - Default admin: admin@devshowcase.com / admin123")
    print("\n5. Configuration:")
    print("   - Edit php/config.php for database settings")
    print("   - Edit python/.env for Flask configuration")
    print("\nFor more information, see the README.md file")

def main():
    """Main setup function"""
    print_header()
    
    # Check requirements
    check_python_version()
    check_node_js()
    
    # Install dependencies
    if not install_python_dependencies():
        print("❌ Setup failed during dependency installation")
        sys.exit(1)
    
    # Setup components
    setup_database()
    create_env_file()
    check_web_server()
    create_startup_scripts()
    
    # Print next steps
    print_next_steps()

if __name__ == "__main__":
    main() 