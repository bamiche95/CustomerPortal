<?php
require_once __DIR__ . '/../app/controllers/UserController.php';

// The UserController.php file should fetch the current user details
// and set them in the $currentUser variable.
// Example (pseudo-code):
// $currentUser = UserController::getCurrentUser();
// Assuming a structure like ['name' => 'John Doe', 'company_name' => 'Acme Corp']
?>

<aside id="sidebarMenu" class="d-flex flex-column flex-shrink-0 p-3 bg-dark vh-100 position-fixed" style="width: 280px; top: 0; left: 0;">
    <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-5 fw-bold text-uppercase">
            <i class="bi bi-person-circle me-2"></i>
            <?= $currentUser ? htmlspecialchars($currentUser['company_name']) : 'Dashboard'; ?>
        </span>
    </a>
    <hr class="text-white-50">
    <ul class="nav nav-pills flex-column mb-auto">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <?php if ($_SESSION['role'] === 'booker'): ?>
            <li class="nav-item">
                <a href="/bookingportal/portal/booking.php" class="nav-link text-white <?= ($current_page === 'booking.php' || $current_page === 'booking-new.php' || $current_page === 'booking-detail.php') ? 'active bg-primary' : ''; ?>" aria-current="page">
                    <i class="bi bi-calendar-check-fill me-2"></i>
                    Bookings
                </a>
            </li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'viewer'): ?>
            <li class="nav-item">
                <a href="/bookingportal/portal/files.php" class="nav-link text-white <?= ($current_page === 'files.php') ? 'active bg-primary' : ''; ?>">
                    <i class="bi bi-folder2-open me-2"></i>
                    Files
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <hr class="text-white-50">
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-fill me-2"></i>
            <strong><?= $currentUser ? htmlspecialchars($currentUser['name']) : 'User'; ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
            
            <li><a class="dropdown-item" href="/bookingportal/public/logout.php">Sign out</a></li>
        </ul>
    </div>
</aside>