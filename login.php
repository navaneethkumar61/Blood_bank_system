<?php
session_start();

echo "<h3>DEBUG:</h3><pre>";
print_r($_POST);
echo "</pre>";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Form not submitted via POST.";
    exit;
}

if (!isset($_POST['role'], $_POST['username'], $_POST['password'])) {
    echo "Please fill out the login form.";
    exit;
}

$role = trim($_POST['role']);
$username = trim($_POST['username']);
$password = $_POST['password'];

$host = "localhost";
$user = "root";
$pass = "";
$db = "sepm";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table = "";
$redirectPage = "";

switch ($role) {
    case "admin":
        $table = "admins";
        $redirectPage = "admin_dashboard.php";
        break;
    case "donor":
        $table = "donors";
        $redirectPage = "donor_dashboard.php";
        break;
    case "patient":
        $table = "patients";
        $redirectPage = "patient_dashboard.php";
        break;
    default:
        echo "Invalid role selected.";
        exit;
}

$stmt = $conn->prepare("SELECT * FROM $table WHERE username = ?");
if (!$stmt) {
    echo "Prepare failed: " . $conn->error;
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        header("Location: $redirectPage");
        exit;
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "User not found.";
}

$stmt->close();
$conn->close();
?>
