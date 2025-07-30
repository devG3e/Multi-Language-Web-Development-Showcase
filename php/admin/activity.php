<?php
require_once 'admin_auth.php';

$auth = new AdminAuth();
$auth->requireAuth();

$current_admin = $auth->getCurrentAdmin();

// Get activity log with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$db = Database::getInstance();

// Get total count
$total_activities = $db->fetch("SELECT COUNT(*) as count FROM admin_activities")['count'] ?? 0;
$total_pages = ceil($total_activities / $limit);

// Get activities for current page
$sql = "SELECT a.*, u.username, u.full_name 
        FROM admin_activities a 
        JOIN users u ON a.user_id = u.id 
        ORDER BY a.created_at DESC 
        LIMIT :limit OFFSET :offset";

$activities = $db->fetchAll($sql, ['limit' => $limit, 'offset' => $offset]);

// Get activity statistics
$today_activities = $db->fetch("SELECT COUNT(*) as count FROM admin_activities WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
$week_activities = $db->fetch("SELECT COUNT(*) as count FROM admin_activities WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0;
$month_activities = $db->fetch("SELECT COUNT(*) as count FROM admin_activities WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0;

// Get most active admins
$active_admins = $db->fetchAll("
    SELECT u.username, u.full_name, COUNT(a.id) as activity_count 
    FROM admin_activities a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY a.user_id 
    ORDER BY activity_count DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Multi-Language Web Development Showcase</title>
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 20px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 20px;
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

        /* Activity List */
        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
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
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .activity-user {
            font-weight: 600;
            color: #333;
        }

        .activity-time {
            font-size: 12px;
            color: #999;
        }

        .activity-description {
            color: #666;
            margin-bottom: 5px;
        }

        .activity-meta {
            font-size: 12px;
            color: #999;
        }

        .activity-action {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .action-login { background: #c6f6d5; color: #2f855a; }
        .action-logout { background: #fed7d7; color: #c53030; }
        .action-admin_created { background: #bee3f8; color: #2b6cb0; }
        .action-admin_updated { background: #fef5e7; color: #d69e2e; }
        .action-admin_deactivated { background: #fed7d7; color: #c53030; }
        .action-password_changed { background: #e6fffa; color: #2c7a7b; }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .pagination a {
            background: #f8f9fa;
            color: #667eea;
            border: 1px solid #e1e5e9;
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
        }

        .pagination .current {
            background: #667eea;
            color: white;
        }

        .pagination .disabled {
            background: #f8f9fa;
            color: #999;
            cursor: not-allowed;
        }

        /* Active Admins */
        .active-admins {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .admin-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f1f3f4;
        }

        .admin-item:last-child {
            border-bottom: none;
        }

        .admin-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            margin-right: 15px;
        }

        .admin-info-details {
            flex: 1;
        }

        .admin-info-details h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .admin-info-details p {
            font-size: 12px;
            color: #666;
        }

        .activity-count {
            background: #667eea;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
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

            .activity-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
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
                    <a href="dashboard.php" class="nav-link">
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
                    <a href="activity.php" class="nav-link active">
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
                <h1>Activity Log</h1>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button class="btn btn-primary" onclick="exportActivityLog()">
                        <i class="fas fa-download"></i> Export Log
                    </button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-number"><?php echo $today_activities; ?></div>
                    <div class="stat-label">Today's Activities</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-number"><?php echo $week_activities; ?></div>
                    <div class="stat-label">This Week</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-number"><?php echo $month_activities; ?></div>
                    <div class="stat-label">This Month</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_activities; ?></div>
                    <div class="stat-label">Total Activities</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <!-- Activity Log -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Recent Activities</h3>
                        <span style="color: #666; font-size: 14px;">
                            Showing <?php echo count($activities); ?> of <?php echo $total_activities; ?> activities
                        </span>
                    </div>
                    <div class="card-content">
                        <?php if (empty($activities)): ?>
                            <p style="text-align: center; color: #666; padding: 20px;">No activities found</p>
                        <?php else: ?>
                            <ul class="activity-list">
                                <?php foreach ($activities as $activity): ?>
                                    <li class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-header">
                                                <span class="activity-user">
                                                    <?php echo htmlspecialchars($activity['full_name'] ?: $activity['username']); ?>
                                                </span>
                                                <span class="activity-time">
                                                    <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                                </span>
                                            </div>
                                            <div class="activity-description">
                                                <?php echo htmlspecialchars($activity['description']); ?>
                                            </div>
                                            <div class="activity-meta">
                                                <span class="activity-action action-<?php echo $activity['action']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $activity['action'])); ?>
                                                </span>
                                                <?php if ($activity['ip_address']): ?>
                                                    <span style="margin-left: 10px;">
                                                        <i class="fas fa-globe"></i> <?php echo htmlspecialchars($activity['ip_address']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    <?php else: ?>
                                        <span class="disabled">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </span>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <?php if ($i == $page): ?>
                                            <span class="current"><?php echo $i; ?></span>
                                        <?php else: ?>
                                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="disabled">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Most Active Admins -->
                <div class="active-admins">
                    <div class="card-header">
                        <h3><i class="fas fa-users"></i> Most Active Admins</h3>
                        <span style="color: #666; font-size: 14px;">Last 30 days</span>
                    </div>
                    <div class="card-content">
                        <?php if (empty($active_admins)): ?>
                            <p style="text-align: center; color: #666; padding: 20px;">No activity data</p>
                        <?php else: ?>
                            <?php foreach ($active_admins as $admin): ?>
                                <div class="admin-item">
                                    <div class="admin-avatar">
                                        <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                                    </div>
                                    <div class="admin-info-details">
                                        <h4><?php echo htmlspecialchars($admin['full_name'] ?: $admin['username']); ?></h4>
                                        <p><?php echo htmlspecialchars($admin['username']); ?></p>
                                    </div>
                                    <div class="activity-count">
                                        <?php echo $admin['activity_count']; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportActivityLog() {
            // Create a simple CSV export
            const csvContent = "data:text/csv;charset=utf-8," + 
                "Date,User,Action,Description,IP Address\n" +
                "<?php foreach ($activities as $activity): ?>" +
                "<?php echo date('Y-m-d H:i:s', strtotime($activity['created_at'])); ?>," +
                "<?php echo htmlspecialchars($activity['full_name'] ?: $activity['username']); ?>," +
                "<?php echo ucfirst(str_replace('_', ' ', $activity['action'])); ?>," +
                "<?php echo htmlspecialchars($activity['description']); ?>," +
                "<?php echo htmlspecialchars($activity['ip_address']); ?>\n" +
                "<?php endforeach; ?>";

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "activity_log_<?php echo date('Y-m-d'); ?>.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Auto-refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html> 