<?php
session_start();
require_once 'header.php';
require_once 'admin-sidebar.php';
require_once __DIR__ . '/../app/config/db.php';

// Protect page: only admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /bookingportal/portal/index.php");
    exit;
}

// Fetch all bookings with user and company info
$sql = "SELECT b.id, b.booking_number, b.bin_number, b.container_type, b.location, b.`Additional Service`,
                u.name AS booked_by, u.email AS booked_email, c.name AS company_name, b.created_at
        FROM recbook_bookings b
        LEFT JOIN recbook_users u ON b.user_id = u.id
        LEFT JOIN recbook_companies c ON b.company_id = c.id
        ORDER BY b.created_at DESC";
$result = mysqli_query($conn, $sql);

// Fetch distinct companies and bookers for filters
$companies = mysqli_query($conn, "SELECT DISTINCT name FROM recbook_companies ORDER BY name ASC");
$bookers = mysqli_query($conn, "SELECT DISTINCT name FROM recbook_users ORDER BY name ASC");
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">All Bookings 📋</h1>
    </div>

    <div class="row mb-4 g-3 align-items-end">
        <div class="col-md-3">
            <label for="filterCompany" class="form-label text-muted">
                <i class="bi bi-building me-1"></i>Filter by Company
            </label>
            <select id="filterCompany" class="form-select rounded-pill">
                <option value="">All Companies</option>
                <?php while ($c = mysqli_fetch_assoc($companies)): ?>
                    <option value="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterBooker" class="form-label text-muted">
                <i class="bi bi-person me-1"></i>Filter by Booker
            </label>
            <select id="filterBooker" class="form-select rounded-pill">
                <option value="">All Bookers</option>
                <?php while ($b = mysqli_fetch_assoc($bookers)): ?>
                    <option value="<?php echo htmlspecialchars($b['name']); ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterDate" class="form-label text-muted">
                <i class="bi bi-calendar-date me-1"></i>Filter by Date
            </label>
            <input type="date" id="filterDate" class="form-control rounded-pill">
        </div>
        <div class="col-md-3">
            <label for="globalSearch" class="form-label text-muted">
                <i class="bi bi-search me-1"></i>Search All
            </label>
            <input type="text" id="globalSearch" class="form-control rounded-pill" placeholder="Search...">
        </div>
        <div class="col-12 text-end">
            <button id="clearFilters" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-x-circle me-1"></i> Clear Filters
            </button>
        </div>
    </div>

    <div class="table-responsive shadow-lg rounded-3" style="max-height: 65vh; overflow-y: auto;">
        <table id="bookingsTable" class="table table-striped table-hover align-middle mb-0">
            <thead class="bg-dark text-white sticky-top">
                <tr>
                    <th>#</th>
                    <th>Booking Number</th>
                    <th>Bin Number</th>
                    <th>Container Type</th>
                    <th>Location</th>
                    <th>Additional Service</th>
                    <th>Booked By</th>
                    <th>Booked Email</th>
                    <th>Company</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td></td>
                            <td><?php echo htmlspecialchars($row['booking_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['bin_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['container_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td><?php echo htmlspecialchars($row['Additional Service']); ?></td>
                            <td><?php echo htmlspecialchars($row['booked_by']); ?></td>
                            <td><?php echo htmlspecialchars($row['booked_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="/bookingportal/portal/admin-booking-detail.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-primary rounded-pill">
                                   <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="11" class="text-center p-4">
                        <div class="alert alert-info border-0 m-0">No bookings found.</div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#bookingsTable').DataTable({
        "order": [[ 9, "desc" ]], // sort by Created At descending
        "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csvHtml5',
                text: '<i class="bi bi-download"></i> Export CSV',
                className: 'btn btn-primary mb-2 rounded-pill',
                title: 'Bookings Export'
            }
        ],
        columnDefs: [
            { targets: 0, searchable: false, orderable: false } // S/N column
        ],
        // Disable DataTables built-in search functionality
        initComplete: function () {
            this.api().columns().every(function () {
                var column = this;
                var select = $(column.header()).find('select');
                if (select.length) {
                    select.off('.dt');
                }
            });
            $('#bookingsTable_filter').hide();
        }
    });

    // Dynamic serial numbering
    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search:'applied', order:'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();

    // Filters
    $('#filterCompany').on('change', function() {
        table.column(8).search(this.value).draw();
    });
    $('#filterBooker').on('change', function() {
        table.column(6).search(this.value).draw();
    });
    $('#filterDate').on('change', function() {
        table.column(9).search(this.value).draw();
    });

    // Global search
    $('#globalSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
    // Clear all filters and global search
    $('#clearFilters').on('click', function() {
        $('#filterCompany').val('');
        $('#filterBooker').val('');
        $('#filterDate').val('');
        $('#globalSearch').val('');

        table.search('').columns().search('').draw();
    });
});
</script>

<?php
require_once 'portalfooter.php';
?>