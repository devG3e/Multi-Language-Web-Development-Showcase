#!/usr/bin/env python3
# ===== INSTALLATION TEST SCRIPT =====

import sys
import importlib

def test_import(module_name, description):
    """Test if a module can be imported"""
    try:
        importlib.import_module(module_name)
        print(f"✅ {description} ({module_name})")
        return True
    except ImportError as e:
        print(f"❌ {description} ({module_name}) - {e}")
        return False

def main():
    print("=" * 60)
    print("Python Installation Test")
    print("=" * 60)
    print(f"Python version: {sys.version}")
    print()
    
    # Test core dependencies
    print("Core Dependencies:")
    core_modules = [
        ('flask', 'Flask Web Framework'),
        ('flask_cors', 'Flask CORS'),
        ('flask_sqlalchemy', 'Flask SQLAlchemy'),
        ('werkzeug', 'Werkzeug'),
        ('sqlalchemy', 'SQLAlchemy'),
        ('dotenv', 'Python-dotenv'),
        ('requests', 'Requests HTTP Library'),
    ]
    
    core_success = True
    for module, description in core_modules:
        if not test_import(module, description):
            core_success = False
    
    print()
    print("Data Science Dependencies (Optional):")
    data_science_modules = [
        ('pandas', 'Pandas Data Analysis'),
        ('numpy', 'NumPy Numerical Computing'),
        ('matplotlib', 'Matplotlib Plotting'),
        ('seaborn', 'Seaborn Statistical Visualization'),
        ('plotly', 'Plotly Interactive Charts'),
    ]
    
    data_science_available = True
    for module, description in data_science_modules:
        if not test_import(module, description):
            data_science_available = False
    
    print()
    print("=" * 60)
    print("Test Results:")
    print("=" * 60)
    
    if core_success:
        print("✅ Core dependencies are working!")
        print("   The Flask dashboard should run properly.")
    else:
        print("❌ Some core dependencies are missing.")
        print("   Please run: pip install -r requirements-basic.txt")
    
    if data_science_available:
        print("✅ Data science libraries are available!")
        print("   All dashboard features will work.")
    else:
        print("⚠️  Some data science libraries are missing.")
        print("   Basic dashboard will work, but advanced features may be limited.")
        print("   To install: pip install -r requirements.txt")
    
    print()
    print("To start the dashboard:")
    print("   cd python")
    print("   python app.py")
    print("   Then visit: http://localhost:5000")

if __name__ == "__main__":
    main() 