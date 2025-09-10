<?php
session_start();
require_once 'header.php';
require_once 'PortalSidebar.php';
require_once __DIR__ . '/../app/config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /bookingportal/public/login.php");
    exit;
}

// Validate booking ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
            <div class="alert alert-danger shadow-sm rounded-3 p-4">
                <i class="bi bi-x-octagon-fill me-2"></i>Invalid booking ID.
            </div>
          </main>';
    exit;
}

$booking_id = intval($_GET['id']);
$company_id = $_SESSION['company_id'];

// Fetch booking including signature
$sql = "SELECT b.id, b.booking_number, b.bin_number, b.container_type, b.location, b.`Additional Service`, 
               b.created_at, u.name AS booked_by, u.email AS booked_email, b.signature
        FROM recbook_bookings b
        LEFT JOIN recbook_users u ON b.user_id = u.id
        WHERE b.id = ? AND b.company_id = ? LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $company_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)):
    $signature_web = !empty($row['signature']) ? '/bookingportal/' . $row['signature'] : null;
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">Booking Details <i class="bi bi-calendar-check-fill text-primary"></i></h1>
    </div>

    <div class="card shadow-lg border-0 rounded-3 mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold">
                Booking #<?php echo htmlspecialchars($row['booking_number']); ?>
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <p class="mb-1"><strong class="text-muted">Bin Number:</strong></p>
                    <p class="fw-bold fs-5"><?php echo htmlspecialchars($row['bin_number']); ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <p class="mb-1"><strong class="text-muted">Container Type:</strong></p>
                    <p class="fw-bold fs-5"><?php echo htmlspecialchars($row['container_type']); ?></p>
                </div>
            </div>
            
            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <p class="mb-1"><strong class="text-muted">Location On Site:</strong></p>
                    <p><?php echo htmlspecialchars($row['location']); ?></p>
                </div>
                <div class="col-md-6 mb-3">
                    <p class="mb-1"><strong class="text-muted">Booked By:</strong></p>
                    <p><?php echo htmlspecialchars($row['booked_by']); ?> (<?php echo htmlspecialchars($row['booked_email']); ?>)</p>
                </div>
            </div>

            <div class="mb-3">
                <p class="mb-1"><strong class="text-muted">Additional Service:</strong></p>
                <p class="fst-italic"><?php echo htmlspecialchars($row['Additional Service']); ?></p>
            </div>
            
            <div class="mb-3">
                <p class="mb-1"><strong class="text-muted">Created At:</strong></p>
                <p><?php echo date("d M Y, h:i A", strtotime($row['created_at'])); ?></p>
            </div>

            <?php if ($signature_web && file_exists(__DIR__ . '/../' . $row['signature'])): ?>
                <div class="mt-4">
                    <p class="mb-1"><strong class="text-muted">Signature:</strong></p>
                    <img src="<?php echo $signature_web; ?>" alt="Signature" class="img-fluid border rounded-3 p-2" style="max-width:400px; background-color: #fff;">
                </div>
            <?php else: ?>
                <div class="mt-4">
                    <p class="mb-1"><strong class="text-muted">Signature:</strong></p>
                    <div class="alert alert-warning my-2">No signature provided.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <a href="/bookingportal/portal/booking.php" class="btn btn-secondary rounded-pill">
        <i class="bi bi-arrow-left me-2"></i>Back to Bookings
    </a>
</main>

<?php
else:
    echo '<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
            <div class="alert alert-warning shadow-sm rounded-3 p-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Booking not found or you do not have permission to view it.
            </div>
          </main>';
endif;

require_once 'portalfooter.php';
?>