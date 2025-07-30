-- ===== DATABASE SETUP FOR PHP BLOG SYSTEM =====

-- Create database
CREATE DATABASE IF NOT EXISTS devshowcase_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE devshowcase_blog;

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_email (email)
);

-- Blog posts table
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    featured BOOLEAN DEFAULT FALSE,
    author_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_created_at (created_at),
    INDEX idx_slug (slug),
    INDEX idx_featured (featured)
);

-- Blog comments table
CREATE TABLE IF NOT EXISTS blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    comment TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Blog tags table
CREATE TABLE IF NOT EXISTS blog_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
);

-- Blog post tags relationship table
CREATE TABLE IF NOT EXISTS blog_post_tags (
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
);

-- Users table (for admin functionality)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor', 'author') DEFAULT 'author',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Insert sample data

-- Sample blog posts
INSERT INTO blog_posts (title, slug, excerpt, content, category, status, featured, published_at) VALUES
(
    'Getting Started with Modern Web Development',
    'getting-started-with-modern-web-development',
    'Learn the fundamentals of modern web development including HTML5, CSS3, and JavaScript ES6+. This comprehensive guide covers everything you need to know to start building responsive and interactive websites.',
    '<h2>Introduction to Modern Web Development</h2>
<p>Web development has evolved significantly over the past decade. Today, we have powerful tools and frameworks that make building websites faster and more efficient than ever before.</p>

<h3>Essential Technologies</h3>
<ul>
<li><strong>HTML5:</strong> The latest version of HTML with semantic elements and improved accessibility</li>
<li><strong>CSS3:</strong> Advanced styling with flexbox, grid, animations, and responsive design</li>
<li><strong>JavaScript ES6+:</strong> Modern JavaScript with classes, modules, and async/await</li>
</ul>

<h3>Getting Started</h3>
<p>To begin your web development journey, you\'ll need:</p>
<ol>
<li>A code editor (VS Code, Sublime Text, or Atom)</li>
<li>A modern web browser (Chrome, Firefox, Safari, or Edge)</li>
<li>Basic understanding of programming concepts</li>
</ol>

<h3>Best Practices</h3>
<p>Follow these best practices for better code quality:</p>
<ul>
<li>Write semantic HTML</li>
<li>Use CSS Grid and Flexbox for layouts</li>
<li>Write clean, readable JavaScript</li>
<li>Test across different browsers</li>
<li>Optimize for performance</li>
</ul>

<p>Remember, web development is a continuous learning process. Stay updated with the latest trends and technologies!</p>',
    'Web Development',
    'published',
    TRUE,
    NOW()
),
(
    'Building Responsive Websites with CSS Grid',
    'building-responsive-websites-with-css-grid',
    'Master CSS Grid layout to create flexible, responsive websites that work perfectly on all devices. Learn advanced techniques and best practices.',
    '<h2>Understanding CSS Grid</h2>
<p>CSS Grid is a powerful layout system that allows you to create complex, responsive layouts with ease. Unlike Flexbox, which is designed for one-dimensional layouts, Grid is perfect for two-dimensional layouts.</p>

<h3>Grid Container Properties</h3>
<pre><code>.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    grid-gap: 20px;
    padding: 20px;
}</code></pre>

<h3>Grid Items</h3>
<p>Grid items can be positioned using:</p>
<ul>
<li><code>grid-column</code> and <code>grid-row</code> for positioning</li>
<li><code>grid-area</code> for named areas</li>
<li><code>justify-self</code> and <code>align-self</code> for alignment</li>
</ul>

<h3>Responsive Design</h3>
<p>CSS Grid makes responsive design much easier:</p>
<pre><code>@media (max-width: 768px) {
    .grid-container {
        grid-template-columns: 1fr;
    }
}</code></pre>

