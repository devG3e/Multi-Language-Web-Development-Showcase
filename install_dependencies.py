#!/usr/bin/env python3
# ===== MANUAL DEPENDENCY INSTALLATION =====

import subprocess
import sys
from pathlib import Path

def install_with_user_flag(requirements_file):
    """Install dependencies using --user flag"""
    try:
        print(f"📦 Installing from {requirements_file}...")
        subprocess.run([
            sys.executable, "-m", "pip", "install", "--user", "-r", requirements_file
        ], check=True)
        print(f"✅ Successfully installed dependencies from {requirements_file}")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ Failed to install from {requirements_file}: {e}")
        return False

def main():
    print("=" * 60)
    print("Manual Dependency Installation")
    print("=" * 60)
    print("This script installs Python dependencies using the --user flag")
    print("to avoid permission issues on Windows.")
    print()
    
    # Check if requirements files exist
    basic_req = Path("python/requirements-basic.txt")
    full_req = Path("python/requirements.txt")
    
    if not basic_req.exists() and not full_req.exists():
        print("❌ No requirements files found!")
        print("Please run this script from the project root directory.")
        return
    
    # Try basic requirements first
    if basic_req.exists():
        print("1. Installing basic dependencies (Flask only)...")
        if install_with_user_flag(basic_req):
            print("✅ Basic installation successful!")
        else:
            print("❌ Basic installation failed!")
            return
    
    # Try full requirements
    if full_req.exists():
        print("\n2. Installing full dependencies (including data science libraries)...")
        if install_with_user_flag(full_req):
            print("✅ Full installation successful!")
        else:
            print("⚠️  Full installation failed, but basic installation should work.")
    
    print("\n" + "=" * 60)
    print("Installation Complete!")
    print("=" * 60)
    print("\nNext steps:")
    print("1. Test your installation:")
    print("   python python/test_installation.py")
    print("\n2. Start the Flask dashboard:")
    print("   cd python")
    print("   python app.py")
    print("\n3. Visit: http://localhost:5000")

if __name__ == "__main__":
    main() 