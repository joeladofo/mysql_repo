<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    if (empty($email) || empty($otp)) {
        die("Email and OTP are required.");
    }

    // Sanitize input
    $email = mysqli_real_escape_string($conn, $email);
    $otp = mysqli_real_escape_string($conn, $otp);

    // Check if OTP is correct and not expired
    $sql = "SELECT otp, otp_expiry FROM users WHERE email = ? AND is_verified = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($row['otp'] == $otp && strtotime($row['otp_expiry']) > time()) {
            // OTP is correct and not expired, update user as verified
            $update_sql = "UPDATE users SET is_verified = 1, otp = NULL, otp_expiry = NULL WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("s", $email);
            $update_stmt->execute();

            echo "OTP verification successful! Your account is now verified.";
            header("refresh:2; url=login.html"); // Redirect to login page
        } else {
            echo "Invalid or expired OTP.";
        }
    } else {
        echo "No pending verification found.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>

