<?php require_once './header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Portal - Your Home for Bookings</title>
    <link rel="stylesheet" href="../app/assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Custom styles for a more modern, stunning look */
        :root {
            --bs-primary: #006400; /* Dark Green */
            --bs-primary-rgb: 0, 100, 0;
            --bs-dark-green: #004d00;
            --bs-accent: #2c3e50; /* A dark, professional accent color */
            --bs-accent-rgb: 44, 62, 80;
        }

        .bg-primary { background-color: var(--bs-primary) !important; }
        .text-primary { color: var(--bs-primary) !important; }

        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-dark-green);
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: var(--bs-dark-green);
            border-color: var(--bs-dark-green);
        }

        .btn-outline-primary {
            color: var(--bs-primary);
            border-color: var(--bs-primary);
            transition: all 0.3s ease;
        }
        .btn-outline-primary:hover {
            color: #fff;
            background-color: var(--bs-primary);
        }

        .hero-section {
    background: linear-gradient(rgba(0, 100, 0, 0.7), rgba(0, 100, 0, 0.7)), url('../assets/img/image00014.jpeg') no-repeat center center/cover;
    color: white;
    padding: 8rem 0;
    min-height: 85vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    position: relative;
}
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0));
            z-index: 1;
        }
        .hero-content {
            z-index: 2;
        }
        
        .card-shadow {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .card-shadow:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }

        .icon-large {
            font-size: 3.5rem;
            color: var(--bs-accent);
        }
        .divider {
            height: 4px;
            width: 80px;
            background-color: var(--bs-primary);
            margin: 1.5rem auto;
            border-radius: 2px;
        }
    </style>
</head>
<body>

<section class="hero-section">
    <div class="container hero-content">
        <h1 class="display-1 fw-bold">Welcome to <br>our customer portal</h1>
        <p class="lead mt-3 mx-auto" style="max-width: 600px;">
            Your seamless solution for booking and managing services. Experience a professional and efficient way to handle all your important documents in one place.
        </p>
        <a href="#features" class="btn btn-outline-light btn-lg mt-5 rounded-pill px-5">Explore Features</a>
    </div>
</section>

<main class="container py-5 my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Designed for Efficiency</h2>
        <div class="divider"></div>
        <p class="text-muted lead">
            A portal built to streamline your workflow and keep your information organized.
        </p>
    </div>

    <div class="row text-center g-5" id="features">
        <div class="col-md-6">
            <div class="card p-5 h-100 card-shadow border-0 rounded-5">
                <div class="card-body d-flex flex-column align-items-center">
                    <div class="icon-large mb-4">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="card-title fw-bold">Ready to Book?</h3>
                    <p class="card-text text-muted mb-4">
                        Secure your spot with just a few clicks. Whether it's for a consultation, an event, or a service, our streamlined process makes it simple and fast.
                    </p>
                   
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-5 h-100 card-shadow border-0 rounded-5">
                <div class="card-body d-flex flex-column align-items-center">
                    <div class="icon-large mb-4">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3 class="card-title fw-bold">Access Your Documents</h3>
                    <p class="card-text text-muted mb-4">
                        Easily view, download, and manage all your important documents, from invoices to contracts, in your personalized dashboard.
                    </p>
                   
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>

<?php
require_once './footer.php';
?>