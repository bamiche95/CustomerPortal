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

// Role protection
if ($_SESSION['role'] === 'booker') {
    // Redirect bookers to their booking dashboard
    header("Location: /bookingportal/portal/booking.php");
    exit;
} elseif ($_SESSION['role'] !== 'viewer') {
    // Block any other unexpected roles
    echo "<main class='col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4'>
            <div class='alert alert-danger'>Access denied.</div>
          </main>";
    require_once 'portalfooter.php';
    exit;
}

$companyId = $_SESSION['company_id'];
$role = $_SESSION['role'];

// Fetch files for this company, restricted to visible_to_role
$sql = "SELECT f.id, f.filename, f.filepath, f.uploaded_at, f.category_id, 
                c.name AS category_name, c.description AS category_description
        FROM recbook_files f
        LEFT JOIN file_categories c ON f.category_id = c.id
        WHERE f.company_id = ? AND f.visible_to_role = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $companyId, $role);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">Your Documents 📁</h1>
    </div>

    <div class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label for="searchInput" class="form-label text-muted">
                <i class="bi bi-search me-1"></i>Search
            </label>
            <input type="text" id="searchInput" class="form-control rounded-pill" placeholder="Search by filename...">
        </div>
        <div class="col-md-3">
            <label for="categoryFilter" class="form-label text-muted">
                <i class="bi bi-tags me-1"></i>Category
            </label>
            <select id="categoryFilter" class="form-select rounded-pill">
                <option value="">All Categories</option>
                <?php
                // Fetch categories for filter dropdown
                $categoriesResult = mysqli_query($conn, "SELECT id, name FROM file_categories ORDER BY name ASC");
                while ($cat = mysqli_fetch_assoc($categoriesResult)) {
                    echo '<option value="' . htmlspecialchars($cat['name']) . '">' . htmlspecialchars($cat['name']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="dateFilter" class="form-label text-muted">
                <i class="bi bi-calendar-date me-1"></i>Date
            </label>
            <input type="date" id="dateFilter" class="form-control rounded-pill">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button id="clearFilters" class="btn btn-outline-secondary w-100 rounded-pill">
                <i class="bi bi-x-circle me-1"></i>Clear
            </button>
        </div>
    </div>

    <div class="table-responsive shadow-lg rounded-3" style="max-height: 65vh; overflow-y: auto;">
        <table class="table table-striped table-hover align-middle mb-0" id="filesTable">
            <thead class="bg-dark text-white sticky-top">
                <tr>
                    <th scope="col">Filename</th>
                    <th scope="col">Category</th>
                    <th scope="col">Description</th>
                    <th scope="col">Uploaded At</th>
                    <th scope="col" class="text-center">Download</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="text-truncate" style="max-width: 250px;">
                                <i class="bi bi-file-earmark-text me-2"></i><?= htmlspecialchars($row['filename']); ?>
                            </td>
                            <td><?= htmlspecialchars($row['category_name']); ?></td>
                            <td class="text-truncate" style="max-width: 300px;">
                                <?= htmlspecialchars($row['category_description'] ?? 'N/A'); ?>
                            </td>
                            <td><?= date("d M Y", strtotime($row['uploaded_at'])); ?></td>
                            <td class="text-center">
                                <a href="/bookingportal/<?= htmlspecialchars($row['filepath']); ?>" class="btn btn-sm btn-primary rounded-pill" download>
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center p-4">
                        <div class="alert alert-info border-0 m-0">
                            No files are currently available for your company.
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
// Filters + Search
const searchInput = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const dateFilter = document.getElementById("dateFilter");
const clearFiltersBtn = document.getElementById("clearFilters");
const tableRows = document.querySelectorAll("#filesTable tbody tr");

searchInput.addEventListener("keyup", filterTable);
categoryFilter.addEventListener("change", filterTable);
dateFilter.addEventListener("change", filterTable);
clearFiltersBtn.addEventListener("click", () => {
    searchInput.value = "";
    categoryFilter.value = "";
    dateFilter.value = "";
    filterTable();
});

function filterTable() {
    const search = searchInput.value.toLowerCase();
    const category = categoryFilter.value.toLowerCase();
    const date = dateFilter.value;

    tableRows.forEach(row => {
        // Skip the 'No files' row if it exists
        if (row.cells.length < 5) return;

        const filename = row.cells[0].textContent.toLowerCase();
        const rowCategory = row.cells[1].textContent.toLowerCase();
        const uploadedAt = row.cells[3].textContent.split(" ")[0].trim();

        const matchesSearch = filename.includes(search);
        const matchesCategory = category === "" || rowCategory.includes(category);
        const matchesDate = date === "" || uploadedAt.replace(/ /g, '-') === date; // Match date format

        row.style.display = (matchesSearch && matchesCategory && matchesDate) ? "" : "none";
    });
}
</script>

<?php
require_once 'portalfooter.php';
?>