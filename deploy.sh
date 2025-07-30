#!/bin/bash

# Multi-Language Web Development Showcase - Deployment Script
# This script deploys all components of the project

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="Multi-Language Web Development Showcase"
FLASK_PORT=5000
XAMPP_PORT=80
RUBY_PORT=3000
CSHARP_PORT=5001
PERL_PORT=8080

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}"
}

# Check if running as root (for some operations)
check_root() {
    if [[ $EUID -eq 0 ]]; then
        warn "Running as root. Some operations may require elevated privileges."
    fi
}

# Check system requirements
check_requirements() {
    log "Checking system requirements..."
    
    # Check Python
    if command -v python3 &> /dev/null; then
        PYTHON_VERSION=$(python3 --version | cut -d' ' -f2)
        log "Python $PYTHON_VERSION found"
    else
        error "Python 3 is required but not installed"
        exit 1
    fi
    
    # Check pip
    if command -v pip3 &> /dev/null; then
        log "pip3 found"
    else
        error "pip3 is required but not installed"
        exit 1
    fi
    
    # Check Node.js (for some frontend tools)
    if command -v node &> /dev/null; then
        NODE_VERSION=$(node --version)
        log "Node.js $NODE_VERSION found"
    else
        warn "Node.js not found (optional for some features)"
    fi
    
    # Check Git
    if command -v git &> /dev/null; then
        log "Git found"
    else
        warn "Git not found (optional for version control)"
    fi
}

# Deploy Python Flask Application
deploy_python() {
    log "Deploying Python Flask Application..."
    
    cd python
    
    # Create virtual environment if it doesn't exist
    if [[ ! -d "venv" ]]; then
        log "Creating Python virtual environment..."
        python3 -m venv venv
    fi
    
    # Activate virtual environment
    source venv/bin/activate
    
    # Upgrade pip
    log "Upgrading pip..."
    pip install --upgrade pip
    
    # Install dependencies
    log "Installing Python dependencies..."
    if [[ -f "requirements.txt" ]]; then
        pip install -r requirements.txt
    else
        warn "requirements.txt not found, installing basic dependencies..."
        pip install Flask Flask-CORS Flask-SQLAlchemy python-dotenv
    fi
    
    # Create .env file if it doesn't exist
    if [[ ! -f ".env" ]]; then
        log "Creating .env file..."
        cat > .env << EOF
FLASK_APP=app.py
FLASK_ENV=development
SECRET_KEY=dev-secret-key-change-in-production
DATABASE_URL=sqlite:///dashboard.db
EOF
    fi
    
    # Initialize database
    log "Initializing database..."
    python -c "
from app import app, db
with app.app_context():
    db.create_all()
    print('Database initialized successfully')
"
    
    cd ..
    log "Python Flask application deployed successfully"
}

# Deploy PHP Components (XAMPP)
deploy_php() {
    log "Deploying PHP Components..."
    
    # Check if XAMPP is installed
    if [[ -d "/opt/lampp" ]] || [[ -d "C:/xampp" ]]; then
        log "XAMPP found"
        
        # Copy PHP files to XAMPP htdocs
        if [[ -d "/opt/lampp/htdocs" ]]; then
            # Linux
            sudo cp -r php /opt/lampp/htdocs/dev-showcase
            log "PHP files copied to /opt/lampp/htdocs/dev-showcase"
        elif [[ -d "C:/xampp/htdocs" ]]; then
            # Windows
            cp -r php C:/xampp/htdocs/dev-showcase
            log "PHP files copied to C:/xampp/htdocs/dev-showcase"
        fi
        
        # Create MySQL database
        log "Setting up MySQL database..."
        if command -v mysql &> /dev/null; then
            mysql -u root -e "CREATE DATABASE IF NOT EXISTS dev_showcase;"
            if [[ -f "php/database.sql" ]]; then
                mysql -u root dev_showcase < php/database.sql
                log "Database schema imported"
            fi
        else
            warn "MySQL not found. Please import database manually."
        fi
    else
        warn "XAMPP not found. Please install XAMPP to run PHP components."
        info "PHP components will be available at: http://localhost/dev-showcase"
    fi
}

