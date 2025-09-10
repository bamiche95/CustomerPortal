<?php require_once './login.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recycling Management Customer Portal - Login and manage your bookings.">
    <title>Customer Portal - Recycling Management</title>
    <link rel="stylesheet" href="../app/assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../assets/img/RML_icon_logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="/bookingportal/assets/css/style.css" rel="stylesheet">
</head>
<body>
<header class="bg-light py-3 shadow-sm">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a class="navbar-brand d-flex align-items-center text-light me-4" href="/bookingportal/public/index.php">
                <img src="../assets/img/logo_landscape.jpg" alt="Company Logo" style="height: 40px; margin-right: 10px;">
              
            </a>
        </div>
        
       

        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-dark btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>            
           
        </div>
    </div>
</header>

