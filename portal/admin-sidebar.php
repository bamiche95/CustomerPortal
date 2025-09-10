<?php
require_once __DIR__ . '/../app/controllers/UserController.php';

// The UserController.php should set $currentUser based on the session.
// Assuming $currentUser is an array with 'name', 'role', and 'company_name'.
// For this sidebar to work, make sure this variable is available.
// Example:
// session_start();
// ... db connection logic ...
// $stmt = $conn->prepare("SELECT u.name, u.role, c.name as company_name FROM recbook_users u LEFT JOIN recbook_companies c ON u.company_id = c.id WHERE u.id = ?");
// $stmt->bind_param("i", $_SESSION['user_id']);
// $stmt->execute();
// $result = $stmt->get_result();
// $currentUser = $result->fetch_assoc();
?>

<nav id="sidebarMenu" class="col-md-2 col-lg-2 d-md-block bg-dark sidebar collapse vh-100 shadow-lg">
    <div class="d-flex flex-column h-100">
        <h5 class="px-3 py-3 text-white border-bottom border-secondary mb-0">
            <?php echo $currentUser ? htmlspecialchars($currentUser['company_name']) : 'Business Name'; ?>
        </h5>

        <div class="flex-grow-1 overflow-auto">
            <ul class="nav flex-column py-2">
                <li class="nav-item">
                    <a class="nav-link text-white active fw-bold" href="/bookingportal/portal/admin-dashboard.php">
                        <i class="bi bi-grid-fill me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/bookingportal/portal/admin-booking.php">
                        <i class="bi bi-calendar2-check-fill me-2"></i> Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/bookingportal/portal/admin-files.php">
                        <i class="bi bi-folder-fill me-2"></i> Files
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/bookingportal/portal/user.php">
                        <i class="bi bi-people-fill me-2"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/bookingportal/portal/company.php">
                        <i class="bi bi-building-fill me-2"></i> Companies
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="mt-auto p-3 border-top border-secondary">
            <div class="d-flex align-items-center mb-2">
                <div class="me-2">
                    <i class="bi bi-person-circle fs-4 text-white"></i>
                </div>
                <div>
                    <span class="d-block text-white fw-bold">
                        <?= htmlspecialchars($currentUser['name'] ?? 'User'); ?>
                    </span>
                    <small class="text-secondary text-capitalize">
                        <?= htmlspecialchars($currentUser['role'] ?? 'Role'); ?>
                    </small>
                </div>
            </div>
            <a href="/bookingportal/app/controllers/AuthController.php?action=logout" class="btn btn-outline-danger w-100 rounded-pill">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>
    </div>
</nav>