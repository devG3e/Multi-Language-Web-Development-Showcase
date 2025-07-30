<?php
require_once '../config.php';

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);

// Get search query
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Get category filter
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Calculate offset
$offset = ($page - 1) * POSTS_PER_PAGE;

// Build query
$whereConditions = ['status = "published"'];
$params = [];

if ($search) {
    $whereConditions[] = '(title LIKE :search OR content LIKE :search)';
    $params['search'] = "%$search%";
}

if ($category) {
    $whereConditions[] = 'category = :category';
    $params['category'] = $category;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total posts count
$countSql = "SELECT COUNT(*) as total FROM posts WHERE $whereClause";
$totalPosts = $db->fetch($countSql, $params)['total'];
$totalPages = ceil($totalPosts / POSTS_PER_PAGE);

// Get posts
$sql = "SELECT p.*, u.username as author_name, 
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'approved') as comment_count
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE $whereClause 
        ORDER BY p.created_at DESC 
        LIMIT " . POSTS_PER_PAGE . " OFFSET $offset";

$posts = $db->fetchAll($sql, $params);

// Get categories for filter
$categories = $db->fetchAll("SELECT category, COUNT(*) as count FROM posts WHERE status = 'published' GROUP BY category ORDER BY count DESC");

// Get recent posts for sidebar
$recentPosts = $db->fetchAll("SELECT id, title, slug, created_at FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Latest articles and insights about web development, programming, and technology">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/responsive.css">
    <link rel="stylesheet" href="../../css/components.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .blog-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: var(--spacing-3xl) 0;
            text-align: center;
        }
        
        .blog-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--spacing-xl);
            margin-top: var(--spacing-xl);
        }
        
        .blog-main {
            min-height: 500px;
        }
        
        .blog-sidebar {
            background: var(--background-secondary);
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            height: fit-content;
        }
        
        .search-form {
            margin-bottom: var(--spacing-xl);
        }
        
        .search-input {
            width: 100%;
            padding: var(--spacing-md);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--font-size-base);
        }
        
        .category-filter {
            margin-bottom: var(--spacing-xl);
        }
        
        .category-list {
            list-style: none;
            padding: 0;
        }
        
        .category-list li {
            margin-bottom: var(--spacing-sm);
        }
        
        .category-list a {
            display: flex;
            justify-content: space-between;
            padding: var(--spacing-sm);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }
        
        .category-list a:hover {
            background: var(--background-primary);
        }
        
        .category-list .count {
            background: var(--primary-color);
            color: white;
            padding: 2px 8px;
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
        }
        
        .recent-posts {
            margin-bottom: var(--spacing-xl);
        }
        
        .recent-posts h3 {
            margin-bottom: var(--spacing-md);
        }
        
        .recent-post {
            padding: var(--spacing-sm) 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .recent-post:last-child {
            border-bottom: none;
        }
        
        .recent-post a {
            color: var(--text-primary);
            text-decoration: none;
        }
        
        .recent-post a:hover {
            color: var(--primary-color);
        }
        
        .recent-post .date {
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }
        
        @media (max-width: 768px) {
            .blog-container {
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

    <!-- Blog Header -->
    <header class="blog-header">
        <div class="container">
            <h1>Blog</h1>
            <p>Latest insights, tutorials, and thoughts on web development and technology</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="blog-container">
            <!-- Main Blog Content -->
            <div class="blog-main">
                <!-- Search and Filter -->
                <div class="search-form">
                    <form method="GET" action="">
                        <div style="display: flex; gap: var(--spacing-md);">
                            <input type="text" name="search" class="search-input" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Posts -->
                <?php if ($posts): ?>
                    <div class="blog-grid">
                        <?php foreach ($posts as $post): ?>
                            <article class="blog-post">
                                <div class="blog-image">
                                    <span style="font-size: 3rem;">📝</span>
                                </div>
                                <div class="blog-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo formatDate($post['created_at']); ?></span>
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author_name']); ?></span>
                                    <span><i class="fas fa-comments"></i> <?php echo $post['comment_count']; ?> comments</span>
                                </div>
                                <div class="blog-content">
                                    <h3 class="blog-title">
                                        <a href="post.php?slug=<?php echo $post['slug']; ?>">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="blog-excerpt">
                                        <?php echo truncateText($post['content']); ?>
                                    </p>
                                    <div style="margin-top: var(--spacing-md);">
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($post['category']); ?></span>
                                    </div>
                                    <a href="post.php?slug=<?php echo $post['slug']; ?>" class="blog-read-more">
                                        Read More <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>" class="pagination-item">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>" 
                                   class="pagination-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>" class="pagination-item">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div style="text-align: center; padding: var(--spacing-3xl);">
                        <i class="fas fa-search" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                        <h3>No posts found</h3>
                        <p><?php echo $search ? 'Try adjusting your search criteria.' : 'No blog posts available yet.'; ?></p>
                        <?php if ($search): ?>
                            <a href="index.php" class="btn btn-primary">View All Posts</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <!-- Search -->
                <div class="search-form">
                    <h3>Search Posts</h3>
                    <form method="GET" action="">
                        <input type="text" name="search" class="search-input" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>

                <!-- Categories -->
                <div class="category-filter">
                    <h3>Categories</h3>
                    <ul class="category-list">
                        <li>
                            <a href="index.php">
                                All Posts
                                <span class="count"><?php echo $totalPosts; ?></span>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="?category=<?php echo urlencode($cat['category']); ?>" 
                                   class="<?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                    <span class="count"><?php echo $cat['count']; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Recent Posts -->
                <div class="recent-posts">
                    <h3>Recent Posts</h3>
                    <?php foreach ($recentPosts as $recent): ?>
                        <div class="recent-post">
                            <a href="post.php?slug=<?php echo $recent['slug']; ?>">
                                <div><?php echo htmlspecialchars($recent['title']); ?></div>
                                <div class="date"><?php echo formatDate($recent['created_at'], 'M j, Y'); ?></div>
                            </a>
                        </div>
                    <?php endforeach; ?>
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