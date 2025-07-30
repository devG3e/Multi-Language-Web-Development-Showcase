#!/usr/bin/env python3
# ===== PYTHON FLASK DASHBOARD =====

from flask import Flask, render_template, jsonify, request, send_from_directory, send_file
from flask_cors import CORS
from flask_sqlalchemy import SQLAlchemy
import os
import json
import random
from datetime import datetime, timedelta, timezone
# Optional data science libraries
try:
    import pandas as pd
    import numpy as np
    DATA_SCIENCE_AVAILABLE = True
except ImportError:
    DATA_SCIENCE_AVAILABLE = False
    print("⚠️  pandas/numpy not available. Data visualization features will be limited.")
from werkzeug.middleware.proxy_fix import ProxyFix

# Initialize Flask app
app = Flask(__name__)
app.wsgi_app = ProxyFix(app.wsgi_app, x_proto=1, x_host=1)

# Configuration
app.config['SECRET_KEY'] = os.environ.get('SECRET_KEY', 'dev-secret-key-change-in-production')
app.config['SQLALCHEMY_DATABASE_URI'] = os.environ.get('DATABASE_URL', 'sqlite:///dashboard.db')
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

# Enable CORS
CORS(app)

# Initialize database
db = SQLAlchemy(app)

# Database Models
class User(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    created_at = db.Column(db.DateTime, default=lambda: datetime.now(timezone.utc))
    last_login = db.Column(db.DateTime)
    is_active = db.Column(db.Boolean, default=True)

class PageView(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    page = db.Column(db.String(100), nullable=False)
    user_agent = db.Column(db.String(500))
    ip_address = db.Column(db.String(45))
    timestamp = db.Column(db.DateTime, default=lambda: datetime.now(timezone.utc))
    session_id = db.Column(db.String(100))

class SystemMetric(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    metric_type = db.Column(db.String(50), nullable=False)  # cpu, memory, disk, network
    value = db.Column(db.Float, nullable=False)
    timestamp = db.Column(db.DateTime, default=lambda: datetime.now(timezone.utc))

# Sample data generation functions
def generate_sample_data():
    """Generate sample data for demonstration"""
    
    # Generate sample users
    users_data = [
        {'username': 'john_doe', 'email': 'john@example.com'},
        {'username': 'jane_smith', 'email': 'jane@example.com'},
        {'username': 'bob_wilson', 'email': 'bob@example.com'},
        {'username': 'alice_brown', 'email': 'alice@example.com'},
        {'username': 'charlie_davis', 'email': 'charlie@example.com'}
    ]
    
    for user_data in users_data:
        user = User.query.filter_by(username=user_data['username']).first()
        if not user:
            user = User(**user_data)
            db.session.add(user)
    
    # Generate sample page views
    pages = ['home', 'portfolio', 'blog', 'gallery', 'game', 'dashboard', 'contact']
    for _ in range(100):
        page_view = PageView(
            page=random.choice(pages),
            user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ip_address=f'192.168.1.{random.randint(1, 254)}',
            timestamp=datetime.now(timezone.utc) - timedelta(days=random.randint(0, 30))
        )
        db.session.add(page_view)
    
    # Generate sample system metrics
    for i in range(100):
        timestamp = datetime.now(timezone.utc) - timedelta(hours=i)
        
        # CPU usage
        cpu_metric = SystemMetric(
            metric_type='cpu',
            value=random.uniform(20, 80),
            timestamp=timestamp
        )
        db.session.add(cpu_metric)
        
        # Memory usage
        memory_metric = SystemMetric(
            metric_type='memory',
            value=random.uniform(40, 90),
            timestamp=timestamp
        )
        db.session.add(memory_metric)
        
        # Disk usage
        disk_metric = SystemMetric(
            metric_type='disk',
            value=random.uniform(30, 85),
            timestamp=timestamp
        )
        db.session.add(disk_metric)
    
    db.session.commit()

# Routes
@app.route('/')
def dashboard():
    """Main dashboard page"""
    return render_template('dashboard.html')

@app.route('/python/')
@app.route('/python')
def python_dashboard():
    """Python dashboard - redirect to main dashboard"""
    return render_template('dashboard.html')

@app.route('/index.html')
def index():
    """Main showcase index page"""
    return send_from_directory('..', 'index.html')

@app.route('/portfolio')
def portfolio():
    """Portfolio section"""
    return send_from_directory('..', 'index.html')

@app.route('/gallery')
def gallery():
    """Gallery section"""
    return send_from_directory('..', 'index.html')

@app.route('/game')
def game():
    """Game section"""
    return send_from_directory('..', 'index.html')

@app.route('/blog')
def blog():
    """Blog section"""
    return send_from_directory('..', 'index.html')

# Static file routes for root directory
@app.route('/css/<path:filename>')
def serve_css(filename):
    """Serve CSS files from root directory"""
    return send_from_directory('../css', filename)

@app.route('/js/<path:filename>')
def serve_js(filename):
    """Serve JS files from root directory"""
    return send_from_directory('../js', filename)

@app.route('/images/<path:filename>')
def serve_images(filename):
    """Serve image files from root directory"""
    return send_from_directory('../images', filename)

@app.route('/php/<path:filename>')
def serve_php(filename):
    """Serve PHP files from root directory"""
    return send_from_directory('../php', filename)

@app.route('/favicon.ico')
def favicon():
    """Serve favicon"""
    return send_from_directory('..', 'favicon.ico')

@app.route('/projects')
def projects():
    """Projects page"""
    return render_template('projects.html')

@app.route('/analytics')
def analytics():
    """Analytics page"""
    return render_template('analytics.html')

@app.route('/contact')
def contact():
    """Contact page"""
    return render_template('contact.html')

@app.route('/api/stats')
def get_stats():
    """Get dashboard statistics"""
    try:
        # User statistics
        total_users = User.query.count()
        active_users = User.query.filter_by(is_active=True).count()
        
        # Page view statistics
        total_views = PageView.query.count()
        today_views = PageView.query.filter(
            PageView.timestamp >= datetime.now(timezone.utc).date()
        ).count()
        
        # Popular pages
        page_stats = db.session.query(
            PageView.page,
            db.func.count(PageView.id).label('count')
        ).group_by(PageView.page).order_by(db.func.count(PageView.id).desc()).limit(5).all()
        
        popular_pages = [{'page': page, 'count': count} for page, count in page_stats]
        
        # Recent activity
        recent_views = PageView.query.order_by(PageView.timestamp.desc()).limit(10).all()
        recent_activity = [
            {
                'page': view.page,
                'timestamp': view.timestamp.isoformat(),
                'ip': view.ip_address
            }
            for view in recent_views
        ]
        
        return jsonify({
            'success': True,
            'data': {
                'users': {
                    'total': total_users,
                    'active': active_users
                },
                'views': {
                    'total': total_views,
                    'today': today_views
                },
                'popular_pages': popular_pages,
                'recent_activity': recent_activity
            }
        })
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/metrics')
def get_metrics():
    """Get system metrics for charts"""
    try:
        metric_type = request.args.get('type', 'cpu')
        hours = int(request.args.get('hours', 24))
        
        # Get metrics for the specified time range
        start_time = datetime.now(timezone.utc) - timedelta(hours=hours)
        metrics = SystemMetric.query.filter(
            SystemMetric.metric_type == metric_type,
            SystemMetric.timestamp >= start_time
        ).order_by(SystemMetric.timestamp).all()
        
        data = [
            {
                'timestamp': metric.timestamp.isoformat(),
                'value': metric.value
            }
            for metric in metrics
        ]
        
        return jsonify({
            'success': True,
            'data': data,
            'type': metric_type
        })
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/page-views')
def get_page_views():
    """Get page view analytics"""
    try:
        days = int(request.args.get('days', 7))
        start_date = datetime.now(timezone.utc).date() - timedelta(days=days)
        
        # Get daily page views
        daily_views = db.session.query(
            db.func.date(PageView.timestamp).label('date'),
            db.func.count(PageView.id).label('count')
        ).filter(
            PageView.timestamp >= start_date
        ).group_by(db.func.date(PageView.timestamp)).all()
        
        data = [
            {
                'date': str(date),
                'count': count
            }
            for date, count in daily_views
        ]
        
        return jsonify({
            'success': True,
            'data': data
        })
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/users')
def get_users():
    """Get user data"""
    try:
        users = User.query.all()
        data = [
            {
                'id': user.id,
                'username': user.username,
                'email': user.email,
                'created_at': user.created_at.isoformat(),
                'last_login': user.last_login.isoformat() if user.last_login else None,
                'is_active': user.is_active
            }
            for user in users
        ]
        
        return jsonify({
            'success': True,
            'data': data
        })
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/health')
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'timestamp': datetime.now(timezone.utc).isoformat(),
        'version': '1.0.0',
        'data_science_available': DATA_SCIENCE_AVAILABLE
    })

# Error handlers
@app.errorhandler(404)
def not_found(error):
    return jsonify({'error': 'Not found'}), 404

@app.errorhandler(500)
def internal_error(error):
    db.session.rollback()
    return jsonify({'error': 'Internal server error'}), 500

# CLI commands
@app.cli.command('init-db')
def init_db():
    """Initialize the database"""
    db.create_all()
    print('Database initialized!')

@app.cli.command('seed-data')
def seed_data():
    """Seed the database with sample data"""
    generate_sample_data()
    print('Sample data generated!')

# Development server
if __name__ == '__main__':
    with app.app_context():
        # Create database tables
        db.create_all()
        
        # Generate sample data if database is empty
        if User.query.count() == 0:
            generate_sample_data()
            print('Sample data generated!')
    
    # Run the application
    app.run(debug=True, host='0.0.0.0', port=5000) 