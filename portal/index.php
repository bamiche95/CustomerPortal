<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: http://localhost:8080/bookingportal/public/index.php");
    exit;
}

require_once 'header.php';
require_once 'PortalSidebar.php';
?>



<!-- Main content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <h1 class="h2">Welcome to your Portal</h1>
    <p>This is a placeholder for the dashboard content.</p>
</main>

<?php
require_once 'portalfooter.php';
?>
