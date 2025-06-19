<?php
include 'connect.php';

function showAlertAndRedirect($message) {
    echo "<script>alert('$message'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit();
}

function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    return $age;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    // Fetch the request details
    $stmt = $conn->prepare("SELECT * FROM blood_requests WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if (!$request) {
        showAlertAndRedirect("Request not found.");
    }

    $requested_blood = $request['blood_type'];
    $requested_quantity = (int)$request['quantity'];

    if ($action === 'approve') {
        // Check blood_inventory
        $inv_stmt = $conn->prepare("SELECT total_units FROM blood_inventory WHERE blood_type = ?");
        $inv_stmt->bind_param("s", $requested_blood);
        $inv_stmt->execute();
        $inv_result = $inv_stmt->get_result();
        $inventory = $inv_result->fetch_assoc();

        $available_units = $inventory['total_units'] ?? 0;

        if ($available_units < $requested_quantity) {
            showAlertAndRedirect("Insufficient units in inventory for blood type: $requested_blood. Available: $available_units, Required: $requested_quantity");
        }

        // Update blood_requests status
        $status = 'Approved';
        $stmt = $conn->prepare("UPDATE blood_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        $stmt->execute();

        // Deduct from inventory
        $new_units = $available_units - $requested_quantity;
        $inv_update = $conn->prepare("UPDATE blood_inventory SET total_units = ? WHERE blood_type = ?");
        $inv_update->bind_param("is", $new_units, $requested_blood);
        $inv_update->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();

    } else {
        // If rejected
        $status = 'Rejected';
        $stmt = $conn->prepare("UPDATE blood_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        $stmt->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all blood requests
$requests_stmt = $conn->prepare("SELECT * FROM blood_requests ORDER BY id DESC");
$requests_stmt->execute();
$requests_result = $requests_stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Blood Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f9fc;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            padding: 20px;
            color: #2c3e50;
        }

        table {
            width: 90%;
            margin: 0 auto 40px;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
        }

        th {
            background-color: #ecf0f1;
            color: #34495e;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 5px;
        }

        .approve {
            background-color: #27ae60;
            color: white;
        }

        .approve:hover {
            background-color: #219150;
        }

        .reject {
            background-color: #e74c3c;
            color: white;
        }

        .reject:hover {
            background-color: #c0392b;
        }

        em {
            font-style: normal;
            font-weight: bold;
            color: #2980b9;
            background-color: #ecf0f1;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .icon-container {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .icon-container a {
            color: black;
            font-size: 20px;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="icon-container">
    <a href="admin_dashboard.php"><i class="fas fa-home"></i></a>
</div>

<h2>Blood Requests List</h2>
<table>
    <thead>
        <tr>
            <th>Id</th>
            <th>Username</th>
            <th>Age</th>
            <th>Blood Type</th>
            <th>Quantity</th>
            <th>Urgency</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $id = 1;
        while ($request = $requests_result->fetch_assoc()): 
            $status = ucfirst(trim($request['status'] ?? 'Pending'));
        ?>
            <tr>
                <td><?= $id++ ?></td>
                <td><?= htmlspecialchars($request['username']) ?></td>
                <td><?= calculateAge($request['dob']) ?></td>
                <td><?= htmlspecialchars($request['blood_type']) ?></td>
                <td><?= htmlspecialchars($request['quantity']) ?></td>
                <td><?= htmlspecialchars($request['urgency']) ?></td>
                <td><em><?= $status ?></em></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn approve">Approve</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn reject">Reject</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</body>
</html>
