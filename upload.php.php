<?php
session_start();

// Definisi peran pengguna (admin atau user)
$validRoles = ["admin", "user"];
$defaultRole = "user";  // Peran default

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ambil data dari formulir
    $email = $_POST["email"];
    $fileType = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
    $targetFile = "uploads/" . basename($_FILES["file"]["name"]);

    // Verifikasi reCAPTCHA
    $recaptchaSecretKey = "YOUR_SECRET_KEY";
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    $verifyUrl = "https://www.google.com/recaptcha/api/siteverify";
    $verifyData = [
        'secret' => $recaptchaSecretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $verifyResponse = file_get_contents($verifyUrl . '?' . http_build_query($verifyData));
    $responseData = json_decode($verifyResponse);

    if (!$responseData->success) {
        echo "reCAPTCHA verification failed!";
        die;
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format!";
    }
    // Validasi jenis file
    elseif (!in_array($fileType, ["jpeg", "jpg", "png"])) {
        echo "Only JPEG and PNG files are allowed.";
    }
    // Validasi peran pengguna
    elseif (isset($_SESSION["user_role"]) && in_array($_SESSION["user_role"], $validRoles)) {
        $userRole = $_SESSION["user_role"];

        // Cek akses berdasarkan peran
        if ($userRole === "admin" || $userRole === "user") {
            // Cek apakah file berhasil diunggah
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
                echo "File uploaded successfully.";
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "Access denied.";  // Peran tidak valid
        }
    } else {
        echo "Invalid user role.";  // Peran tidak ditemukan
    }
}
?>

