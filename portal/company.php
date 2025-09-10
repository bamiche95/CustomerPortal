<?php
session_start();
require_once 'header.php';
require_once 'admin-Sidebar.php';
require_once __DIR__ . '/../app/config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: http://localhost:8080/bookingportal/public/index.php");
    exit;
}

// Fetch all companies
$sql = "SELECT id, name, created_at FROM recbook_companies ORDER BY name ASC";
$result = mysqli_query($conn, $sql);
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">Companies 🏢</h1>
        <button class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
            <i class="bi bi-building-add me-2"></i> Add New Company
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

    <div class="row g-3 mb-4 align-items-end">
        <div class="col-md-6">
            <label for="searchInput" class="form-label text-muted">
                <i class="bi bi-search me-1"></i>Search Companies
            </label>
            <input type="text" id="searchInput" class="form-control rounded-pill" placeholder="Search companies by name...">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button id="clearFilters" class="btn btn-outline-secondary w-100 rounded-pill">
                <i class="bi bi-x-circle me-1"></i> Clear
            </button>
        </div>
    </div>

    <div class="table-responsive shadow-lg rounded-3" style="max-height: 65vh; overflow-y: auto;">
        <table class="table table-striped table-hover align-middle mb-0" id="companiesTable">
            <thead class="bg-dark text-white sticky-top">
                <tr>
                    <th><i class="bi bi-building me-1"></i>Company Name</th>
                    <th><i class="bi bi-calendar-check me-1"></i>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= date("d M Y, H:i", strtotime(htmlspecialchars($row['created_at']))); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="text-center p-4">
                        <div class="alert alert-info border-0 m-0">No companies found.</div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="modal fade" id="addCompanyModal" tabindex="-1" aria-labelledby="addCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form action="/bookingportal/app/controllers/CompanyController.php" method="POST">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="addCompanyModalLabel">
                        <i class="bi bi-building-add me-2"></i> Add New Company
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="company_name" class="form-label text-muted">
                            <i class="bi bi-pencil-square me-2"></i>Company Name
                        </label>
                        <input type="text" name="name" id="company_name" class="form-control rounded-pill" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_company" class="btn btn-primary rounded-pill">
                        <i class="bi bi-plus-circle me-2"></i> Add Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Simple search filter
document.getElementById("searchInput").addEventListener("keyup", filterTable);
document.getElementById("clearFilters").addEventListener("click", () => {
    document.getElementById("searchInput").value = "";
    filterTable();
});

function filterTable() {
    const search = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#companiesTable tbody tr");

    rows.forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        row.style.display = (search === "" || name.includes(search)) ? "" : "none";
    });
}
</script>

<?php
require_once 'portalfooter.php';
?>