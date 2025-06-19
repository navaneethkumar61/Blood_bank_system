<?php
session_start();

$host = 'localhost';
$db   = 'sepm';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(eligible) as passed FROM questionnaire_answers WHERE username = ?");
$stmt->execute([$username]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$eligible = ($result['total'] == $result['passed']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Result</title>
    <style>
    body {
        background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
        color: #ffffff;
        font-family: 'Poppins', sans-serif;
        text-align: center;
        padding-top: 100px;
        margin: 0;
    }

    .box {
        background: rgba(255, 255, 255, 0.08);
        padding: 40px;
        margin: auto;
        width: 60%;
        border-radius: 16px;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    h1 {
        font-size: 28px;
        color: #b2fef7; /* Light cyan for headers */
    }

    p {
        font-size: 18px;
        color: #d9f9f6; /* Soft white-blue for description */
    }

    /* Donation Link Styling (Teal Theme) */
    a.donation-link {
        display: inline-block;
        margin-top: 20px;
        font-family: 'Poppins', sans-serif;
        font-size: 18px;
        color: #00bfa6;
        text-decoration: none;
        padding: 12px 26px;
        border-radius: 8px;
        background-color: #ffffff;
        border: 2px solid #00bfa6;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 12px rgba(0, 191, 166, 0.3);
    }

    a.donation-link:hover {
        background-color: #00bfa6;
        color: #ffffff;
        border-color: #00bfa6;
        box-shadow: 0 6px 18px rgba(0, 191, 166, 0.5);
    }

    .back-button {
    position: absolute;
    top: 20px;
    left: 20px;
    font-size: 16px;
    color: #fff;
    text-decoration: none;
    background: rgba(255, 255, 255, 0.1);
    padding: 10px 15px;
    border-radius: 12px;
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
  }

  .back-button i {
    margin-right: 8px;
  }

  .back-button:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(-2px);
  }
</style>


</head>
<body>
    <div class="box">
        <h1><?= $eligible ? "You are eligible to donate blood!" : "Sorry, you are not eligible at this time." ?></h1>
        <p>Thank you for your time and honesty!</p>
    </div>
    <br>
    <?php if ($eligible): ?>
            <a href="donation_form.php" class="donation-link">Proceed to Donation Form</a>
    <?php endif; ?>
    <a href="donor_dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</body>
</html>
