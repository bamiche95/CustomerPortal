<?php
session_start();
require_once 'header.php';
require_once 'admin-sidebar.php';
require_once __DIR__ . '/../app/config/db.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /bookingportal/portal/index.php");
    exit;
}

// Validate booking ID
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">Booking Details 📝</h1>
    </div>

    <?php
    if ($booking_id <= 0) {
        echo "<div class='alert alert-danger text-center shadow-sm rounded-pill mt-4'><i class='bi bi-x-circle-fill me-2'></i>Invalid booking ID.</div>";
    } else {
        // Fetch booking details including company and user info
        $sql = "SELECT b.*, u.name AS booked_by, u.email AS booked_email, 
                        c.name AS company_name
                FROM recbook_bookings b
                LEFT JOIN recbook_users u ON b.user_id = u.id
                LEFT JOIN recbook_companies c ON b.company_id = c.id
                WHERE b.id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $booking_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)):
    ?>
    <div class="card shadow-lg rounded-3 mb-4">
        <div class="card-header bg-dark text-white rounded-top-3">
            <h5 class="card-title mb-0 fw-bold">
                Booking #<?php echo htmlspecialchars($row['booking_number']); ?>
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-2"><strong><i class="bi bi-building me-2"></i>Company:</strong> <br> <?php echo htmlspecialchars($row['company_name']); ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong><i class="bi bi-person me-2"></i>Booked By:</strong> <br> <?php echo htmlspecialchars($row['booked_by']); ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong><i class="bi bi-envelope me-2"></i>Email:</strong> <br> <?php echo htmlspecialchars($row['booked_email']); ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong><i class="bi bi-calendar-check me-2"></i>Created At:</strong> <br> <?php echo date("d M Y, H:i", strtotime($row['created_at'])); ?></p>
                </div>
            </div>
            <hr class="my-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <p class="mb-2"><strong><i class="bi bi-trash-fill me-2"></i>Bin Number:</strong> <br> <?php echo htmlspecialchars($row['bin_number']); ?></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-2"><strong><i class="bi bi-box me-2"></i>Container Type:</strong> <br> <?php echo htmlspecialchars($row['container_type']); ?></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-2"><strong><i class="bi bi-geo-alt-fill me-2"></i>Location:</strong> <br> <?php echo htmlspecialchars($row['location']); ?></p>
                </div>
                <div class="col-12">
                    <p class="mb-2"><strong><i class="bi bi-tools me-2"></i>Additional Service:</strong> <br> <?php echo htmlspecialchars($row['Additional Service']); ?></p>
                </div>
            </div>
            
            <?php if (!empty($row['signature'])): ?>
                <hr class="my-4">
                <div class="mt-3">
                    <h6 class="fw-bold"><i class="bi bi-pencil-square me-2"></i>Signature:</h6>
                    <div class="border rounded-3 p-2 d-inline-block">
                        <img src="/bookingportal/<?php echo htmlspecialchars($row['signature']); ?>" 
                             alt="Signature" class="img-fluid" style="max-width: 300px;">
                    </div>
                </div>
            <?php else: ?>
                <hr class="my-4">
                <p><strong><i class="bi bi-pencil-square me-2"></i>Signature:</strong> Not available</p>
            <?php endif; ?>
        </div>
    </div>

    <a href="/bookingportal/portal/admin-booking.php" class="btn btn-outline-secondary rounded-pill mt-3">
        <i class="bi bi-arrow-left me-2"></i> Back to All Bookings
    </a>

    <?php
    else:
        echo "<div class='alert alert-danger text-center shadow-sm rounded-pill mt-4'><i class='bi bi-x-circle-fill me-2'></i>Booking not found.</div>";
    endif;
    }

    require_once 'portalfooter.php';
    ?>
</main>