<p>With CSS Grid, you can create complex layouts that automatically adapt to different screen sizes!</p>',
    'CSS',
    'published',
    TRUE,
    DATE_SUB(NOW(), INTERVAL 1 DAY)
),
(
    'JavaScript ES6+ Features You Should Know',
    'javascript-es6-features-you-should-know',
    'Explore the most important ES6+ features that every JavaScript developer should master, including arrow functions, destructuring, and async/await.',
    '<h2>Modern JavaScript Features</h2>
<p>ES6 (ECMAScript 2015) and later versions introduced many powerful features that make JavaScript more expressive and easier to work with.</p>

<h3>Arrow Functions</h3>
<p>Arrow functions provide a concise syntax for writing functions:</p>
<pre><code>// Traditional function
function add(a, b) {
    return a + b;
}

// Arrow function
const add = (a, b) => a + b;</code></pre>

<h3>Destructuring</h3>
<p>Destructuring allows you to extract values from objects and arrays:</p>
<pre><code>// Object destructuring
const { name, age } = person;

// Array destructuring
const [first, second, ...rest] = array;</code></pre>

<h3>Async/Await</h3>
<p>Async/await makes working with promises much cleaner:</p>
<pre><code>async function fetchData() {
    try {
        const response = await fetch(\'/api/data\');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error(\'Error:\', error);
    }
}</code></pre>

<h3>Template Literals</h3>
<p>Template literals provide an elegant way to create strings:</p>
<pre><code>const name = \'John\';
const greeting = `Hello, ${name}! Welcome to our website.`;</code></pre>

<p>These features make JavaScript more powerful and enjoyable to work with!</p>',
    'JavaScript',
    'published',
    FALSE,
    DATE_SUB(NOW(), INTERVAL 2 DAY)
),
(
    'Introduction to PHP and MySQL',
    'introduction-to-php-and-mysql',
    'Learn the basics of server-side programming with PHP and database management with MySQL. Build dynamic web applications from scratch.',
    '<h2>Server-Side Programming with PHP</h2>
<p>PHP is a popular server-side scripting language that powers millions of websites worldwide. When combined with MySQL, you can create powerful web applications.</p>

<h3>PHP Basics</h3>
<p>PHP code is embedded within HTML:</p>
<pre><code>&lt;?php
$name = "World";
echo "Hello, " . $name . "!";
?&gt;</code></pre>

<h3>Working with Forms</h3>
<p>PHP makes it easy to handle form submissions:</p>
<pre><code>&lt;?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    // Process the form data
}
?&gt;</code></pre>

<h3>Database Connection</h3>
<p>Connect to MySQL using PDO:</p>
<pre><code>&lt;?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=myapp", "username", "password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?&gt;</code></pre>

<h3>Security Best Practices</h3>
<ul>
<li>Always use prepared statements to prevent SQL injection</li>
<li>Validate and sanitize user input</li>
<li>Use HTTPS for sensitive data</li>
<li>Keep PHP and MySQL updated</li>
</ul>

<p>PHP and MySQL provide a solid foundation for building web applications!</p>',
    'PHP',
    'published',
    FALSE,
    DATE_SUB(NOW(), INTERVAL 3 DAY)
),
(
    'Python Web Development with Flask',
    'python-web-development-with-flask',
    'Build web applications using Python and the Flask framework. Learn routing, templates, forms, and database integration.',
    '<h2>Flask Framework Overview</h2>
<p>Flask is a lightweight and flexible Python web framework that makes it easy to build web applications. It follows the WSGI standard and provides a simple yet powerful foundation.</p>

<h3>Basic Flask Application</h3>
<pre><code>from flask import Flask, render_template

app = Flask(__name__)

@app.route(\'/\')
def home():
    return \'Hello, World!\'

if __name__ == \'__main__\':
    app.run(debug=True)</code></pre>

<h3>Routing and Templates</h3>
<p>Flask makes routing and template rendering straightforward:</p>
<pre><code>@app.route(\'/user/&lt;username&gt;\')
def show_user_profile(username):
    return render_template(\'user.html\', username=username)</code></pre>

<h3>Working with Forms</h3>
<p>Handle form submissions with Flask-WTF:</p>
<pre><code>from flask_wtf import FlaskForm
from wtforms import StringField, SubmitField

class ContactForm(FlaskForm):
    name = StringField(\'Name\')
    email = StringField(\'Email\')
    submit = SubmitField(\'Submit\')</code></pre>

<h3>Database Integration</h3>
<p>Use Flask-SQLAlchemy for database operations:</p>
<pre><code>from flask_sqlalchemy import SQLAlchemy

db = SQLAlchemy(app)

class User(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)</code></pre>

<p>Flask is perfect for building APIs and web applications with Python!</p>',
    'Python',
    'published',
    TRUE,
    DATE_SUB(NOW(), INTERVAL 4 DAY)
),
(
    'Ruby on Rails: Building Web Applications',
    'ruby-on-rails-building-web-applications',
    'Discover the power of Ruby on Rails for rapid web application development. Learn MVC architecture, Active Record, and deployment strategies.',
    '<h2>Ruby on Rails Philosophy</h2>
<p>Ruby on Rails follows the "Convention over Configuration" principle, which means it provides sensible defaults that work for most applications. This allows developers to focus on building features rather than configuring the framework.</p>

<h3>MVC Architecture</h3>
<p>Rails follows the Model-View-Controller pattern:</p>
<ul>
<li><strong>Models:</strong> Handle data and business logic</li>
<li><strong>Views:</strong> Present data to users</li>
<li><strong>Controllers:</strong> Handle user requests and coordinate between models and views</li>
</ul>

<h3>Active Record</h3>
<p>Active Record is Rails\' ORM (Object-Relational Mapping) system:</p>
<pre><code>class User < ApplicationRecord
  validates :email, presence: true, uniqueness: true
  has_many :posts
  
  def full_name
    "#{first_name} #{last_name}"
  end
end</code></pre>

<h3>RESTful Routes</h3>
<p>Rails provides RESTful routing by default:</p>
<pre><code>Rails.application.routes.draw do
  resources :users
  resources :posts
end</code></pre>

<h3>Gems and Plugins</h3>
<p>Rails has a rich ecosystem of gems:</p>
<ul>
<li><code>devise</code> for authentication</li>
<li><code>cancancan</code> for authorization</li>
<li><code>paperclip</code> for file uploads</li>
<li><code>sidekiq</code> for background jobs</li>
</ul>

<p>Ruby on Rails enables rapid development of feature-rich web applications!</p>',
    'Ruby',
    'published',
    FALSE,
    DATE_SUB(NOW(), INTERVAL 5 DAY)
);

-- Sample tags
INSERT INTO blog_tags (name, slug, description) VALUES
('Web Development', 'web-development', 'General web development topics'),
('JavaScript', 'javascript', 'JavaScript programming and frameworks'),
('CSS', 'css', 'CSS styling and layout techniques'),
('PHP', 'php', 'PHP server-side programming'),
('Python', 'python', 'Python programming and web frameworks'),
('Ruby', 'ruby', 'Ruby programming and Rails framework'),
('C#', 'csharp', 'C# programming and .NET framework'),
('Perl', 'perl', 'Perl programming language'),
('Database', 'database', 'Database design and management'),
('API', 'api', 'Application Programming Interfaces'),
('Security', 'security', 'Web security best practices'),
('Performance', 'performance', 'Website performance optimization');

-- Link posts to tags
INSERT INTO blog_post_tags (post_id, tag_id) VALUES
(1, 1), (1, 2), (1, 3),  -- Web Development post
(2, 3), (2, 1),          -- CSS Grid post
(3, 2), (3, 1),          -- JavaScript ES6 post
(4, 4), (4, 9), (4, 11), -- PHP MySQL post
(5, 5), (5, 1), (5, 10), -- Python Flask post
(6, 6), (6, 1), (6, 9);  -- Ruby Rails post

-- Sample comments
INSERT INTO blog_comments (post_id, name, email, comment, status, created_at) VALUES
(1, 'John Doe', 'john@example.com', 'Great article! This really helped me understand modern web development concepts.', 'approved', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'Jane Smith', 'jane@example.com', 'I especially liked the section about best practices. Very practical advice!', 'approved', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 'Mike Johnson', 'mike@example.com', 'CSS Grid is amazing! This tutorial made it so much clearer.', 'approved', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(3, 'Sarah Wilson', 'sarah@example.com', 'Arrow functions are my favorite ES6 feature. Great explanation!', 'approved', DATE_SUB(NOW(), INTERVAL 15 MINUTE));

-- Sample user (admin)
INSERT INTO users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@devshowcase.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Create indexes for better performance
CREATE INDEX idx_posts_category_status ON blog_posts(category, status);
CREATE INDEX idx_comments_post_status ON blog_comments(post_id, status);
CREATE INDEX idx_tags_name ON blog_tags(name); 