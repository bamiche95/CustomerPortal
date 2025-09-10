<?php
session_start();
require_once 'header.php';
require_once 'admin-sidebar.php';
require_once __DIR__ . '/../app/config/db.php';

// Check for user login and admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch companies for dropdown
$companies = mysqli_query($conn, "SELECT id, name FROM recbook_companies ORDER BY name ASC");

// Define available roles
$roles = ['admin' => 'Admin', 'booker' => 'Booker', 'viewer' => 'Viewer'];

// Fetch all users with company info
$sql = "SELECT u.id, u.username, u.name, u.email, u.role, c.name AS company_name, c.id AS company_id
        FROM recbook_users u
        LEFT JOIN recbook_companies c ON u.company_id = c.id
        ORDER BY u.name ASC";
$result = mysqli_query($conn, $sql);
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">User Accounts 👥</h1>
        <button class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill me-2"></i> Add New User
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
        <div class="col-md-4">
            <label for="searchInput" class="form-label text-muted">
                <i class="bi bi-search me-1"></i>Search Users
            </label>
            <input type="text" id="searchInput" class="form-control rounded-pill" placeholder="Search by name, email, etc...">
        </div>
        <div class="col-md-3">
            <label for="companyFilter" class="form-label text-muted">
                <i class="bi bi-building me-1"></i>Filter by Company
            </label>
            <select id="companyFilter" class="form-select rounded-pill">
                <option value="">All Companies</option>
                <?php
                mysqli_data_seek($companies, 0);
                while ($comp = mysqli_fetch_assoc($companies)) {
                    echo '<option value="' . htmlspecialchars($comp['name']) . '">' . htmlspecialchars($comp['name']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="roleFilter" class="form-label text-muted">
                <i class="bi bi-person-workspace me-1"></i>Filter by Role
            </label>
            <select id="roleFilter" class="form-select rounded-pill">
                <option value="">All Roles</option>
                <?php foreach ($roles as $key => $label): ?>
                    <option value="<?= $key; ?>"><?= $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button id="clearFilters" class="btn btn-outline-secondary w-100 rounded-pill">
                <i class="bi bi-x-circle me-1"></i>Clear
            </button>
        </div>
    </div>

    <div class="table-responsive shadow-lg rounded-3" style="max-height: 65vh; overflow-y: auto;">
        <table class="table table-striped table-hover align-middle mb-0" id="usersTable">
            <thead class="bg-dark text-white sticky-top">
                <tr>
                    <th><i class="bi bi-person me-1"></i>Name</th>
                    <th><i class="bi bi-building me-1"></i>Company</th>
                    <th><i class="bi bi-envelope me-1"></i>Email</th>
                    <th><i class="bi bi-person-circle me-1"></i>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr data-user-id="<?= $row['id']; ?>"
                            data-user-name="<?= htmlspecialchars($row['name'] ?? ''); ?>"
                            data-user-username="<?= htmlspecialchars($row['username'] ?? ''); ?>"
                            data-user-email="<?= htmlspecialchars($row['email'] ?? ''); ?>"
                            data-user-company-id="<?= htmlspecialchars($row['company_id'] ?? ''); ?>"
                            data-user-role="<?= htmlspecialchars($row['role'] ?? ''); ?>">
                            <td><?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['company_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-capitalize"><?= htmlspecialchars($row['role'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info edit-user-btn" data-bs-toggle="modal" data-bs-target="#editUserModal">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center p-4">
                        <div class="alert alert-info border-0 m-0">No users found.</div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form action="/bookingportal/app/controllers/UserController.php" method="POST">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="addUserModalLabel">
                        <i class="bi bi-person-plus-fill me-2"></i> Add New User
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="name" class="form-label text-muted">
                            <i class="bi bi-person me-2"></i>Full Name
                        </label>
                        <input type="text" name="name" id="name" class="form-control rounded-pill" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label text-muted">
                            <i class="bi bi-person-badge me-2"></i>Username
                        </label>
                        <input type="text" name="username" id="username" class="form-control rounded-pill" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input type="email" name="email" id="email" class="form-control rounded-pill" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_id" class="form-label text-muted">
                            <i class="bi bi-building me-2"></i>Company
                        </label>
                        <select name="company_id" id="company_id" class="form-select rounded-pill" required>
                            <option value="">Select Company</option>
                            <?php
                            mysqli_data_seek($companies, 0);
                            while ($comp = mysqli_fetch_assoc($companies)) {
                                echo '<option value="' . $comp['id'] . '">' . htmlspecialchars($comp['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label text-muted">
                            <i class="bi bi-person-workspace me-2"></i>Role
                        </label>
                        <select name="role" id="role" class="form-select rounded-pill" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="booker">Booker</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label text-muted">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                        <div class="input-group">
                            <input type="text" name="password" id="password" class="form-control rounded-start-pill" readonly required>
                            <button type="button" class="btn btn-outline-secondary rounded-end-pill" id="generatePassword">
                                <i class="bi bi-arrow-clockwise"></i> Generate
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">A strong password is auto-generated. You can regenerate if needed.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_user" class="btn btn-primary rounded-pill">
                        <i class="bi bi-check-circle me-2"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form action="/bookingportal/app/controllers/UserController.php" method="POST">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="editUserModalLabel">
                        <i class="bi bi-person-gear me-2"></i> Edit User
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="editName" class="form-label text-muted">
                            <i class="bi bi-person me-2"></i>Full Name
                        </label>
                        <input type="text" name="name" id="editName" class="form-control rounded-pill" required>
                    </div>
                    <div class="mb-3">
                        <label for="editUsername" class="form-label text-muted">
                            <i class="bi bi-person-badge me-2"></i>Username
                        </label>
                        <input type="text" name="username" id="editUsername" class="form-control rounded-pill" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label text-muted">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input type="email" name="email" id="editEmail" class="form-control rounded-pill" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCompanyId" class="form-label text-muted">
                            <i class="bi bi-building me-2"></i>Company
                        </label>
                        <select name="company_id" id="editCompanyId" class="form-select rounded-pill" required>
                            <option value="">Select Company</option>
                            <?php
                            mysqli_data_seek($companies, 0);
                            while ($comp = mysqli_fetch_assoc($companies)) {
                                echo '<option value="' . $comp['id'] . '">' . htmlspecialchars($comp['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label text-muted">
                            <i class="bi bi-person-workspace me-2"></i>Role
                        </label>
                        <select name="role" id="editRole" class="form-select rounded-pill" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="booker">Booker</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editPassword" class="form-label text-muted">
                            <i class="bi bi-lock me-2"></i>Password (optional)
                        </label>
                        <div class="input-group">
                            <input type="text" name="password" id="editPassword" class="form-control rounded-start-pill">
                            <button type="button" class="btn btn-outline-secondary rounded-end-pill" id="generateEditPassword">
                                <i class="bi bi-arrow-clockwise"></i> Generate
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">Leave blank to keep current password. Or generate a new one.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_user" class="btn btn-primary rounded-pill">
                        <i class="bi bi-save me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Password generator function
function generatePassword() {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()";
    let pass = "";
    for (let i = 0; i < 10; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return pass;
}

// Add User Modal functionality
document.getElementById("addUserModal").addEventListener("shown.bs.modal", () => {
    document.getElementById("password").value = generatePassword();
});
document.getElementById("generatePassword").addEventListener("click", () => {
    document.getElementById("password").value = generatePassword();
});

// Edit User Modal functionality
document.querySelectorAll(".edit-user-btn").forEach(button => {
    button.addEventListener("click", event => {
        const row = event.target.closest("tr");
        const userId = row.dataset.userId;
        const name = row.dataset.userName;
        const username = row.dataset.userUsername;
        const email = row.dataset.userEmail;
        const companyId = row.dataset.userCompanyId;
        const role = row.dataset.userRole;

        document.getElementById("editUserId").value = userId;
        document.getElementById("editName").value = name;
        document.getElementById("editUsername").value = username;
        document.getElementById("editEmail").value = email;
        document.getElementById("editCompanyId").value = companyId;
        document.getElementById("editRole").value = role;
        document.getElementById("editPassword").value = "";
    });
});
document.getElementById("generateEditPassword").addEventListener("click", () => {
    document.getElementById("editPassword").value = generatePassword();
});


// Filters + Search
document.getElementById("searchInput").addEventListener("keyup", filterTable);
document.getElementById("companyFilter").addEventListener("change", filterTable);
document.getElementById("roleFilter").addEventListener("change", filterTable);
document.getElementById("clearFilters").addEventListener("click", () => {
    document.getElementById("searchInput").value = "";
    document.getElementById("companyFilter").value = "";
    document.getElementById("roleFilter").value = "";
    filterTable();
});

function filterTable() {
    const search = document.getElementById("searchInput").value.toLowerCase();
    const company = document.getElementById("companyFilter").value.toLowerCase();
    const role = document.getElementById("roleFilter").value.toLowerCase();
    const rows = document.querySelectorAll("#usersTable tbody tr");

    rows.forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        const companyName = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const roleText = row.cells[3].textContent.toLowerCase();

        const matchesSearch = name.includes(search) || email.includes(search) || companyName.includes(search) || roleText.includes(search);
        const matchesCompany = company === "" || companyName === company;
        const matchesRole = role === "" || roleText === role;

        row.style.display = (matchesSearch && matchesCompany && matchesRole) ? "" : "none";
    });
}
</script>

<?php
require_once 'portalfooter.php';
?>