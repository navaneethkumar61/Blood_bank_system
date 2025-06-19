<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sepm";

// Database connection
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($dbname);
} else {
    die("Error creating database: " . $conn->error);
}

// Create tables if not exists
$conn->query("CREATE TABLE IF NOT EXISTS donors (
    username VARCHAR(50) PRIMARY KEY
)");

$conn->query("CREATE TABLE IF NOT EXISTS patients (
    username VARCHAR(50) PRIMARY KEY
)");

$conn->query("CREATE TABLE IF NOT EXISTS blood_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    blood_type VARCHAR(5),
    units INT,
    status VARCHAR(10),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS blood_inventory (
    blood_type VARCHAR(5) PRIMARY KEY,
    total_units INT DEFAULT 0
)");

// Handle form POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['blood_type'], $_POST['units'], $_POST['action'])) {
    $username = $_POST['username'];
    $blood_type = $_POST['blood_type'];
    $units = (int)$_POST['units'];
    $status = $_POST['action'];

    $valid_blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    if (!empty($username) && in_array($blood_type, $valid_blood_types) && $units > 0 && in_array($status, ['add', 'remove'])) {
        $check_user_sql = "SELECT username FROM donors WHERE username = ? 
                           UNION 
                           SELECT username FROM patients WHERE username = ?";
        $check_stmt = $conn->prepare($check_user_sql);
        $check_stmt->bind_param("ss", $username, $username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows === 0) {
            echo "<script>alert('Username not found in donors or patients');</script>";
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();

        if ($status === 'add') {
            $stmt = $conn->prepare("INSERT INTO blood_inventory (blood_type, total_units) VALUES (?, ?) 
                                    ON DUPLICATE KEY UPDATE total_units = total_units + ?");
            $stmt->bind_param("sii", $blood_type, $units, $units);
            if ($stmt->execute()) {
                $log_stmt = $conn->prepare("INSERT INTO blood_units (username, blood_type, units, status) VALUES (?, ?, ?, ?)");
                $log_stmt->bind_param("ssis", $username, $blood_type, $units, $status);
                $log_stmt->execute();
                $log_stmt->close();
                echo "<script>alert('Units added successfully!');</script>";
            }
            $stmt->close();
        } elseif ($status === 'remove') {
            $stmt = $conn->prepare("SELECT total_units FROM blood_inventory WHERE blood_type = ?");
            $stmt->bind_param("s", $blood_type);
            $stmt->execute();
            $stmt->bind_result($current_units);
            if ($stmt->fetch()) {
                $stmt->free_result();
                $stmt->close();
                if ($current_units >= $units) {
                    $remove_stmt = $conn->prepare("UPDATE blood_inventory SET total_units = total_units - ? WHERE blood_type = ?");
                    $remove_stmt->bind_param("is", $units, $blood_type);
                    if ($remove_stmt->execute()) {
                        $log_stmt = $conn->prepare("INSERT INTO blood_units (username, blood_type, units, status) VALUES (?, ?, ?, ?)");
                        $log_stmt->bind_param("ssis", $username, $blood_type, $units, $status);
                        $log_stmt->execute();
                        $log_stmt->close();
                        echo "<script>alert('Units removed successfully!');</script>";
                    }
                    $remove_stmt->close();
                } else {
                    echo "<script>alert('Insufficient stock to remove!');</script>";
                }
            } else {
                echo "<script>alert('Blood type not found in inventory!');</script>";
            }
        }
    } else {
        echo "<script>alert('Invalid input data!');</script>";
    }
}

// Fetch data
$result = $conn->query("SELECT * FROM blood_units ORDER BY timestamp DESC");
$inventory_result = $conn->query("SELECT * FROM blood_inventory");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Blood Unit Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');

    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
        background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
        color: white;
        overflow-x: hidden;
        position: relative;
    }

    h2 {
        color: #fff;
        text-align: center;
        margin-top: 20px;
    }

    form {
        background: rgba(255, 255, 255, 0.05);
        padding: 20px;
        border-radius: 10px;
        margin: 30px auto;
        width: 90%;
        max-width: 700px;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
        color: white;
    }

    label {
        display: block;
        margin: 8px 0 4px 0;
    }

    input, select {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        margin: 5px 0 15px 0;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 100%;
        box-sizing: border-box;
    }

    button {
        padding: 8px 18px;
        background: #00bfa6;
        color: white;
        border: none;
        border-radius: 4px;
        margin: 10px 5px 0 0;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    button:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .center-buttons {
        text-align: center;
        margin-top: 10px;
    }

    table {
        width: 90%;
        margin: 20px auto;
        border-collapse: collapse;
        text-align: center;
        background: rgba(255, 255, 255, 0.05);
        display: none;
    }

    th, td {
        border: 1px solid #ccc;
        padding: 12px;
    }

    th {
        background-color: rgba(255, 255, 255, 0.1);
    }

    tr:nth-child(even) td {
        background-color: rgba(255, 255, 255, 0.08);
    }

    .visible {
        display: table;
    }

    .icon-container {
      position: absolute;
      top: 1px;
      left: 20px;
    }
    .icon-container a {
      color: #fff;
      font-size: 20px;
      text-decoration: none;
    }
    </style>
</head>
<body>
<div class="icon-container">
    <a href="admin_dashboard.php"><i class="fas fa-home"></i></a>
  </div>
    <h2>Blood Unit Management</h2>

    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="blood_type">Blood Type:</label>
        <select id="blood_type" name="blood_type" required>
            <option value="">Select</option>
            <?php
            foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type) {
                echo "<option value='$type' style='color:black;'>$type</option>";
            }
            ?>
        </select>

        <label for="units">Units:</label>
        <input type="number" id="units" name="units" min="1" required>

        <button type="submit" name="action" value="add">Add</button>
        <button type="submit" name="action" value="remove">Remove</button>
    </form>

    <div class="center-buttons">
        <button onclick="toggleTable('transactions_table')">View Transaction History</button>
        <button onclick="toggleTable('inventory_table')">View Available Blood Units</button>
    </div>

    <table id="transactions_table">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Blood Type</th>
            <th>Units</th>
            <th>Status</th>
            <th>Timestamp</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            $id = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . $id++ . "</td>
                    <td>{$row['username']}</td>
                    <td>{$row['blood_type']}</td>
                    <td>{$row['units']}</td>
                    <td>{$row['status']}</td>
                    <td>{$row['timestamp']}</td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No records found</td></tr>";
        }
        ?>
    </table>

    <table id="inventory_table">
        <tr>
            <th>Blood Type</th>
            <th>Total Units</th>
        </tr>
        <?php
        if ($inventory_result->num_rows > 0) {
            while ($row = $inventory_result->fetch_assoc()) {
                echo "<tr><td>{$row['blood_type']}</td><td>{$row['total_units']}</td></tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No inventory data found</td></tr>";
        }
        ?>
    </table>

    <script>
        function toggleTable(id) {
            const table = document.getElementById(id);
            table.classList.toggle('visible');
        }
    </script>

</body>
</html>

<?php $conn->close(); ?>
