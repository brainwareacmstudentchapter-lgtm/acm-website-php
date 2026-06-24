<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

requireLogin();

$stats = getStats();
$posts = getPosts(null, false);
$events = getEvents(null);
$requests = getRequests();

$postCount = $posts->num_rows;
$eventCount = $events->num_rows;
$requestCount = $requests->num_rows;

$pendingRequests = 0;
while ($req = $requests->fetch_assoc()) {
    if ($req['status'] === 'pending') $pendingRequests++;
}
$requests->data_seek(0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ACM VIT Chennai</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-dashboard">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon">ACM</div>
                <span>Admin Panel</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-home"></i> Overview
                </a>
                <a href="posts.php">
                    <i class="fas fa-blog"></i> Posts
                </a>
                <a href="events.php">
                    <i class="fas fa-calendar-alt"></i> Events
                </a>
                <a href="requests.php">
                    <i class="fas fa-comments"></i> Requests
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-user">
                    <span><?php echo $_SESSION['username']; ?></span>
                    <span class="admin-role"><?php echo $_SESSION['role']; ?></span>
                </div>
            </header>

            <div class="admin-content">
                <div class="overview-grid">
                    <div class="stat-card">
                        <h3>Total Posts</h3>
                        <p class="stat-number"><?php echo $postCount; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Events</h3>
                        <p class="stat-number"><?php echo $eventCount; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Requests</h3>
                        <p class="stat-number"><?php echo $pendingRequests; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Requests</h3>
                        <p class="stat-number"><?php echo $requestCount; ?></p>
                    </div>
                </div>

                <!-- Recent Posts -->
                <div class="recent-section">
                    <h2>Recent Posts</h2>
                    <div class="items-list">
                        <?php 
                        $posts->data_seek(0);
                        $count = 0;
                        while (($post = $posts->fetch_assoc()) && $count < 5): 
                            $count++;
                        ?>
                        <div class="item-card">
                            <div class="item-info">
                                <h4><?php echo $post['title']; ?></h4>
                                <p class="item-meta">By <?php echo $post['author']; ?> • <?php echo date('M d, Y', strtotime($post['created_at'])); ?></p>
                                <span class="item-badge <?php echo $post['is_published'] ? 'published' : 'draft'; ?>">
                                    <?php echo $post['is_published'] ? 'Published' : 'Draft'; ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
