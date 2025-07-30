#!/usr/bin/env python3
"""
Comprehensive Test Suite for Multi-Language Web Development Showcase
Tests all components: Frontend, Python Flask, PHP, Ruby, C#, and Perl
"""

import unittest
import requests
import json
import os
import sys
import subprocess
import time
from pathlib import Path

class TestFrontend(unittest.TestCase):
    """Test frontend components and functionality"""
    
    def setUp(self):
        self.base_url = "http://localhost:5000"
        
    def test_main_page_loads(self):
        """Test that the main index.html page loads correctly"""
        response = requests.get(f"{self.base_url}/index.html")
        self.assertEqual(response.status_code, 200)
        self.assertIn("Multi-Language Web Development Showcase", response.text)
        
    def test_static_files_served(self):
        """Test that CSS and JS files are served correctly"""
        files_to_test = [
            "/css/main.css",
            "/css/responsive.css", 
            "/js/main.js",
            "/js/game.js",
            "/js/portfolio.js"
        ]
        
        for file_path in files_to_test:
            response = requests.get(f"{self.base_url}{file_path}")
            self.assertEqual(response.status_code, 200, f"Failed to load {file_path}")
            
    def test_navigation_links(self):
        """Test that all navigation links work"""
        sections = ["portfolio", "gallery", "game", "blog"]
        
        for section in sections:
            response = requests.get(f"{self.base_url}/{section}")
            self.assertEqual(response.status_code, 200, f"Failed to load {section}")
            
    def test_game_functionality(self):
        """Test memory game functionality"""
        response = requests.get(f"{self.base_url}/game")
        self.assertEqual(response.status_code, 200)
        self.assertIn("Memory Game", response.text)
        self.assertIn("game.js", response.text)

class TestPythonFlask(unittest.TestCase):
    """Test Python Flask dashboard functionality"""
    
    def setUp(self):
        self.base_url = "http://localhost:5000"
        
    def test_dashboard_loads(self):
        """Test that the Flask dashboard loads correctly"""
        response = requests.get(f"{self.base_url}/")
        self.assertEqual(response.status_code, 200)
        self.assertIn("Data Analysis Dashboard", response.text)
        
    def test_python_route_works(self):
        """Test that the /python/ route works (fixed issue)"""
        response = requests.get(f"{self.base_url}/python/")
        self.assertEqual(response.status_code, 200)
        self.assertIn("Data Analysis Dashboard", response.text)
        
    def test_api_endpoints(self):
        """Test Flask API endpoints"""
        endpoints = [
            "/api/health",
            "/api/stats",
            "/api/metrics?type=cpu&hours=24",
            "/api/page-views?days=7"
        ]
        
        for endpoint in endpoints:
            response = requests.get(f"{self.base_url}{endpoint}")
            self.assertEqual(response.status_code, 200, f"Failed to load {endpoint}")
            
    def test_database_operations(self):
        """Test database operations"""
        # Test that sample data is generated
        response = requests.get(f"{self.base_url}/api/stats")
        data = response.json()
        self.assertTrue(data['success'])
        self.assertIn('users', data['data'])
        self.assertIn('views', data['data'])

class TestPHPComponents(unittest.TestCase):
    """Test PHP blog and contact system"""
    
    def setUp(self):
        self.php_url = "http://localhost/dev-showcase"
        
    def test_php_blog_loads(self):
        """Test that PHP blog loads correctly"""
        try:
            response = requests.get(f"{self.php_url}/php/blog/index.php")
            self.assertEqual(response.status_code, 200)
            self.assertIn("Blog", response.text)
        except requests.exceptions.ConnectionError:
            self.skipTest("XAMPP not running or PHP not accessible")
            
    def test_php_contact_form(self):
        """Test PHP contact form"""
        try:
            response = requests.get(f"{self.php_url}/php/contact.php")
            self.assertEqual(response.status_code, 200)
            self.assertIn("Contact", response.text)
        except requests.exceptions.ConnectionError:
            self.skipTest("XAMPP not running or PHP not accessible")

