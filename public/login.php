<?php
session_start();
require_once __DIR__ . '/../app/config/db.php'; // db connection
// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: /portal/index.php");
    exit;
}
?>
<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-5 pt-0 pb-4">
                <div class="text-center mb-4">
                    <h5 class="modal-title fw-bold" id="loginModalLabel">Welcome back!</h5>
                    <p class="text-muted small mt-2">Sign in to manage your bookings.</p>
                </div>
                <!-- Error placeholder -->
                <div id="loginError" class="alert alert-danger d-none rounded-3 small"></div>

                <!-- Login Form -->
                <form id="loginForm" method="POST" action="/app/controllers/AuthController.php">
                    <div class="mb-3">
                        <label for="emailInput" class="form-label">Email address</label>
                        <input type="email" name="email" class="form-control rounded-pill" id="emailInput" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="passwordInput" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control rounded-pill" id="passwordInput" placeholder="Enter your password" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMeCheck">
                            <label class="form-check-label" for="rememberMeCheck">Remember me</label>
                        </div>
                        <a href="#" class="text-primary text-decoration-none small fw-bold">Forgot Password?</a>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill">Login</button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Include your JS file at the bottom -->
<script src="/bookingportal/assets/js/main.js"></script>