const BASE_URL = "http://localhost:8080/bookingportal";

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const errorBox = document.getElementById("loginError");
    const loginModalEl = document.getElementById("loginModal");
    const loginButton = loginForm ? loginForm.querySelector("button[type='submit']") : null;

    if (!loginForm || !loginButton) return;

    // Hide error when typing
    loginForm.querySelectorAll("input").forEach(input => {
        input.addEventListener("input", () => {
            if (errorBox && !errorBox.classList.contains("d-none")) {
                errorBox.classList.add("d-none");
                errorBox.textContent = "";
            }
        });
    });

    loginForm.addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Disable button and show spinner
        loginButton.disabled = true;
        const originalButtonText = loginButton.innerHTML;
        loginButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...`;

        fetch(`${BASE_URL}/app/controllers/AuthController.php`, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Restore button
            loginButton.disabled = false;
            loginButton.innerHTML = originalButtonText;

if (data.success) {
    const bootstrapModal = bootstrap.Modal.getInstance(loginModalEl) || new bootstrap.Modal(loginModalEl);
    bootstrapModal.hide();

    loginModalEl.addEventListener('hidden.bs.modal', () => {
        window.location.href = `${BASE_URL}${data.redirect}`;
    }, { once: true });
}
else if (errorBox) {
                errorBox.textContent = data.error;
                errorBox.classList.remove("d-none");
            }
        })
        .catch(error => {
            console.error("Error:", error);

            // Restore button
            loginButton.disabled = false;
            loginButton.innerHTML = originalButtonText;

            if (errorBox) {
                errorBox.textContent = "An unexpected error occurred. Please try again.";
                errorBox.classList.remove("d-none");
            }
        });
    });
});


// Fetch user data and company data
