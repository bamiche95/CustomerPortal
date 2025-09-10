<?php
session_start();
require_once 'header.php';
require_once 'PortalSidebar.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: http://localhost:8080/bookingportal/public/index.php");
    exit;
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">New Booking 📝</h1>
    </div>

    <div class="card shadow-lg border-0 rounded-3 p-4">
        <form id="bookingForm" method="POST" action="/bookingportal/app/controllers/BookingController.php">
            <h2 class="card-title text-center fw-bold mb-4">Create a New Booking</h2>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="binNumber" class="form-label fw-bold">Bin Number</label>
                    <input type="number" name="bin_number" class="form-control rounded-pill" id="binNumber" placeholder="Enter bin number" required>
                </div>

                <div class="col-md-6">
                    <label for="containerType" class="form-label fw-bold">Container Type</label>
                    <input type="text" name="container_type" id="containerType" class="form-control rounded-pill" placeholder="Enter container type" required>
                </div>
            </div>

            <div class="mb-3 mt-3">
                <label for="location" class="form-label fw-bold">Location On Site</label>
                <input type="text" name="location" class="form-control rounded-pill" id="location" placeholder="Enter location" required>
            </div>

            <div class="mb-3">
                <label for="additionalService" class="form-label fw-bold">Additional Service</label>
                <textarea name="additional_service" class="form-control rounded-3" id="additionalService" rows="3" placeholder="Enter any additional service details"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Signature</label>
                <div class="border rounded-3 bg-white p-2">
                    <canvas id="signature-pad" style="width:100%; height:200px;"></canvas>
                </div>
                <input type="hidden" name="signature" id="signatureInput">
                <div class="text-end mt-2">
                    <button type="button" id="clearSignature" class="btn btn-outline-secondary rounded-pill btn-sm">Clear Signature</button>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill">
                    <i class="bi bi-calendar-plus me-2"></i>Submit Booking
                </button>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('signature-pad');
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
    }
    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();

    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)'
    });

    // Clear button
    document.getElementById('clearSignature').addEventListener('click', () => {
        signaturePad.clear();
        document.getElementById('signatureInput').value = '';
    });

    // On form submit, save signature as base64
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert("Please provide a signature.");
            return false;
        }
        document.getElementById('signatureInput').value = signaturePad.toDataURL();
    });
</script>

<?php
require_once 'portalfooter.php';
?>