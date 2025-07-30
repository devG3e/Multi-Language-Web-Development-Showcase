# Data Analysis Dashboard - Python Flask

A modern, responsive data analysis dashboard built with Python Flask, featuring real-time analytics, project management, and interactive data visualization.

## 🚀 Features

### Core Features
- **Real-time Dashboard**: Live statistics and metrics with auto-refresh
- **Project Management**: CRUD operations for web development projects
- **Advanced Analytics**: Interactive charts and data visualization
- **Contact System**: Message management with form validation
- **Responsive Design**: Mobile-first approach with Bootstrap 5

### Technical Features
- **Security**: CSRF protection, input validation, security headers
- **Performance**: Response caching, optimized database queries
- **Error Handling**: Comprehensive error pages and logging
- **API Endpoints**: RESTful API for data access
- **Database**: SQLite with SQLAlchemy ORM

## 📋 Prerequisites

- Python 3.8 or higher
- pip (Python package installer)

## 🛠️ Installation

1. **Clone the repository** (if not already done):
   ```bash
   cd python
   ```

2. **Create a virtual environment**:
   ```bash
   python -m venv venv
   ```

3. **Activate the virtual environment**:
   - **Windows**:
     ```bash
     venv\Scripts\activate
     ```
   - **macOS/Linux**:
     ```bash
     source venv/bin/activate
     ```

4. **Install dependencies**:
   ```bash
   pip install -r requirements.txt
   ```

## 🚀 Quick Start

### Development Mode
```bash
python run.py
```

### Production Mode
```bash
export FLASK_ENV=production
export SECRET_KEY=your-secure-secret-key
gunicorn -w 4 -b 0.0.0.0:5000 app:app
```

The dashboard will be available at: `http://localhost:5000`

## 📁 Project Structure

```
python/
├── app.py                 # Main Flask application
├── run.py                 # Startup script
├── requirements.txt       # Python dependencies
├── README.md             # This file
├── templates/            # HTML templates
│   ├── index.html        # Main dashboard
│   ├── projects.html     # Project management
│   ├── analytics.html    # Advanced analytics
│   ├── contact.html      # Contact form
│   ├── 404.html          # 404 error page
│   └── 500.html          # 500 error page
└── dashboard.db          # SQLite database (created automatically)
```

## 🔧 Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `SECRET_KEY` | Flask secret key | `dev-secret-key-change-in-production` |
| `DATABASE_URL` | Database connection string | `sqlite:///dashboard.db` |
| `HOST` | Server host | `0.0.0.0` |
| `PORT` | Server port | `5000` |
| `DEBUG` | Debug mode | `False` |

### Example `.env` file:
```env
SECRET_KEY=your-super-secret-key-here
DATABASE_URL=sqlite:///dashboard.db
HOST=0.0.0.0
PORT=5000
DEBUG=False
```

## 📊 API Endpoints

### Dashboard Statistics
- `GET /api/stats` - Get dashboard statistics and chart data
- `GET /api/projects` - Get all projects
- `GET /api/contacts` - Get contact messages

### Project Management
- `POST /projects/add` - Add new project
- `POST /projects/<id>/update` - Update project
- `POST /projects/<id>/delete` - Delete project

### Contact System
- `POST /contact` - Submit contact form
- `GET /api/contacts` - Get contact messages

## 🎨 Customization

### Styling
The dashboard uses Bootstrap 5 with custom CSS variables. Main colors can be modified in the CSS `:root` section:

```css
:root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
}
```

### Adding New Features
1. **New Routes**: Add to `app.py`
2. **New Templates**: Create in `templates/` directory
3. **New Models**: Add to database models section in `app.py`
4. **New API Endpoints**: Add with proper error handling

## 🔒 Security Features

- **CSRF Protection**: All forms protected with CSRF tokens
- **Input Validation**: Server-side validation for all inputs
- **Security Headers**: XSS protection, content type options
- **SQL Injection Protection**: Using SQLAlchemy ORM
- **Error Handling**: Secure error messages

## 📈 Performance Optimizations

- **Response Caching**: API responses cached for 1 minute
- **Database Optimization**: Efficient queries with SQLAlchemy
- **Static Assets**: CDN for Bootstrap and Font Awesome
- **Lazy Loading**: Charts load on demand

## 🐛 Troubleshooting

### Common Issues

1. **Database Errors**:
   ```bash
   # Delete and recreate database
   rm dashboard.db
   python run.py
   ```

2. **Port Already in Use**:
   ```bash
   # Change port
   export PORT=5001
   python run.py
   ```

3. **Import Errors**:
   ```bash
   # Reinstall dependencies
   pip install -r requirements.txt
   ```

### Logs
Check `dashboard.log` for detailed error information.

## 🧪 Testing

### Manual Testing
1. Navigate to each page and verify functionality
2. Test form submissions with valid/invalid data
3. Verify API endpoints return correct data
4. Test responsive design on different screen sizes

### API Testing
Use tools like Postman or curl to test API endpoints:

```bash
# Test stats endpoint
curl http://localhost:5000/api/stats

# Test projects endpoint
curl http://localhost:5000/api/projects
```

## 📝 Development

### Adding New Charts
1. Add chart data to `generate_chart_data()` function
2. Create chart in template with Chart.js
3. Add chart initialization in JavaScript

### Adding New Metrics
1. Add metric calculation in API endpoint
2. Update dashboard template to display metric
3. Add to caching if needed

## 🚀 Deployment

### Production Checklist
- [ ] Set `DEBUG=False`
- [ ] Use strong `SECRET_KEY`
- [ ] Configure production database
- [ ] Set up logging
- [ ] Configure reverse proxy (nginx)
- [ ] Set up SSL/TLS
- [ ] Configure backup strategy

### Docker Deployment
```dockerfile
FROM python:3.9-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt
COPY . .
EXPOSE 5000
CMD ["gunicorn", "-w", "4", "-b", "0.0.0.0:5000", "app:app"]
```

## 📄 License

This project is part of the Multi-Language Web Development Showcase.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support and questions:
- Check the troubleshooting section
- Review the logs in `dashboard.log`
- Contact through the dashboard contact form

---

**Happy Coding! 🎉** 