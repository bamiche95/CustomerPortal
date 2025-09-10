<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

class CompanyController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Create a new company
    public function createCompany($name) {
        $name = trim($name);

        if (empty($name)) {
            return ["status" => "error", "message" => "Company name cannot be empty."];
        }

        // Check if company already exists
        $stmt = $this->conn->prepare("SELECT id FROM recbook_companies WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            return ["status" => "error", "message" => "Company already exists."];
        }
        $stmt->close();

        // Insert company
        $stmt = $this->conn->prepare("INSERT INTO recbook_companies (name, created_at) VALUES (?, NOW())");
        $stmt->bind_param("s", $name);

        if ($stmt->execute()) {
            $stmt->close();
            return ["status" => "success", "message" => "Company added successfully."];
        } else {
            $stmt->close();
            return ["status" => "error", "message" => "Error adding company: " . $this->conn->error];
        }
    }
}

// Initialize controller
$companyController = new CompanyController($conn);

// Handle POST request from Add Company modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company'])) {
    $result = $companyController->createCompany($_POST['name'] ?? '');
    $_SESSION[$result['status']] = $result['message'];
    header("Location: /bookingportal/portal/company.php");
    exit;
}
?>
