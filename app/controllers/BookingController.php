<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // PHPMailer autoload

session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /bookingportal/public/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bin_number = isset($_POST['bin_number']) ? intval($_POST['bin_number']) : null;
    $container_type = isset($_POST['container_type']) ? trim($_POST['container_type']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $additional_service = isset($_POST['additional_service']) ? trim($_POST['additional_service']) : '';
    $signature_data = isset($_POST['signature']) ? $_POST['signature'] : '';

    $company_id = $_SESSION['company_id'];
    $user_id = $_SESSION['user_id'];

    // Generate booking number starting from 1000
    $result = mysqli_query($conn, "SELECT MAX(booking_number) AS last_booking FROM recbook_bookings");
    $row = mysqli_fetch_assoc($result);
    $lastBooking = $row['last_booking'];
    $booking_number = $lastBooking ? intval($lastBooking) + 1 : 1000;

    // Fetch user and company information
    $userSql = "SELECT u.name AS user_name, c.name AS company_name
                FROM recbook_users u
                JOIN recbook_companies c ON u.company_id = c.id
                WHERE u.id = ? AND u.company_id = ?";
    $userStmt = mysqli_prepare($conn, $userSql);
    mysqli_stmt_bind_param($userStmt, "ii", $user_id, $company_id);
    mysqli_stmt_execute($userStmt);
    $userResult = mysqli_stmt_get_result($userStmt);
    $userData = mysqli_fetch_assoc($userResult);

    $user_name = $userData['user_name'] ?? 'N/A';
    $company_name = $userData['company_name'] ?? 'N/A';

    // Handle signature
    $signature_path = null;
    $signature_base64 = null;

    if (!empty($signature_data)) {
        $signature_base64 = str_replace('data:image/png;base64,', '', $signature_data);
        $signature_base64 = str_replace(' ', '+', $signature_base64);
        $signature_binary = base64_decode($signature_base64);

        $upload_dir = __DIR__ . '/../../uploads/signatures/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $signature_file = $upload_dir . $booking_number . '.png';
        file_put_contents($signature_file, $signature_binary);

        $signature_path = 'uploads/signatures/' . $booking_number . '.png';
    }

    // Insert booking
    $sql = "INSERT INTO recbook_bookings 
            (booking_number, company_id, user_id, bin_number, container_type, location, `Additional Service`, signature, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "siiissss", 
        $booking_number, $company_id, $user_id, $bin_number, $container_type, $location, $additional_service, $signature_path);

    if (mysqli_stmt_execute($stmt)) {
        
        // Send email to admin
        $adminEmail = 'customerportal@recman.com'; // change to your admin email

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'mail.newportal.recyclingmanagement.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'customerportal@newportal.recyclingmanagement.com';
            $mail->Password   = 'RMLScrapman169';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('customerportal@newportal.recyclingmanagement.com', 'Customer Portal');
            $mail->addAddress($adminEmail);
            $mail->isHTML(true);

            $mail->Subject = "New Booking Created: #{$booking_number}";

            // Add the signature as an inline image if it exists
            $signature_cid = '';
            if (!empty($signature_base64)) {
                $mail->addStringEmbeddedImage(base64_decode($signature_base64), 'signature_image', 'signature.png', 'base64', 'image/png');
                $signature_cid = 'cid:signature_image';
            }

            // HTML Body with embedded signature
            $mail->Body = "
            <!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
            <html xmlns=\"http://www.w3.org/1999/xhtml\">
            <head>
                <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                <title>New Booking Confirmation</title>
                <style>
                    body, p, h1, h2, h3, h4, h5, h6 { font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; width: 100%; }
                    .container { width: 400px; max-width: 400px; margin: 0 auto; }
                    .card { background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                    .header { background-color: #3498db; color: #ffffff; text-align: center; padding: 40px 0 20px 0; border-top-left-radius: 8px; border-top-right-radius: 8px; }
                    .content { padding: 40px; }
                    .footer { text-align: center; padding: 30px 40px; background-color: #ecf0f1; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; color: #888888; }
                    .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
                    .data-table th { background-color: #f8f9fa; font-weight: bold; }
                    .signature-box { border: 1px solid #dee2e6; padding: 15px; text-align: center; margin-top: 30px; }
                    @media only screen and (max-width: 600px) {
                        .content { padding: 20px !important; }
                    }
                </style>
            </head>
            <body style=\"margin: 0; padding: 0; background-color: #f4f7f6;\">
                <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
                    <tr>
                        <td align=\"center\" style=\"padding: 40px 0;\">
                            <table class=\"container card\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"500\">
                                <tr>
                                    <td class=\"header\">
                                        <h1 style=\"margin: 0; font-size: 28px; font-weight: bold;\">New Booking Notification</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td class=\"content\">
                                        <p style=\"margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #333333;\">
                                            A new booking has been created and requires your attention.
                                        </p>
                                        <table class=\"data-table\">
                                            <tr>
                                                <th>Booking Number:</th>
                                                <td><strong>{$booking_number}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Booked By:</th>
                                                <td>{$user_name}</td>
                                            </tr>
                                            <tr>
                                                <th>Company:</th>
                                                <td>{$company_name}</td>
                                            </tr>
                                            <tr>
                                                <th>Bin Number:</th>
                                                <td>{$bin_number}</td>
                                            </tr>
                                            <tr>
                                                <th>Container Type:</th>
                                                <td>{$container_type}</td>
                                            </tr>
                                            <tr>
                                                <th>Location:</th>
                                                <td>{$location}</td>
                                            </tr>
                                            <tr>
                                                <th>Additional Service:</th>
                                                <td>{$additional_service}</td>
                                            </tr>
                                        </table>
                                        
                                        <div class=\"signature-box\">
                                            <p style=\"margin: 0 0 10px 0; font-weight: bold;\">Customer Signature:</p>
                                            " . (!empty($signature_data) ? "<img src=\"{$signature_cid}\" alt=\"Customer Signature\" style=\"max-width: 100%; height: auto; display: block; margin: 0 auto;\">" : "<p>No signature provided.</p>") . "
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class=\"footer\">
                                        <p style=\"margin: 0; font-size: 12px;\">
                                            &copy; " . date("Y") . " Booking Portal. All Rights Reserved.
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
            error_log("Booking email send failed: {$mail->ErrorInfo}");
        }

        $_SESSION['success'] = "Booking created successfully!";
        header("Location: /bookingportal/portal/booking.php");
        exit;

    } else {
        $_SESSION['error'] = "Error creating booking: " . mysqli_error($conn);
        header("Location: /bookingportal/portal/booking-new.php");
        exit;
    }
} else {
    header("Location: /bookingportal/portal/booking-new.php");
    exit;
}

?>