# Deploy Ruby on Rails Application
deploy_ruby() {
    log "Deploying Ruby on Rails Application..."
    
    cd ruby
    
    # Check if Ruby is installed
    if command -v ruby &> /dev/null; then
        RUBY_VERSION=$(ruby --version | cut -d' ' -f2)
        log "Ruby $RUBY_VERSION found"
        
        # Check if Rails is installed
        if command -v rails &> /dev/null; then
            log "Rails found"
            
            # Install dependencies
            if [[ -f "Gemfile" ]]; then
                log "Installing Ruby dependencies..."
                bundle install
                
                # Setup database
                log "Setting up Rails database..."
                rails db:create db:migrate db:seed
                
                log "Rails application deployed successfully"
            else
                warn "Gemfile not found"
            fi
        else
            warn "Rails not found. Please install Rails to run Ruby components."
        fi
    else
        warn "Ruby not found. Please install Ruby to run Rails components."
    fi
    
    cd ..
}

# Deploy C# ASP.NET Application
deploy_csharp() {
    log "Deploying C# ASP.NET Application..."
    
    cd csharp
    
    # Check if .NET is installed
    if command -v dotnet &> /dev/null; then
        DOTNET_VERSION=$(dotnet --version)
        log ".NET $DOTNET_VERSION found"
        
        # Restore dependencies
        log "Restoring .NET dependencies..."
        dotnet restore
        
        # Build application
        log "Building C# application..."
        dotnet build
        
        # Run database migrations
        log "Running database migrations..."
        dotnet ef database update
        
        log "C# ASP.NET application deployed successfully"
    else
        warn ".NET not found. Please install .NET SDK to run C# components."
    fi
    
    cd ..
}

# Deploy Perl Application
deploy_perl() {
    log "Deploying Perl Application..."
    
    cd perl
    
    # Check if Perl is installed
    if command -v perl &> /dev/null; then
        PERL_VERSION=$(perl --version | head -n2 | tail -n1 | cut -d' ' -f9)
        log "Perl $PERL_VERSION found"
        
        # Install Perl dependencies
        log "Installing Perl dependencies..."
        if command -v cpanm &> /dev/null; then
            cpanm --installdeps .
        elif command -v cpan &> /dev/null; then
            cpan DBI DBD::mysql CGI JSON Digest::SHA File::Path File::Copy
        else
            warn "CPAN/CPANM not found. Please install Perl dependencies manually."
        fi
        
        # Make script executable
        chmod +x SnippetRepository.pl
        
        log "Perl application deployed successfully"
    else
        warn "Perl not found. Please install Perl to run Perl components."
    fi
    
    cd ..
}

# Start all services
start_services() {
    log "Starting all services..."
    
    # Start Flask (Python)
    log "Starting Flask server..."
    cd python
    source venv/bin/activate
    nohup python app.py > flask.log 2>&1 &
    FLASK_PID=$!
    echo $FLASK_PID > flask.pid
    cd ..
    
    # Start XAMPP (if available)
    if [[ -d "/opt/lampp" ]]; then
        log "Starting XAMPP..."
        sudo /opt/lampp/lampp start
    elif [[ -d "C:/xampp" ]]; then
        log "Starting XAMPP (Windows)..."
        # Windows XAMPP should be started manually
        info "Please start XAMPP manually on Windows"
    fi
    
    # Start Rails (if available)
    if command -v rails &> /dev/null; then
        log "Starting Rails server..."
        cd ruby
        nohup rails server -p $RUBY_PORT > rails.log 2>&1 &
        RAILS_PID=$!
        echo $RAILS_PID > rails.pid
        cd ..
    fi
    
    # Start .NET (if available)
    if command -v dotnet &> /dev/null; then
        log "Starting .NET application..."
        cd csharp/TaskManager
        nohup dotnet run --urls "http://localhost:$CSHARP_PORT" > dotnet.log 2>&1 &
        DOTNET_PID=$!
        echo $DOTNET_PID > dotnet.pid
        cd ../..
    fi
    
    # Start Perl (if available)
    if command -v perl &> /dev/null; then
        log "Starting Perl application..."
        cd perl
        nohup perl SnippetRepository.pl > perl.log 2>&1 &
        PERL_PID=$!
        echo $PERL_PID > perl.pid
        cd ..
    fi
    
    log "All services started"
}