class TestRubyRails(unittest.TestCase):
    """Test Ruby on Rails admin panel"""
    
    def test_rails_structure(self):
        """Test that Rails project structure exists"""
        rails_path = Path("ruby")
        self.assertTrue(rails_path.exists())
        self.assertTrue((rails_path / "Gemfile").exists())
        self.assertTrue((rails_path / "app").exists())
        
    def test_rails_controllers(self):
        """Test that Rails controllers exist"""
        controllers_path = Path("ruby/app/controllers/admin")
        self.assertTrue(controllers_path.exists())
        self.assertTrue((controllers_path / "base_controller.rb").exists())
        self.assertTrue((controllers_path / "dashboard_controller.rb").exists())

class TestCSharpASPNet(unittest.TestCase):
    """Test C# ASP.NET Task Management System"""
    
    def test_csharp_structure(self):
        """Test that C# project structure exists"""
        csharp_path = Path("csharp")
        self.assertTrue(csharp_path.exists())
        self.assertTrue((csharp_path / "TaskManager.sln").exists())
        self.assertTrue((csharp_path / "TaskManager").exists())
        
    def test_csharp_models(self):
        """Test that C# models exist"""
        models_path = Path("csharp/TaskManager/Models")
        self.assertTrue(models_path.exists())
        self.assertTrue((models_path / "Task.cs").exists())
        
    def test_csharp_controllers(self):
        """Test that C# controllers exist"""
        controllers_path = Path("csharp/TaskManager/Controllers")
        self.assertTrue(controllers_path.exists())
        self.assertTrue((controllers_path / "TasksController.cs").exists())

class TestPerlComponents(unittest.TestCase):
    """Test Perl Code Snippet Repository"""
    
    def test_perl_script_exists(self):
        """Test that Perl script exists"""
        perl_script = Path("perl/SnippetRepository.pl")
        self.assertTrue(perl_script.exists())
        
    def test_perl_script_executable(self):
        """Test that Perl script is executable"""
        perl_script = Path("perl/SnippetRepository.pl")
        if os.name != 'nt':  # Skip on Windows
            self.assertTrue(os.access(perl_script, os.X_OK))

class TestIntegration(unittest.TestCase):
    """Test integration between components"""
    
    def setUp(self):
        self.flask_url = "http://localhost:5000"
        self.php_url = "http://localhost/dev-showcase"
        
    def test_flask_serves_php_files(self):
        """Test that Flask can serve PHP files"""
        response = requests.get(f"{self.flask_url}/php/blog/index.php")
        self.assertEqual(response.status_code, 200)
        
    def test_cross_component_navigation(self):
        """Test navigation between different components"""
        # Test Flask dashboard link from main page
        response = requests.get(f"{self.flask_url}/index.html")
        self.assertIn('href="python/"', response.text)

class TestPerformance(unittest.TestCase):
    """Test performance and load times"""
    
    def setUp(self):
        self.base_url = "http://localhost:5000"
        
    def test_page_load_times(self):
        """Test that pages load within reasonable time"""
        pages = ["/", "/index.html", "/python/", "/game"]
        
        for page in pages:
            start_time = time.time()
            response = requests.get(f"{self.base_url}{page}")
            load_time = time.time() - start_time
            
            self.assertEqual(response.status_code, 200)
            self.assertLess(load_time, 5.0, f"Page {page} took too long to load: {load_time:.2f}s")

