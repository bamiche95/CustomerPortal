<?php
session_start();
require_once 'header.php';
require_once 'PortalSidebar.php';
require_once __DIR__ . '/../app/config/db.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /bookingportal/public/login.php");
    exit;
}

// Role-based protection
if ($_SESSION['role'] === 'viewer') {
    // Redirect viewers to their files page
    header("Location: /bookingportal/portal/files.php");
    exit;
} elseif ($_SESSION['role'] !== 'booker' && $_SESSION['role'] !== 'admin') {
    // Block any other unexpected roles
    echo "<main class='col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4'>
            <div class='alert alert-danger'>Access denied.</div>
          </main>";
    require_once 'portalfooter.php';
    exit;
}

$company_id = $_SESSION['company_id'];

// Fetch bookings for this company
$sql = "SELECT b.id, b.booking_number, b.bin_number, b.container_type, b.location, b.`Additional Service`, u.name AS booked_by, 
        u.email AS booked_email, b.created_at
        FROM recbook_bookings b
        LEFT JOIN recbook_users u ON b.user_id = u.id
        WHERE b.company_id = ?
        ORDER BY b.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $company_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
    
    <div class="d-flex justify-content-between flex-wrap align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">Booking List 🗓️</h1>
        <a href="/bookingportal/portal/booking-new.php" class="btn btn-primary add-booking-btn rounded-pill">
            <i class="bi bi-plus-circle me-1"></i> Add Booking
        </a>
    </div>

    <div class="row g-3 mb-4 align-items-end">
        <div class="col-md-5">
            <label for="searchInput" class="form-label text-muted">
                <i class="bi bi-search me-1"></i>Search Bookings
            </label>
            <input type="text" id="searchInput" class="form-control rounded-pill" placeholder="Search by booking number, bin, or location...">
        </div>
        <div class="col-md-4">
            <label for="dateFilter" class="form-label text-muted">
                <i class="bi bi-calendar-date me-1"></i>Filter by Date
            </label>
            <input type="date" id="dateFilter" class="form-control rounded-pill">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button id="clearFilters" class="btn btn-outline-secondary w-100 rounded-pill">
                <i class="bi bi-x-circle me-1"></i>Clear Filters
            </button>
        </div>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive shadow-lg rounded-3" style="max-height: 65vh; overflow-y: auto;">
            <table class="table table-hover table-striped align-middle mb-0" id="bookingsTable">
                <thead class="bg-dark text-white sticky-top">
                    <tr>
                        <th>Booking Number</th>
                        <th>Bin Number</th>
                        <th>Container Type</th>
                        <th>Location</th>
                        <th>Additional Service</th>
                        <th>Booked By</th>
                        <th>Booked Email</th>
                        <th>Created At</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['booking_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['bin_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['container_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td><?php echo htmlspecialchars($row['Additional Service']); ?></td>
                            <td><?php echo htmlspecialchars($row['booked_by']); ?></td>
                            <td><?php echo htmlspecialchars($row['booked_email']); ?></td>
                            <td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                            <td class="text-center">
                                <a href="/bookingportal/portal/booking-detail.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-primary rounded-pill">
                                   <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info shadow-sm rounded-3 p-4">
            <i class="bi bi-info-circle-fill me-2"></i> No bookings found for your company.
        </div>
    <?php endif; ?>
</main>

<script>
// Client-side filtering
const searchInput = document.getElementById("searchInput");
const dateFilter = document.getElementById("dateFilter");
const clearFiltersBtn = document.getElementById("clearFilters");
const tableRows = document.querySelectorAll("#bookingsTable tbody tr");

searchInput.addEventListener("keyup", filterTable);
dateFilter.addEventListener("change", filterTable);
clearFiltersBtn.addEventListener("click", () => {
    searchInput.value = "";
    dateFilter.value = "";
    filterTable();
});

function filterTable() {
    const search = searchInput.value.toLowerCase();
    const date = dateFilter.value;

    tableRows.forEach(row => {
        const textContent = (row.cells[0].textContent + " " + row.cells[1].textContent + " " + row.cells[3].textContent).toLowerCase();
        const createdDate = new Date(row.cells[7].textContent).toISOString().split('T')[0];

        const matchesSearch = textContent.includes(search);
        const matchesDate = !date || createdDate === date;

        row.style.display = (matchesSearch && matchesDate) ? "" : "none";
    });
}
</script>

<?php
require_once 'portalfooter.php';
?>