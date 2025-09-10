<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You do not have permission to perform this action.";
    header("Location: /bookingportal/public/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_file'])) {

    $filename = trim($_POST['filename'] ?? '');
    $company_id = intval($_POST['company_id'] ?? 0);
    $visible_to_role = $_POST['visible_to_role'] ?? 'viewer';
    $category_id = intval($_POST['category_id'] ?? 0);

    if (empty($filename) || $company_id <= 0 || $category_id <= 0 || !isset($_FILES['file'])) {
        $_SESSION['error'] = "Please fill in all required fields and select a file.";
        header("Location: /bookingportal/portal/admin-files.php");
        exit;
    }

    $uploaded_file = $_FILES['file'];
    $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'png', 'jpg', 'jpeg'];
    $file_ext = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_types)) {
        $_SESSION['error'] = "Invalid file type. Allowed: " . implode(', ', $allowed_types);
        header("Location: /bookingportal/portal/admin-files.php");
        exit;
    }

    $upload_dir = __DIR__ . '/../../uploads/files/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $unique_name = uniqid('file_') . '.' . $file_ext;
    $file_path = $upload_dir . $unique_name;

    if (!move_uploaded_file($uploaded_file['tmp_name'], $file_path)) {
        $_SESSION['error'] = "Failed to upload file.";
        header("Location: /bookingportal/portal/admin-files.php");
        exit;
    }

    $relative_path = 'uploads/files/' . $unique_name;
    $uploaded_by = $_SESSION['user_id'];

    $sql = "INSERT INTO recbook_files 
            (company_id, filename, filepath, visible_to_role, uploaded_by, category_id, uploaded_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssii", $company_id, $filename, $relative_path, $visible_to_role, $uploaded_by, $category_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "File uploaded successfully!";
    } else {
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        if (file_exists($file_path)) unlink($file_path);
    }

    header("Location: /bookingportal/portal/admin-files.php");
    exit;
} else {
    header("Location: /bookingportal/portal/admin-files.php");
    exit;
}
