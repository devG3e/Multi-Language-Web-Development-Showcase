<?php
require_once 'admin_auth.php';

$auth = new AdminAuth();
$auth->requireAuth();

$current_admin = $auth->getCurrentAdmin();
$all_admins = $auth->getAllAdmins();
$admin_count = $auth->getAdminCount();
$activity_log = $auth->getActivityLog(10);

// Get statistics
$db = Database::getInstance();

// Blog statistics
$total_posts = $db->fetch("SELECT COUNT(*) as count FROM posts")['count'] ?? 0;
$published_posts = $db->fetch("SELECT COUNT(*) as count FROM posts WHERE status = 'published'")['count'] ?? 0;
$total_comments = $db->fetch("SELECT COUNT(*) as count FROM comments")['count'] ?? 0;
$pending_comments = $db->fetch("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'")['count'] ?? 0;

// Contact form statistics
$total_contacts = $db->fetch("SELECT COUNT(*) as count FROM contacts")['count'] ?? 0;
$unread_contacts = $db->fetch("SELECT COUNT(*) as count FROM contacts WHERE is_read = 0")['count'] ?? 0;

// Recent activities
$recent_posts = $db->fetchAll("SELECT * FROM posts ORDER BY created_at DESC LIMIT 5");
$recent_comments = $db->fetchAll("SELECT * FROM comments ORDER BY created_at DESC LIMIT 5");
$recent_contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Multi-Language Web Development Showcase</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 14px;
            opacity: 0.8;
        }

        .admin-info {
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            margin: 0 20px 20px;
        }

        .admin-info h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .admin-info p {
            font-size: 12px;
            opacity: 0.8;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0 25px 25px 0;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
        }

        .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .btn-danger:hover {
            background: #c53030;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .stat-icon.posts { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.comments { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stat-icon.contacts { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .stat-icon.admins { background: linear-gradient(135deg, #43e97b, #38f9d7); }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .stat-subtitle {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e1e5e9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .card-content {
            padding: 20px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #667eea;
        }

        .activity-content h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .activity-content p {
            font-size: 12px;
            color: #666;
        }

        .admin-list {
            list-style: none;
        }

        .admin-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .admin-item:last-child {
            border-bottom: none;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 15px;
        }

        .admin-info-details h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .admin-info-details p {
            font-size: 12px;
            color: #666;
        }

        .admin-status {
            margin-left: auto;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #c6f6d5;
            color: #2f855a;
        }

        .status-inactive {
            background: #fed7d7;
            color: #c53030;
        }

        .admin-limit-warning {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .admin-limit-warning i {
            margin-right: 8px;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
                <p>Multi-Language Web Development Showcase</p>
            </div>

            <div class="admin-info">
                <h4><?php echo htmlspecialchars($current_admin['username']); ?></h4>
                <p><?php echo htmlspecialchars($current_admin['email']); ?></p>
            </div>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="posts.php" class="nav-link">
                        <i class="fas fa-file-alt"></i> Blog Posts
                    </a>
                </li>
                <li class="nav-item">
                    <a href="comments.php" class="nav-link">
                        <i class="fas fa-comments"></i> Comments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="contacts.php" class="nav-link">
                        <i class="fas fa-envelope"></i> Contact Messages
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admins.php" class="nav-link">
                        <i class="fas fa-users-cog"></i> Admin Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="activity.php" class="nav-link">
                        <i class="fas fa-history"></i> Activity Log
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Dashboard Overview</h1>
                <div class="header-actions">
                    <a href="../index.html" class="btn btn-outline">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
                    <a href="settings.php" class="btn btn-primary">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>

            <!-- Admin Limit Warning -->
            <?php if ($admin_count >= 3): ?>
                <div class="admin-limit-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Admin Limit Reached:</strong> Maximum of 3 active admins allowed. Deactivate an admin to add a new one.
                </div>
            <?php endif; ?>

            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon posts">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $total_posts; ?></div>
                    <div class="stat-label">Total Posts</div>
                    <div class="stat-subtitle"><?php echo $published_posts; ?> published</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon comments">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $total_comments; ?></div>
                    <div class="stat-label">Total Comments</div>
                    <div class="stat-subtitle"><?php echo $pending_comments; ?> pending</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon contacts">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $total_contacts; ?></div>
                    <div class="stat-label">Contact Messages</div>
                    <div class="stat-subtitle"><?php echo $unread_contacts; ?> unread</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon admins">
                            <i class="fas fa-users-cog"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $admin_count; ?>/3</div>
                    <div class="stat-label">Active Admins</div>
                    <div class="stat-subtitle">Maximum limit: 3</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Activity -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Recent Activity</h3>
                        <a href="activity.php" class="btn btn-outline" style="padding: 5px 10px; font-size: 12px;">
                            View All
                        </a>
                    </div>
                    <div class="card-content">
                        <?php if (empty($activity_log)): ?>
                            <p style="text-align: center; color: #666; padding: 20px;">No recent activity</p>
                        <?php else: ?>
                            <?php foreach ($activity_log as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h4><?php echo htmlspecialchars($activity['username']); ?></h4>
                                        <p><?php echo htmlspecialchars($activity['description']); ?></p>
                                        <small style="color: #999;"><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Admin Management -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-users-cog"></i> Admin Management</h3>
                        <?php if ($admin_count < 3): ?>
                            <a href="admins.php?action=create" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">
                                Add Admin
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <ul class="admin-list">
                            <?php foreach ($all_admins as $admin): ?>
                                <li class="admin-item">
                                    <div class="admin-avatar">
                                        <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                                    </div>
                                    <div class="admin-info-details">
                                        <h4><?php echo htmlspecialchars($admin['username']); ?></h4>
                                        <p><?php echo htmlspecialchars($admin['email']); ?></p>
                                        <small style="color: #999;">
                                            Last login: <?php echo $admin['last_login'] ? date('M j, Y', strtotime($admin['last_login'])) : 'Never'; ?>
                                        </small>
                                    </div>
                                    <span class="admin-status <?php echo $admin['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <?php if ($admin_count < 3): ?>
                            <div style="text-align: center; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                                <p style="color: #666; font-size: 14px;">
                                    <i class="fas fa-info-circle"></i>
                                    You can add <?php echo 3 - $admin_count; ?> more admin<?php echo (3 - $admin_count) > 1 ? 's' : ''; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }

        // Auto-refresh dashboard every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);

        // Add click outside to close mobile menu
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth <= 768 && !sidebar.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    </script>
</body>
</html> 