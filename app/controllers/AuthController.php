<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Ensure POST data exists
if (!isset($_POST['email'], $_POST['password'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// Fetch user by email including name
$sql = "SELECT id, name, email, password_hash, role, company_id FROM recbook_users WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $loginSuccess = false;

    // 1️⃣ Check password_hash (modern PHP)
    if (password_verify($password, $row['password_hash'])) {
        $loginSuccess = true;
    }
    // 2️⃣ Check legacy SHA1 password
    elseif (strlen($row['password_hash']) === 40 && sha1($password) === $row['password_hash']) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateSql = "UPDATE recbook_users SET password_hash = ? WHERE id = ?";
        $updateStmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "si", $newHash, $row['id']);
        mysqli_stmt_execute($updateStmt);
        $loginSuccess = true;
    }
    // 3️⃣ Check plain-text password (legacy)
    elseif ($password === $row['password_hash']) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateSql = "UPDATE recbook_users SET password_hash = ? WHERE id = ?";
        $updateStmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "si", $newHash, $row['id']);
        mysqli_stmt_execute($updateStmt);
        $loginSuccess = true;
    }

    if ($loginSuccess) {
        // Save session variables
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['company_id'] = $row['company_id'];
        $_SESSION['name'] = $row['name'];

        // Decide redirect URL based on role
        $redirectUrl = '/portal/index.php';
        if ($row['role'] === 'admin') {
            $redirectUrl = '/portal/admin-dashboard.php';
        } elseif ($row['role'] === 'booker') {
            $redirectUrl = '/portal/booking.php';
        } elseif ($row['role'] === 'viewer') {
            $redirectUrl = '/portal/files.php';
        }

        echo json_encode([
            'success' => true,
            'role' => $row['role'],
            'redirect' => $redirectUrl
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid password.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'User not found.']);
    exit;
}
