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

// Fetch files with company and category info
$sql = "SELECT f.id, f.filename, f.filepath, f.visible_to_role, f.company_id, f.uploaded_at, 
                c.name AS company_name, u.name AS uploader_name, fc.name AS category_name
        FROM recbook_files f
        LEFT JOIN recbook_companies c ON f.company_id = c.id
        LEFT JOIN recbook_users u ON f.uploaded_by = u.id
        LEFT JOIN file_categories fc ON f.category_id = fc.id
        ORDER BY f.uploaded_at DESC";

$result = mysqli_query($conn, $sql);

// Fetch distinct companies and categories for filters
$companies = mysqli_query($conn, "SELECT id, name FROM recbook_companies ORDER BY name ASC");
$categories = mysqli_query($conn, "SELECT id, name FROM file_categories ORDER BY name ASC");
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">File Management Dashboard 📂</h1>
        <button class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
            <i class="bi bi-cloud-arrow-up-fill me-2"></i> Upload New File
        </button>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-pill" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-pill" role="alert">
            <i class="bi bi-x-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row mb-4 g-3 align-items-end">
        <div class="col-md-3">
            <label for="filterCompany" class="form-label text-muted">
                <i class="bi bi-building me-1"></i>Filter by Company
            </label>
            <select id="filterCompany" class="form-select rounded-pill">
                <option value="">All Companies</option>
                <?php mysqli_data_seek($companies, 0); // Reset result pointer ?>
                <?php while ($c = mysqli_fetch_assoc($companies)): ?>
                    <option value="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterCategory" class="form-label text-muted">
                <i class="bi bi-tags me-1"></i>Filter by Category
            </label>
            <select id="filterCategory" class="form-select rounded-pill">
                <option value="">All Categories</option>
                <?php mysqli_data_seek($categories, 0); // Reset result pointer ?>
                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterRole" class="form-label text-muted">
                <i class="bi bi-person-workspace me-1"></i>Visible To Role
            </label>
            <select id="filterRole" class="form-select rounded-pill">
                <option value="">All Roles</option>
                <option value="viewer">Viewer</option>
                <option value="booker">Booker</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="globalSearch" class="form-label text-muted">
                <i class="bi bi-search me-1"></i>Search
            </label>
            <input type="text" id="globalSearch" class="form-control rounded-pill" placeholder="Search all columns">
        </div>
        <div class="col-12 text-end">
            <button id="clearFilters" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-x-circle me-1"></i> Clear Filters
            </button>
        </div>
    </div>

    <div class="table-responsive shadow-lg rounded-3" style="max-height: 65vh; overflow-y: auto;">
        <table id="filesTable" class="table table-striped table-hover align-middle mb-0">
            <thead class="bg-dark text-white sticky-top">
                <tr>
                    <th>#</th>
                    <th><i class="bi bi-file-earmark-text me-1"></i>File Name</th>
                    <th><i class="bi bi-building me-1"></i>Company</th>
                    <th><i class="bi bi-person-workspace me-1"></i>Visible To</th>
                    <th><i class="bi bi-person me-1"></i>Uploaded By</th>
                    <th><i class="bi bi-tags me-1"></i>Category</th>
                    <th><i class="bi bi-file-earmark me-1"></i>File Type</th>
                    <th><i class="bi bi-calendar-check me-1"></i>Uploaded At</th>
                    <th><i class="bi bi-gear-fill me-1"></i>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td></td>
                            <td class="text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($row['filename']); ?></td>
                            <td><?php echo htmlspecialchars($row['company_name'] ?? 'N/A'); ?></td>
                            <td class="text-capitalize"><?php echo htmlspecialchars($row['visible_to_role']); ?></td>
                            <td><?php echo htmlspecialchars($row['uploader_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo strtoupper(pathinfo($row['filename'], PATHINFO_EXTENSION)); ?>
                                </span>
                            </td>
                            <td><?php echo date("d M Y, H:i", strtotime($row['uploaded_at'])); ?></td>
                            <td>
                                <a href="/bookingportal/<?php echo htmlspecialchars($row['filepath']); ?>" 
                                   class="btn btn-sm btn-primary rounded-pill" target="_blank">
                                   <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center p-4">
                        <div class="alert alert-info border-0 m-0">No files found.</div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form action="/bookingportal/app/controllers/FileController.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="uploadFileModalLabel">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i> Upload New File
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="fileName" class="form-label text-muted"><i class="bi bi-folder me-2"></i>File Name</label>
                        <input type="text" name="filename" id="fileName" class="form-control rounded-pill" placeholder="Enter file name (e.g., Annual Report)" required>
                    </div>

                    <div class="mb-3">
                        <label for="companyId" class="form-label text-muted"><i class="bi bi-building me-2"></i>Company</label>
                        <select name="company_id" id="companyId" class="form-select rounded-pill" required>
                            <option value="">Select Company</option>
                            <?php
                            mysqli_data_seek($companies, 0); // Reset pointer for the modal form
                            while ($comp = mysqli_fetch_assoc($companies)) {
                                echo '<option value="'. $comp['id'] .'">'. htmlspecialchars($comp['name']) .'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="categoryId" class="form-label text-muted"><i class="bi bi-tags me-2"></i>Category</label>
                        <select name="category_id" id="categoryId" class="form-select rounded-pill" required>
                            <option value="">Select Category</option>
                            <?php
                            mysqli_data_seek($categories, 0); // Reset pointer for the modal form
                            while ($cat = mysqli_fetch_assoc($categories)) {
                                echo '<option value="'. $cat['id'] .'">'. htmlspecialchars($cat['name']) .'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="fileInput" class="form-label text-muted"><i class="bi bi-file-earmark-arrow-up me-2"></i>Choose File</label>
                        <input type="file" name="file" id="fileInput" class="form-control rounded-pill" required>
                    </div>

                    <div class="mb-3">
                        <label for="visibleRole" class="form-label text-muted"><i class="bi bi-person-fill-gear me-2"></i>Visible To Role</label>
                        <select name="visible_to_role" id="visibleRole" class="form-select rounded-pill">
                            <option value="viewer" selected>Viewer</option>
                            <option value="booker">Booker</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="upload_file" class="btn btn-primary rounded-pill">
                        <i class="bi bi-upload me-2"></i> Upload File
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
    var table = $('#filesTable').DataTable({
        "order": [[ 7, "desc" ]], // sort by Uploaded At descending
        "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csvHtml5',
                text: '<i class="bi bi-download"></i> Export CSV',
                className: 'btn btn-primary rounded-pill me-2',
                title: 'Files Export'
            }
        ],
        columnDefs: [
            { targets: 0, searchable: false, orderable: false }
        ]
    });

    // Dynamic serial numbering
    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search:'applied', order:'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();

    // Hide DataTables built-in search input and buttons
    $('#filesTable_filter').hide();

    // Filters
    $('#filterCompany').on('change', function() { table.column(2).search(this.value).draw(); });
    $('#filterCategory').on('change', function() { table.column(5).search(this.value).draw(); });
    $('#filterRole').on('change', function() { table.column(3).search(this.value).draw(); });

    // Global search
    $('#globalSearch').on('keyup', function() { table.search(this.value).draw(); });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#filterCompany').val('');
        $('#filterCategory').val('');
        $('#filterRole').val('');
        $('#globalSearch').val('');
        table.search('').columns().search('').draw();
    });
});
</script>

<?php
require_once 'portalfooter.php';
?>