class TestSecurity(unittest.TestCase):
    """Test security aspects"""
    
    def setUp(self):
        self.base_url = "http://localhost:5000"
        
    def test_sql_injection_protection(self):
        """Test SQL injection protection"""
        malicious_inputs = [
            "'; DROP TABLE users; --",
            "' OR '1'='1",
            "'; INSERT INTO users VALUES ('hacker', 'password'); --"
        ]
        
        for malicious_input in malicious_inputs:
            response = requests.get(f"{self.base_url}/api/stats", params={"search": malicious_input})
            # Should not crash or expose sensitive data
            self.assertNotEqual(response.status_code, 500)
            
    def test_xss_protection(self):
        """Test XSS protection"""
        xss_payload = "<script>alert('xss')</script>"
        response = requests.get(f"{self.base_url}/api/stats", params={"search": xss_payload})
        self.assertNotIn("<script>", response.text)

class TestDeployment(unittest.TestCase):
    """Test deployment and configuration"""
    
    def test_environment_files(self):
        """Test that environment files exist"""
        env_files = [
            "python/.env",
            "csharp/TaskManager/appsettings.json",
            "ruby/.env"
        ]
        
        for env_file in env_files:
            if Path(env_file).exists():
                self.assertTrue(Path(env_file).is_file())
                
    def test_dependencies_files(self):
        """Test that dependency files exist"""
        dep_files = [
            "python/requirements.txt",
            "ruby/Gemfile",
            "csharp/TaskManager/TaskManager.csproj",
            "setup.py"
        ]
        
        for dep_file in dep_files:
            self.assertTrue(Path(dep_file).exists(), f"Missing dependency file: {dep_file}")

def run_flask_server():
    """Start Flask server for testing"""
    try:
        # Check if Flask is already running
        response = requests.get("http://localhost:5000/api/health", timeout=2)
        if response.status_code == 200:
            print("Flask server already running")
            return True
    except:
        pass
    
    # Start Flask server
    try:
        os.chdir("python")
        process = subprocess.Popen([sys.executable, "app.py"], 
                                 stdout=subprocess.PIPE, 
                                 stderr=subprocess.PIPE)
        time.sleep(3)  # Wait for server to start
        return True
    except Exception as e:
        print(f"Failed to start Flask server: {e}")
        return False

def main():
    """Main test runner"""
    print("🚀 Starting Multi-Language Web Development Showcase Test Suite")
    print("=" * 60)
    
    # Start Flask server if not running
    if not run_flask_server():
        print("❌ Failed to start Flask server. Some tests will be skipped.")
    
    # Run tests
    loader = unittest.TestLoader()
    suite = unittest.TestSuite()
    
    # Add test classes
    test_classes = [
        TestFrontend,
        TestPythonFlask,
        TestPHPComponents,
        TestRubyRails,
        TestCSharpASPNet,
        TestPerlComponents,
        TestIntegration,
        TestPerformance,
        TestSecurity,
        TestDeployment
    ]
    
    for test_class in test_classes:
        tests = loader.loadTestsFromTestCase(test_class)
        suite.addTests(tests)
    
    # Run tests
    runner = unittest.TextTestRunner(verbosity=2)
    result = runner.run(suite)
    
    # Print summary
    print("\n" + "=" * 60)
    print("📊 Test Results Summary")
    print("=" * 60)
    print(f"Tests run: {result.testsRun}")
    print(f"Failures: {len(result.failures)}")
    print(f"Errors: {len(result.errors)}")
    print(f"Success rate: {((result.testsRun - len(result.failures) - len(result.errors)) / result.testsRun * 100):.1f}%")
    
    if result.failures:
        print("\n❌ Failures:")
        for test, traceback in result.failures:
            print(f"  - {test}: {traceback.split('AssertionError:')[-1].strip()}")
    
    if result.errors:
        print("\n❌ Errors:")
        for test, traceback in result.errors:
            print(f"  - {test}: {traceback.split('Exception:')[-1].strip()}")
    
    if result.wasSuccessful():
        print("\n✅ All tests passed!")
        return 0
    else:
        print("\n❌ Some tests failed!")
        return 1

if __name__ == "__main__":
    sys.exit(main()) 