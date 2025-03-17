<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php'); // Ensure this file establishes a valid database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require 'C:/xampp/htdocs/Todo/PHPMailer-6.9.3/src/Exception.php';
require 'C:/xampp/htdocs/Todo/PHPMailer-6.9.3/src/PHPMailer.php';
require 'C:/xampp/htdocs/Todo/PHPMailer-6.9.3/src/SMTP.php';

// Function to generate a 6-digit OTP
function generateOTP() {
    return sprintf("%06d", mt_rand(0, 999999));
}

// Function to send verification email
function sendVerificationEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Configure SMTP for Mailtrap
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io'; // Use Mailtrap's SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'f4b621e0683508'; // Replace with your Mailtrap username
        $mail->Password = '2a35bda9454096'; // Replace with your Mailtrap password
        $mail->Port = 2525; // Use the SMTP port provided by Mailtrap
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Encryption (STARTTLS)

        // Sender and recipient settings
        $mail->setFrom('noreply@todoapp.com', 'Todo App'); // Sender email (any for Mailtrap)
        $mail->addAddress($email); // Recipient email

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'OTP Verification';
        $mail->Body    = "<h2>Your OTP is: <b>$otp</b></h2>
                          <p>This OTP will expire in 10 minutes.</p>";

        // Send email
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return "Mailer Error: " . $mail->ErrorInfo;
    }
}

// Handle user signup
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        die("Email and Password are required.");
    }

    // Generate OTP
    $otp = generateOTP();
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, otp, otp_expiry, is_verified) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $is_verified = 0; // User is not verified initially
        $stmt->bind_param("ssssi", $email, $hashed_password, $otp, $otp_expiry, $is_verified);

        if ($stmt->execute()) {
            // Send OTP Email
            if (sendVerificationEmail($email, $otp)) {
                echo "Sign-up successful. Please check your email for OTP.";
                header("refresh:2; url=verify_otp.html");
                exit();
            } else {
                echo "Error sending OTP email.";
            }
        } else {
            echo "Could not sign up: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error in preparing statement: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
