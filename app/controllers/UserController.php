<?php

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // path to PHPMailer autoload

require_once __DIR__ . '/../config/db.php';

class UserController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $userId = $_SESSION['user_id'];

        $sql = "SELECT u.id, u.email, u.role, u.company_id, u.name, c.name AS company_name
                FROM recbook_users u
                LEFT JOIN recbook_companies c ON u.company_id = c.id
                WHERE u.id = ? LIMIT 1";

        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            return $row;
        }

        return null;
    }

    public function getAllUsers() {
        $sql = "SELECT u.id, u.username, u.name, u.email, u.role, c.name AS company_name
                FROM recbook_users u
                LEFT JOIN recbook_companies c ON u.company_id = c.id
                ORDER BY u.id DESC";
        $result = mysqli_query($this->conn, $sql);

        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }

        return $users;
    }

    public function createUser($username, $name, $email, $company_id, $password, $role = 'booker') {
        // Basic validation
        if (empty($username) || empty($name) || empty($email) || empty($company_id) || empty($password)) {
            return ["status" => "error", "message" => "All fields are required."];
        }

        // Ensure role is valid
        $allowedRoles = ['admin', 'booker', 'viewer'];
        if (!in_array($role, $allowedRoles, true)) {
            $role = 'booker'; // default
        }

        // Check if username or email already exists
        $check = $this->conn->prepare("SELECT id FROM recbook_users WHERE username = ? OR email = ? LIMIT 1");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $check->close();
            return ["status" => "error", "message" => "Username or email already exists."];
        }
        $check->close();

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $this->conn->prepare("INSERT INTO recbook_users (username, name, email, company_id, role, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $username, $name, $email, $company_id, $role, $hashedPassword);

        if ($stmt->execute()) {
            // Send welcome email
            $this->sendEmail($email, $name, $password, true);

            return ["status" => "success", "message" => "User added successfully."];
        } else {
            return ["status" => "error", "message" => "Error adding user: " . $this->conn->error];
        }
        $stmt->close();
    }

    public function editUser($userId, $name, $username, $email, $company_id, $role, $newPassword = null) {
        // Basic validation
        if (empty($name) || empty($username) || empty($email) || empty($company_id) || empty($role)) {
            return ["status" => "error", "message" => "All fields except password are required."];
        }

        // Check if username or email already exists for a different user
        $check = $this->conn->prepare("SELECT id FROM recbook_users WHERE (username = ? OR email = ?) AND id != ? LIMIT 1");
        $check->bind_param("ssi", $username, $email, $userId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $check->close();
            return ["status" => "error", "message" => "Username or email is already in use by another account."];
        }
        $check->close();

        $sql = "UPDATE recbook_users SET name = ?, username = ?, email = ?, company_id = ?, role = ?";
        $params = "sssis";
        $values = [$name, $username, $email, $company_id, $role];

        // Check for new password
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql .= ", password_hash = ?";
            $params .= "s";
            $values[] = $hashedPassword;
        }

        $sql .= " WHERE id = ?";
        $params .= "i";
        $values[] = $userId;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($params, ...$values);
        
        if ($stmt->execute()) {
            // Send update email with new password if it was changed
            $this->sendEmail($email, $name, $newPassword, !empty($newPassword));
            return ["status" => "success", "message" => "User account updated successfully."];
        } else {
            return ["status" => "error", "message" => "Error updating user: " . $this->conn->error];
        }
        $stmt->close();
    }

   private function sendEmail($email, $name, $password = null, $isNewUser = false) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.newportal.recyclingmanagement.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'customerportal@newportal.recyclingmanagement.com';
        $mail->Password   = 'RMLScrapman169';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('customerportal@newportal.recyclingmanagement.com', 'CustomerPortal');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);

        $subject = $isNewUser ? 'Welcome to Recycling Management Customer Portal' : 'Your Account Details Have Been Updated';
        $mail->Subject = $subject;

        $messageHeader = $isNewUser ? 'Welcome to Our Portal!' : 'Account Update Notification';
        $introText = $isNewUser ? 'Your account has been successfully created. We are excited to have you on board! Below are your login details.' : 'Your account details have been updated by an administrator. Here is a summary of the changes.';

        $passwordSection = '';
        if ($isNewUser) {
            $passwordSection = "
                <p style=\"margin: 0; font-size: 16px; line-height: 1.6; color: #555555;\">
                    <strong style=\"color: #2c3e50;\">Username:</strong> <span style=\"font-weight: bold; color: #34495e;\">" . htmlspecialchars($email) . "</span>
                </p>
                <p style=\"margin: 10px 0; font-size: 16px; line-height: 1.6; color: #555555;\">
                    <strong style=\"color: #2c3e50;\">Password:</strong> <span style=\"font-weight: bold; color: #34495e;\">" . htmlspecialchars($password) . "</span>
                </p>
                <p style=\"margin: 20px 0 0 0; font-size: 14px; color: #888888;\">For security, please change your password after your first login.</p>
            ";
        } elseif ($password) {
            $passwordSection = "
                <p style=\"margin: 0; font-size: 16px; line-height: 1.6; color: #555555;\">
                    <strong style=\"color: #2c3e50;\">Your password has been reset.</strong> Please use this temporary password to log in.
                </p>
                <p style=\"margin: 10px 0; font-size: 16px; line-height: 1.6; color: #555555;\">
                    <strong style=\"color: #2c3e50;\">Temporary Password:</strong> <span style=\"font-weight: bold; color: #34495e;\">" . htmlspecialchars($password) . "</span>
                </p>
                <p style=\"margin: 20px 0 0 0; font-size: 14px; color: #888888;\">For your security, please change your password immediately.</p>
            ";
        }

        $mail->Body = "
        <!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
        <html xmlns=\"http://www.w3.org/1999/xhtml\">
        <head>
            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
            <title>Recycling Management Customer Portal</title>
            <style>
                body, p, h1, h2, h3, h4, h5, h6, a {
                    font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
                }
                @media only screen and (max-width: 600px) {
                    .container { width: 100% !important; }
                    .content { padding: 20px !important; }
                    .cta-button { width: 100% !important; }
                }
            </style>
        </head>
        <body style=\"margin: 0; padding: 0; background-color: #f4f7f6;\">
            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout: fixed;\">
                <tr>
                    <td align=\"center\" style=\"padding: 40px 0;\">
                        <table class=\"container\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\" style=\"border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);\">
                            <tr>
                                <td align=\"center\" style=\"padding: 40px 0 20px 0; background-color: #096825ff; border-top-left-radius: 8px; border-top-right-radius: 8px;\">
                                    <h1 style=\"color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;\">
                                        {$messageHeader}
                                    </h1>
                                </td>
                            </tr>
                            <tr>
                                <td class=\"content\" style=\"padding: 40px;\">
                                    <p style=\"margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #333333;\">
                                        Hello <strong style=\"color: #096825ff;\">" . htmlspecialchars($name) . "</strong>,
                                    </p>
                                    <p style=\"margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #333333;\">
                                        {$introText}
                                    </p>
                                    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"margin: 25px 0; background-color: #ecf0f1; border-radius: 8px; padding: 20px;\">
                                        <tr>
                                            <td style=\"color: #2c3e50; font-size: 16px; line-height: 1.6;\">
                                                {$passwordSection}
                                            </td>
                                        </tr>
                                    </table>
                                    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin: 0 auto;\">
                                        <tr>
                                            <td align=\"center\" style=\"border-radius: 50px; background-color: #086331ff;\">
                                                <a href=\"http://localhost:8080/bookingportal/public/index.php\" target=\"_blank\" style=\"font-size: 16px; font-weight: bold; color: #ffffff; text-decoration: none; border-radius: 50px; padding: 15px 25px; border: 1px solid #086331ff; display: inline-block;\">
                                                    Log in to Your Account
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align=\"center\" style=\"padding: 30px 40px; background-color: #ecf0f1; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;\">
                                    <p style=\"margin: 0; font-size: 12px; color: #888888;\">
                                        &copy; " . date("Y") . " Recycling Management. All Rights Reserved.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email failed: {$mail->ErrorInfo}");
    }
}
}


// Initialize controller
$userController = new UserController($conn);
$currentUser = $userController->getCurrentUser();

// Handle POST request from forms
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Add User request
    if (isset($_POST['add_user'])) {
        $result = $userController->createUser(
            trim($_POST['username']),
            trim($_POST['name']),
            trim($_POST['email']),
            intval($_POST['company_id']),
            $_POST['password'],
            $_POST['role'] ?? 'booker'
        );

        $_SESSION[$result['status']] = $result['message'];
        header("Location: /bookingportal/portal/User.php");
        exit;
    }

    // Handle Edit User request
    if (isset($_POST['edit_user'])) {
        $result = $userController->editUser(
            intval($_POST['user_id']),
            trim($_POST['name']),
            trim($_POST['username']),
            trim($_POST['email']),
            intval($_POST['company_id']),
            trim($_POST['role']),
            trim($_POST['password']) // New password, optional
        );

        $_SESSION[$result['status']] = $result['message'];
        header("Location: /bookingportal/portal/User.php");
        exit;
    }
}

// return the controller instance for use in other pages
return $userController;