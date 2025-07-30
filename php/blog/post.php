<?php
require_once '../config.php';

// Get post slug
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (!$slug) {
    header('Location: index.php');
    exit();
}

// Get post data
$post = $db->fetch("SELECT p.*, u.username as author_name 
                    FROM posts p 
                    LEFT JOIN users u ON p.author_id = u.id 
                    WHERE p.slug = :slug AND p.status = 'published'", 
                    ['slug' => $slug]);

if (!$post) {
    header('Location: index.php');
    exit();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid request. Please try again.');
    } else {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $comment = sanitize($_POST['comment']);
        
        $errors = [];
        
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email) || !validateEmail($email)) $errors[] = 'Valid email is required';
        if (empty($comment)) $errors[] = 'Comment is required';
        
        if (empty($errors)) {
            $commentData = [
                'post_id' => $post['id'],
                'name' => $name,
                'email' => $email,
                'comment' => $comment,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if ($db->insert('comments', $commentData)) {
                setFlashMessage('success', 'Comment submitted successfully! It will be reviewed before publishing.');
            } else {
                setFlashMessage('error', 'Error submitting comment. Please try again.');
            }
        } else {
            setFlashMessage('error', implode(', ', $errors));
        }
        
        // Redirect to prevent form resubmission
        header("Location: post.php?slug=$slug");
        exit();
    }
}

// Get comments for this post
$comments = $db->fetchAll("SELECT * FROM comments WHERE post_id = :post_id AND status = 'approved' ORDER BY created_at DESC", 
                          ['post_id' => $post['id']]);

// Get related posts
$relatedPosts = $db->fetchAll("SELECT id, title, slug, created_at FROM posts 
                               WHERE category = :category AND id != :post_id AND status = 'published' 
                               ORDER BY created_at DESC LIMIT 3", 
                               ['category' => $post['category'], 'post_id' => $post['id']]);

// Update view count
$db->update('posts', ['views' => $post['views'] + 1], 'id = :id', ['id' => $post['id']]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(truncateText($post['content'], 160)); ?>">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/responsive.css">
    <link rel="stylesheet" href="../../css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .post-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: var(--spacing-3xl) 0;
            text-align: center;
        }
        
        .post-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--spacing-xl);
            margin-top: var(--spacing-xl);
        }
        
        .post-content {
            background: var(--background-secondary);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-xl);
        }
        
        .post-meta {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .post-meta span {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }
        
        .post-body {
            line-height: 1.8;
            color: var(--text-primary);
        }
        
        .post-body h2, .post-body h3, .post-body h4 {
            margin: var(--spacing-lg) 0 var(--spacing-md) 0;
            color: var(--text-primary);
        }
        
        .post-body p {
            margin-bottom: var(--spacing-md);
        }
        
        .post-body ul, .post-body ol {
            margin-bottom: var(--spacing-md);
            padding-left: var(--spacing-lg);
        }
        
        .post-body code {
            background: var(--background-primary);
            padding: 2px 6px;
            border-radius: var(--radius-sm);
            font-family: 'Courier New', monospace;
        }
        
        .post-body pre {
            background: var(--background-primary);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            overflow-x: auto;
            margin: var(--spacing-md) 0;
        }
        
        .comments-section {
            background: var(--background-secondary);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
        }
        
        .comment {
            border-bottom: 1px solid var(--border-color);
            padding: var(--spacing-lg) 0;
        }
        
        .comment:last-child {
            border-bottom: none;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--spacing-sm);
        }
        
        .comment-author {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .comment-date {
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .comment-content {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .comment-form {
            background: var(--background-secondary);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            margin-top: var(--spacing-xl);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-md);
        }
        
        @media (max-width: 768px) {
            .post-container {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="../../index.html">
                    <i class="fas fa-code"></i>
                    <span>Dev Showcase</span>
                </a>
            </div>
            
            <div class="nav-menu" id="nav-menu">
                <a href="../../index.html" class="nav-link">Home</a>
                <a href="../../index.html#portfolio" class="nav-link">Portfolio</a>
                <a href="index.php" class="nav-link active">Blog</a>
                <a href="../../index.html#gallery" class="nav-link">Gallery</a>
                <a href="../../index.html#game" class="nav-link">Game</a>
                <a href="../../index.html#dashboard" class="nav-link">Dashboard</a>
                <a href="../../index.html#contact" class="nav-link">Contact</a>
            </div>
            
            <div class="nav-toggle" id="nav-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Post Header -->
    <header class="post-header">
        <div class="container">
            <div class="post-meta">
                <span><i class="fas fa-calendar"></i> <?php echo formatDate($post['created_at']); ?></span>
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author_name']); ?></span>
                <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($post['category']); ?></span>
                <span><i class="fas fa-eye"></i> <?php echo $post['views']; ?> views</span>
                <span><i class="fas fa-comments"></i> <?php echo count($comments); ?> comments</span>
            </div>
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="post-container">
            <!-- Post Content -->
            <div class="post-main">
                <article class="post-content">
                    <div class="post-body">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                </article>

                <!-- Comments Section -->
                <section class="comments-section">
                    <h3>Comments (<?php echo count($comments); ?>)</h3>
                    
                    <?php if ($comments): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="comment-header">
                                    <span class="comment-author"><?php echo htmlspecialchars($comment['name']); ?></span>
                                    <span class="comment-date"><?php echo formatDate($comment['created_at']); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--text-light); text-align: center; padding: var(--spacing-xl);">
                            No comments yet. Be the first to comment!
                        </p>
                    <?php endif; ?>
                </section>

                <!-- Comment Form -->
                <section class="comment-form">
                    <h3>Leave a Comment</h3>
                    
                    <?php 
                    $flash = getFlashMessage();
                    if ($flash): 
                    ?>
                        <div class="alert alert-<?php echo $flash['type']; ?>">
                            <?php echo htmlspecialchars($flash['message']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Name *</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment">Comment *</label>
                            <textarea id="comment" name="comment" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" name="submit_comment" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Comment
                        </button>
                    </form>
                </section>
            </div>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <!-- Related Posts -->
                <?php if ($relatedPosts): ?>
                    <div class="recent-posts">
                        <h3>Related Posts</h3>
                        <?php foreach ($relatedPosts as $related): ?>
                            <div class="recent-post">
                                <a href="post.php?slug=<?php echo $related['slug']; ?>">
                                    <div><?php echo htmlspecialchars($related['title']); ?></div>
                                    <div class="date"><?php echo formatDate($related['created_at'], 'M j, Y'); ?></div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Back to Blog -->
                <div style="text-align: center; margin-top: var(--spacing-xl);">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Blog
                    </a>
                </div>
            </aside>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>Demonstrating proficiency in modern web technologies and programming languages.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../../index.html#portfolio">Portfolio</a></li>
                        <li><a href="index.php">Blog</a></li>
                        <li><a href="../../index.html#gallery">Gallery</a></li>
                        <li><a href="../../index.html#game">Game</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Technologies</h4>
                    <ul>
                        <li>HTML5 & CSS3</li>
                        <li>JavaScript ES6+</li>
                        <li>PHP & MySQL</li>
                        <li>Python Flask</li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Connect</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../../js/main.js"></script>
</body>
</html> 