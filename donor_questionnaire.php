<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Check donor profile completeness (reuse logic from checkdonorprofile.php)
require_once 'checkdonorprofile.php';

// Use mysqli for profile check
$conn_mysqli = new mysqli('localhost', 'root', '', 'sepm');
if ($conn_mysqli->connect_error) {
    die("Connection failed: " . $conn_mysqli->connect_error);
}
if (!isDonorProfileComplete($conn_mysqli, $username)) {
    echo "<script>alert('Please complete your profile before proceeding.'); window.location.href='donor_profile.php';</script>";
    exit();
}

// PDO for main questionnaire DB operations
$dsn = "mysql:host=localhost;dbname=sepm;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $conn = new PDO($dsn, 'root', '', $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get donor gender and DOB
$donor_stmt = $conn->prepare("SELECT id, gender, dob FROM donor_profile WHERE username = ?");
$donor_stmt->execute([$username]);
$donor_data = $donor_stmt->fetch();
$gender = strtolower($donor_data['gender']);
$dob = $donor_data['dob'];
$donor_id = $donor_data['id'];

// Base questions (self-reportable only)
$questions = [
    "What is your age? (Must be 18–65 years)",
    "What is your weight? (Must be at least 45 kg)",
    "Are you feeling healthy and well today?",
    "Do you have any current illness such as fever, cold, cough, or infection?",
    "Have you had any recent surgery, dental procedure, or hospitalization?",
    "Do you have or have you ever had Hepatitis B or C?",
    "Do you have or have you ever had HIV/AIDS?",
    "Do you have or have you ever had Malaria (within the last 3 months)?",
    "Do you have or have you ever had heart disease or uncontrolled blood pressure?",
    "Do you have or have you ever had cancer or bleeding disorders?",
    "Are you currently on any medication or undergoing medical treatment?",
    "Have you ever received a blood transfusion?",
    "Have you received tattoos, piercings, or acupuncture in the last 6–12 months?",
    "Have you been in contact with someone who has hepatitis, HIV, or COVID-19?",
    "Have you traveled internationally in the past 6 months?",
    "Have you visited areas affected by malaria, Zika, or dengue outbreaks recently?",
];

// Gender-specific additions
if ($gender === 'female') {
    $questions[] = "Are you currently menstruating?";
    $questions[] = "Are you pregnant or have been pregnant in the last 6 weeks?";
    $questions[] = "Have you donated blood in the last 4 months?";
} else {
    $questions[] = "Have you donated blood in the last 3 months?";
}

$questions[] = "Did you experience any complications during or after previous blood donations?";

// Track current question index
$current_q = isset($_GET['q']) ? (int)$_GET['q'] : 0;

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer = trim($_POST['answer']);
    $question_text = $questions[$current_q];
    $eligible = 1;

    switch ($question_text) {
        case "What is your age? (Must be 18–65 years)":
            $age = date_diff(date_create($dob), date_create('today'))->y;
            $eligible = ($age >= 18 && $age <= 65) ? 1 : 0;
            break;

        case "What is your weight? (Must be at least 45 kg)":
            $eligible = (is_numeric($answer) && $answer >= 45) ? 1 : 0;
            break;

        case "Do you have any current illness such as fever, cold, cough, or infection?":
        case "Have you had any recent surgery, dental procedure, or hospitalization?":
        case "Do you have or have you ever had Hepatitis B or C?":
        case "Do you have or have you ever had HIV/AIDS?":
        case "Do you have or have you ever had Malaria (within the last 3 months)?":
        case "Do you have or have you ever had heart disease or uncontrolled blood pressure?":
        case "Do you have or have you ever had cancer or bleeding disorders?":
        case "Are you currently on any medication or undergoing medical treatment?":
        case "Have you ever received a blood transfusion?":
        case "Have you received tattoos, piercings, or acupuncture in the last 6–12 months?":
        case "Have you been in contact with someone who has hepatitis, HIV, or COVID-19?":
        case "Have you traveled internationally in the past 6 months?":
        case "Have you visited areas affected by malaria, Zika, or dengue outbreaks recently?":
        case "Are you currently menstruating?":
        case "Are you pregnant or have been pregnant in the last 6 weeks?":
        case "Have you donated blood in the last 3 months?":
        case "Have you donated blood in the last 4 months?":
        case "Did you experience any complications during or after previous blood donations?":
            $eligible = (strtolower($answer) === 'no') ? 1 : 0;
            break;
    }

    // Save response
    $insert = $conn->prepare("INSERT INTO questionnaire_answers (username, question, answer, eligible) VALUES (?, ?, ?, ?)");
    $insert->execute([$username, $question_text, $answer, $eligible]);

    // Next question
    $current_q++;
    if ($current_q >= count($questions)) {
        header("Location: questionnaire_result.php");
        exit();
    } else {
        header("Location: donor_questionnaire.php?q=" . $current_q);
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Donor Questionnaire</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #bdc3c7, #2c3e50);
            font-family: 'Poppins', sans-serif;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .box {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            text-align: center;
        }
        input[type="text"], input[type="number"], select {
            padding: 10px;
            width: 100%;
            border-radius: 8px;
            border: none;
            margin-top: 15px;
            background: rgba(255,255,255,0.2);
            color: white;
        }
        input[type="submit"] {
            margin-top: 20px;
            padding: 12px 30px;
            background-color: #00c6ff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #007acc;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Question <?= $current_q + 1 ?> of <?= count($questions) ?></h2>
        <p><?= $questions[$current_q] ?></p>
        <form method="POST">
            <input type="text" name="answer" required placeholder="Your answer">
            <input type="submit" value="Next">
        </form>
    </div>
</body>
</html>
