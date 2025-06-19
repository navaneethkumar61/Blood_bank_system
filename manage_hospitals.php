<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sepm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(200) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($sql);

if (isset($_GET['clear'])) {
    $conn->query("TRUNCATE TABLE hospitals");
    echo "<script>alert('All hospital records cleared!');</script>";
    echo "<script>window.location.href='manage_hospitals.php';</script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $action = $_POST['action'] ?? '';

    if (empty($name) || empty($address) || empty($phone) || empty($email)) {
        echo "<script>alert('All fields are required.');</script>";
        exit;
    }

    if (!preg_match('/^\d{10,15}$/', $phone)) {
        echo "<script>alert('Phone number must be 10 to 15 digits.');</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email address.');</script>";
        exit;
    }

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO hospitals (name, address, phone, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $address, $phone, $email);
        $stmt->execute();
        echo "<script>alert('Hospital added successfully!');</script>";
        echo "<script>window.location.href='manage_hospitals.php';</script>";
    } elseif ($action === 'update') {
        $stmt = $conn->prepare("UPDATE hospitals SET name = ?, address = ?, phone = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $address, $phone, $email, $id);
        $stmt->execute();
        echo "<script>alert('Hospital updated successfully!');</script>";
        echo "<script>window.location.href='manage_hospitals.php';</script>";
    }
}

$edit_row = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt_edit = $conn->prepare("SELECT * FROM hospitals WHERE id = ?");
    $stmt_edit->bind_param("i", $edit_id);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    if ($result_edit->num_rows > 0) {
        $edit_row = $result_edit->fetch_assoc();
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt_delete = $conn->prepare("DELETE FROM hospitals WHERE id = ?");
    $stmt_delete->bind_param("i", $delete_id);
    if ($stmt_delete->execute()) {
        echo "<script>alert('Hospital deleted successfully!');</script>";
        echo "<script>window.location.href='manage_hospitals.php';</script>";
    } else {
        echo "<script>alert('Error deleting hospital');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hospital Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 20px;
        background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
        color: #333;
    }

    h2 {
        text-align: center;
        color: #00bfa6;
    }

    form {
        max-width: 500px;
        margin: 0 auto 30px auto;
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    label {
        display: block;
        margin-top: 10px;
        font-weight: bold;
    }

    input {
        margin-top: 5px;
        margin-bottom: 15px;
        padding: 10px;
        width: 90%;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
    }

    button {
        padding: 10px 20px;
        background-color: #00bfa6;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
    }

    button:hover {
        background-color: #009e8c;
    }

    a {
        text-decoration: none;
        color: #00bfa6;
        font-weight: bold;
    }

    .clear-btn {
        display: block;
        width: fit-content;
        margin: 0 auto 30px auto;
        background-color: #00bfa6;
        padding: 10px 20px;
        border-radius: 10px;
        color: white;
        text-align: center;
        font-weight: bold;
    }

    .clear-btn:hover {
        background-color: #009e8c;
    }

    table {
        width: 85%;
        margin: 0 auto 50px auto;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    th, td {
        border: 1px solid #e5e7eb;
        padding: 12px;
        text-align: center;
    }

    th {
        background-color: #00bfa6;
        color: white;
        font-weight: normal;
    }

    tr:nth-child(even) {
        background-color: #f3f4f6;
    }

    tr:hover {
        background-color: #e0f2fe;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .btn-edit, .btn-delete {
        padding: 6px 14px;
        border: none;
        border-radius: 999px;
        font-size: 13px;
        cursor: pointer;
        color: white;
        font-weight: bold;
    }

    .btn-edit {
        background-color: #4CAF50;
    }

    .btn-edit:hover {
        background-color: #3e8e41;
    }

    .btn-delete {
        background-color: #f44336;
    }

    .btn-delete:hover {
        background-color: #d32f2f;
    }

    @media screen and (max-width: 768px) {
        form, table {
            width: 100%;
            overflow-x: auto;
        }
    }

    .icon-container {
      position: absolute;
      top: 20px;
      left: 20px;
    }
    .icon-container a {
      color: white;
      font-size: 20px;
      text-decoration: none;
    }
    </style>
</head>
<body>
<div class="icon-container">
    <a href="admin_dashboard.php"><i class="fas fa-home"></i></a>
  </div>
    <h2>Hospital Management</h2>

    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo isset($edit_row['id']) ? $edit_row['id'] : ''; ?>">
        
        <label>Hospital Name:</label>
        <input type="text" name="name" required value="<?php echo isset($edit_row['name']) ? $edit_row['name'] : ''; ?>">
        
        <label>Address:</label>
        <input type="text" name="address" required value="<?php echo isset($edit_row['address']) ? $edit_row['address'] : ''; ?>">
        
        <label>Phone:</label>
        <input type="text" name="phone" pattern="\d{10,15}" title="Phone number should be 10 to 15 digits" required value="<?php echo isset($edit_row['phone']) ? $edit_row['phone'] : ''; ?>">
        
        <label>Email:</label>
        <input type="email" name="email" required value="<?php echo isset($edit_row['email']) ? $edit_row['email'] : ''; ?>">
        
        <button type="submit" name="action" value="<?php echo isset($edit_row['id']) ? 'update' : 'add'; ?>">
            <?php echo isset($edit_row['id']) ? 'Update' : 'Add'; ?>
        </button>
    </form>

    <!-- <a class="clear-btn" href="?clear=true">Clear All Hospital Records</a> -->

    <h2>Hospital Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Address</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>

        <?php
        $sql_fetch_all = "SELECT * FROM hospitals";
        $result_all = $conn->query($sql_fetch_all);

        if ($result_all->num_rows > 0) {
            while ($row = $result_all->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['address']}</td>
                        <td>{$row['phone']}</td>
                        <td>{$row['email']}</td>
                        <td class='action-buttons'>
                            <a href='manage_hospitals.php?edit={$row['id']}'><button class='btn-edit'>Edit</button></a>
                            <a href='manage_hospitals.php?delete={$row['id']}' onclick=\"return confirm('Are you sure you want to delete this hospital?');\"><button class='btn-delete'>Delete</button></a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No records found</td></tr>";
        }
        ?>
    </table>

</body>
</html>

<?php $conn->close(); ?>
