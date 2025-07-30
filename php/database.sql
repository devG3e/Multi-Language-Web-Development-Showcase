-- ===== DATABASE SCHEMA FOR MULTI-LANGUAGE WEB DEVELOPMENT SHOWCASE =====

-- Create database
CREATE DATABASE IF NOT EXISTS dev_showcase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dev_showcase;

-- Users table (Enhanced for admin system)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) UNIQUE NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    failed_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active),
    INDEX idx_locked_until (locked_until)
);

-- Admin activities table (for audit trail)
CREATE TABLE admin_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Posts table (for blog)
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    author_id INT NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_author (author_id),
    INDEX idx_created (created_at),
    INDEX idx_published (published_at)
);

-- Comments table
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    comment TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- Contacts table
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
);

-- Settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('admin', 'admin@devshowcase.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj4J/HS.iQeO', 'System Administrator', 'admin');

-- Insert sample blog posts
INSERT INTO posts (title, slug, content, author_id, category, status, featured, published_at) VALUES 
('Getting Started with Web Development', 'getting-started-with-web-development', 
'Web development is an exciting journey that combines creativity with technical skills. In this post, we\'ll explore the fundamentals of web development and how to get started on your path to becoming a web developer.

## What is Web Development?

Web development involves creating websites and web applications that run on the internet. It encompasses both frontend development (what users see and interact with) and backend development (server-side logic and databases).

## Essential Technologies

### Frontend Technologies
- **HTML5**: The structure of web pages
- **CSS3**: Styling and layout
- **JavaScript**: Interactivity and dynamic content

### Backend Technologies
- **PHP**: Server-side scripting
- **Python**: Web frameworks like Flask and Django
- **Node.js**: JavaScript runtime for server-side development
- **Ruby**: Ruby on Rails framework
- **C#**: ASP.NET framework

## Getting Started

1. **Learn the Basics**: Start with HTML, CSS, and JavaScript
2. **Choose a Backend Language**: Pick one that interests you
3. **Build Projects**: Practice by creating real applications
4. **Learn Frameworks**: Explore popular frameworks and libraries
5. **Stay Updated**: Web development evolves rapidly

## Conclusion

Web development is a rewarding field that offers endless opportunities for creativity and problem-solving. Start with the basics, build projects, and never stop learning!', 
1, 'Web Development', 'published', TRUE, NOW()),

('Modern CSS Techniques', 'modern-css-techniques', 
'CSS has evolved significantly over the years. Modern CSS provides powerful tools for creating responsive, beautiful layouts. Let\'s explore some advanced techniques.

## CSS Grid Layout

CSS Grid is a powerful layout system that allows you to create complex layouts with ease. It\'s perfect for creating responsive designs.

```css
.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}
```

## Flexbox

Flexbox is excellent for one-dimensional layouts and aligning items.

```css
.flex-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
```

## CSS Custom Properties

CSS variables make your styles more maintainable and dynamic.

```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
}

.button {
    background: var(--primary-color);
}
```

## Conclusion

Modern CSS techniques make web development more efficient and enjoyable. Master these tools to create stunning websites!', 
1, 'CSS', 'published', TRUE, NOW()),

('JavaScript ES6+ Features', 'javascript-es6-features', 
'JavaScript ES6 (ECMAScript 2015) introduced many powerful features that have revolutionized how we write JavaScript code.

## Arrow Functions

Arrow functions provide a concise syntax for writing functions.

```javascript
// Traditional function
function add(a, b) {
    return a + b;
}

// Arrow function
const add = (a, b) => a + b;
```

## Destructuring

Destructuring allows you to extract values from objects and arrays easily.

```javascript
const person = { name: 'John', age: 30 };
const { name, age } = person;

const numbers = [1, 2, 3, 4, 5];
const [first, second, ...rest] = numbers;
```

## Template Literals

Template literals make string interpolation much cleaner.

```javascript
const name = 'World';
const greeting = `Hello, ${name}!`;
```

## Conclusion

ES6+ features make JavaScript more powerful and enjoyable to work with. Embrace these modern features in your projects!', 
1, 'JavaScript', 'published', TRUE, NOW());

-- Insert sample comments
INSERT INTO comments (post_id, name, email, comment, status, created_at) VALUES 
(1, 'John Doe', 'john@example.com', 'Great article! This really helped me understand web development basics.', 'approved', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'Jane Smith', 'jane@example.com', 'I especially liked the section about choosing a backend language. Very helpful!', 'approved', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 'Mike Johnson', 'mike@example.com', 'CSS Grid is amazing! This tutorial made it so much clearer.', 'approved', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(3, 'Sarah Wilson', 'sarah@example.com', 'Arrow functions are my favorite ES6 feature. Great explanation!', 'approved', DATE_SUB(NOW(), INTERVAL 15 MINUTE));

-- Insert sample contact messages
INSERT INTO contacts (name, email, subject, message, created_at) VALUES 
('Alice Brown', 'alice@example.com', 'Website Inquiry', 'I love your website! Can you help me with a similar project?', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Bob Green', 'bob@example.com', 'Technical Question', 'What technologies do you recommend for a beginner web developer?', DATE_SUB(NOW(), INTERVAL 12 HOUR)),
('Carol White', 'carol@example.com', 'Collaboration Request', 'I would like to discuss a potential collaboration on web development projects.', DATE_SUB(NOW(), INTERVAL 6 HOUR));

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('site_name', 'Multi-Language Web Development Showcase', 'Website name'),
('site_description', 'A comprehensive showcase of web development technologies', 'Website description'),
('posts_per_page', '6', 'Number of posts to display per page'),
('comments_enabled', '1', 'Enable/disable comments'),
('contact_email', 'admin@devshowcase.com', 'Contact form recipient email');

-- Create indexes for better performance
CREATE INDEX idx_posts_featured ON posts(featured);
CREATE INDEX idx_comments_email ON comments(email);
CREATE INDEX idx_contacts_subject ON contacts(subject); 