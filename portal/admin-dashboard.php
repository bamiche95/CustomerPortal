<?php
session_start();
require_once __DIR__ . '/../app/config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /bookingportal/public/login.php");
    exit;
}

// Redirect non-admin users to the regular portal
if ($_SESSION['role'] !== 'admin') {
    header("Location: /bookingportal/portal/index.php");
    exit;
}

// Optionally, fetch some admin stats for display
// Example: total users, total bookings, total files
$stats = [];

// Total users
$result = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM recbook_users");
$stats['users'] = mysqli_fetch_assoc($result)['total_users'] ?? 0;

// Total bookings
$result = mysqli_query($conn, "SELECT COUNT(*) AS total_bookings FROM recbook_bookings");
$stats['bookings'] = mysqli_fetch_assoc($result)['total_bookings'] ?? 0;

// Total files
$result = mysqli_query($conn, "SELECT COUNT(*) AS total_files FROM recbook_files");
$stats['files'] = mysqli_fetch_assoc($result)['total_files'] ?? 0;

require_once 'header.php';
require_once 'admin-sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">Admin Dashboard ✨</h1>
    </div>
    
    <div class="alert alert-info shadow-sm rounded-3 p-4 mb-4">
        <h4 class="alert-heading"><i class="bi bi-person-check-fill me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h4>
        <p class="mb-0">
            This is your control center. Use the sidebar to manage all aspects of the portal, including bookings, users, and company files.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary shadow-lg border-0 rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-people-fill display-4 me-3"></i>
                        <div>
                            <p class="card-text text-uppercase fw-bold mb-1">Total Users</p>
                            <h2 class="display-4 fw-bold mb-0"><?php echo $stats['users']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success shadow-lg border-0 rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-journal-bookmark-fill display-4 me-3"></i>
                        <div>
                            <p class="card-text text-uppercase fw-bold mb-1">Total Bookings</p>
                            <h2 class="display-4 fw-bold mb-0"><?php echo $stats['bookings']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-secondary shadow-lg border-0 rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-fill display-4 me-3"></i>
                        <div>
                            <p class="card-text text-uppercase fw-bold mb-1">Total Files</p>
                            <h2 class="display-4 fw-bold mb-0"><?php echo $stats['files']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once 'portalfooter.php';
?>