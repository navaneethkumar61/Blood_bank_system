<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Include profile completeness check
require_once 'checkpatientprofile.php';

$conn = new mysqli('localhost', 'root', '', 'sepm');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if patient profile is complete
if (!isPatientProfileComplete($conn, $username)) {
    echo "<script>alert('Please complete your profile before submitting a blood request.'); window.location.href='patient_profile.php';</script>";
    exit();
}

// Fetch patient data
$stmt = $conn->prepare("SELECT * FROM patients WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$patient_data = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Request Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            color: #fff;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 60px auto;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        h1 {
            text-align: center;
            color: #b2fef7;
            margin-bottom: 30px;
            font-size: 32px;
        }

        h2 {
            color: #b2fef7;
            margin-bottom: 20px;
            font-size: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-group-half {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            background-color: rgba(255, 255, 255, 0.9);
            color: #000;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-family: 'Poppins', sans-serif;
        }

        select {
            appearance: none;
            background-color: rgba(255, 255, 255, 0.9);
            color: #000;
        }

        select option {
            background-color: white;
            color: #000;
            padding: 10px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        input[type="submit"] {
            background-color: #00bfa6;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 12px;
            font-size: 16px;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #00a090;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .back-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .required::after {
            content: " *";
            color: #ff6b6b;
        }
    </style>
</head>
<body>
    <a href="patient_dashboard.php" class="back-button">← Back to Dashboard</a>
    <div class="container">
        <h1>Blood Request Form</h1>
        <form action="patient_request1.php" method="POST">
            <!-- Patient Information Section -->
            <div class="form-section">
                <h2>Patient Information</h2>
                <div class="form-row">
                    <div class="form-group-half">
                        <label class="required">Full Name</label>
                        <input type="text" name="full_name" required placeholder="Enter patient's full name"
                               value="<?php echo isset($patient_data['full_name']) ? htmlspecialchars($patient_data['full_name']) : ''; ?>">
                    </div>
                    <div class="form-group-half">
                        <label class="required">Date of Birth</label>
                        <input type="date" name="dob" required
                               value="<?php echo isset($patient_data['dob']) ? htmlspecialchars($patient_data['dob']) : ''; ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-half">
                        <label class="required">Gender</label>
                        <select name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo (isset($patient_data['gender']) && $patient_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($patient_data['gender']) && $patient_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (isset($patient_data['gender']) && $patient_data['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group-half">
                        <label>Medical Record Number</label>
                        <input type="text" name="medical_record_number" placeholder="If applicable">
                    </div>
                </div>
            </div>

            <!-- Clinical Information Section -->
            <div class="form-section">
                <h2>Clinical Information</h2>
                <div class="form-group">
                    <label class="required">Diagnosis</label>
                    <textarea name="diagnosis" required placeholder="Reason for blood request"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Blood Type</label>
                        <select name="blood_type" required>
                            <option value="">Select Blood Type</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="Unknown">Unknown</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="required">Quantity Required (units)</label>
                    <input type="number" name="quantity" step="0.1" placeholder="Enter quantity:">
                </div>
            </div>

            <!-- Additional Information Section -->
            <div class="form-section">
                <h2>Additional Information</h2>
                <div class="form-group">
                    <label class="required">Urgency Level</label>
                    <select name="urgency" required>
                        <option value="">Select Urgency</option>
                        <option value="Routine">Routine</option>
                        <option value="Urgent">Urgent</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Patient's Current Condition</label>
                    <textarea name="current_condition" placeholder="Relevant details that may affect the transfusion"></textarea>
                </div>
                <div class="form-group">
                    <label>Known Allergies</label>
                    <textarea name="allergies" placeholder="Any known allergies to blood products or medications"></textarea>
                </div>
            </div>

            <div class="form-section">
                <input type="submit" value="Submit Blood Request">
            </div>
        </form>
    </div>
</body>
</html> 