# Stop all services
stop_services() {
    log "Stopping all services..."
    
    # Stop Flask
    if [[ -f "python/flask.pid" ]]; then
        kill $(cat python/flask.pid) 2>/dev/null || true
        rm python/flask.pid
    fi
    
    # Stop Rails
    if [[ -f "ruby/rails.pid" ]]; then
        kill $(cat ruby/rails.pid) 2>/dev/null || true
        rm ruby/rails.pid
    fi
    
    # Stop .NET
    if [[ -f "csharp/TaskManager/dotnet.pid" ]]; then
        kill $(cat csharp/TaskManager/dotnet.pid) 2>/dev/null || true
        rm csharp/TaskManager/dotnet.pid
    fi
    
    # Stop Perl
    if [[ -f "perl/perl.pid" ]]; then
        kill $(cat perl/perl.pid) 2>/dev/null || true
        rm perl/perl.pid
    fi
    
    # Stop XAMPP
    if [[ -d "/opt/lampp" ]]; then
        sudo /opt/lampp/lampp stop
    fi
    
    log "All services stopped"
}

# Health check
health_check() {
    log "Performing health check..."
    
    # Check Flask
    if curl -s http://localhost:$FLASK_PORT/api/health > /dev/null; then
        log "✅ Flask server is running"
    else
        warn "❌ Flask server is not responding"
    fi
    
    # Check XAMPP
    if curl -s http://localhost:$XAMPP_PORT > /dev/null; then
        log "✅ XAMPP is running"
    else
        warn "❌ XAMPP is not responding"
    fi
    
    # Check Rails
    if curl -s http://localhost:$RUBY_PORT > /dev/null; then
        log "✅ Rails server is running"
    else
        warn "❌ Rails server is not responding"
    fi
    
    # Check .NET
    if curl -s http://localhost:$CSHARP_PORT > /dev/null; then
        log "✅ .NET application is running"
    else
        warn "❌ .NET application is not responding"
    fi
}

# Show deployment information
show_info() {
    log "Deployment Information"
    echo "===================="
    echo "Project: $PROJECT_NAME"
    echo ""
    echo "Access URLs:"
    echo "  Main Showcase: http://localhost:$FLASK_PORT"
    echo "  Python Dashboard: http://localhost:$FLASK_PORT/python/"
    echo "  PHP Blog: http://localhost:$XAMPP_PORT/dev-showcase/php/blog/"
    echo "  Rails Admin: http://localhost:$RUBY_PORT/admin"
    echo "  .NET Tasks: http://localhost:$CSHARP_PORT"
    echo "  Perl Snippets: http://localhost:$PERL_PORT"
    echo ""
    echo "Log Files:"
    echo "  Flask: python/flask.log"
    echo "  Rails: ruby/rails.log"
    echo "  .NET: csharp/TaskManager/dotnet.log"
    echo "  Perl: perl/perl.log"
    echo ""
    echo "To stop all services: ./deploy.sh stop"
    echo "To check health: ./deploy.sh health"
}

# Main deployment function
main() {
    case "${1:-deploy}" in
        "deploy")
            log "Starting deployment of $PROJECT_NAME"
            check_root
            check_requirements
            deploy_python
            deploy_php
            deploy_ruby
            deploy_csharp
            deploy_perl
            start_services
            sleep 5
            health_check
            show_info
            log "Deployment completed successfully!"
            ;;
        "start")
            start_services
            ;;
        "stop")
            stop_services
            ;;
        "restart")
            stop_services
            sleep 2
            start_services
            ;;
        "health")
            health_check
            ;;
        "info")
            show_info
            ;;
        "clean")
            log "Cleaning up..."
            stop_services
            rm -f */flask.pid */rails.pid */dotnet.pid */perl.pid
            rm -f */*.log
            log "Cleanup completed"
            ;;
        *)
            echo "Usage: $0 {deploy|start|stop|restart|health|info|clean}"
            echo ""
            echo "Commands:"
            echo "  deploy  - Deploy all components (default)"
            echo "  start   - Start all services"
            echo "  stop    - Stop all services"
            echo "  restart - Restart all services"
            echo "  health  - Check health of all services"
            echo "  info    - Show deployment information"
            echo "  clean   - Clean up log files and PIDs"
            exit 1
            ;;
    esac
}

# Run main function
main "$@" 