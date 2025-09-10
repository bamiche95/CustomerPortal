<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Booking Portal</title>
    <link href="/bookingportal/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/bookingportal/assets/css/custom.css" rel="stylesheet"> <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="/bookingportal/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid py-2">
        <a class="navbar-brand fw-bold text-white fs-4" href="#">
            <i class="bi bi-calendar-check-fill me-2"></i> Customer Booking Portal
        </a>
        
        <div class="d-flex align-items-center ms-auto">
            <div class="d-flex align-items-center me-3 text-white">
                <i class="bi bi-person-circle fs-5 me-2"></i>
                <span class="d-none d-md-inline-block"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
            </div>
            <a href="/bookingportal/public/logout.php" class="btn btn-primary d-flex align-items-center rounded-pill">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">