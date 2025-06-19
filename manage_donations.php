<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donation_id = $_POST['donation_id'];
    $action = $_POST['action'];

    // Fetch the current status and details of this donation
    $stmt = $conn->prepare("SELECT status, blood_type, quantity FROM donations WHERE id = ?");
    $stmt->bind_param("i", $donation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $donation = $result->fetch_assoc();

    if ($donation) {
        $current_status = $donation['status'];
        $blood_type = $donation['blood_type'];
        $quantity = (int)$donation['quantity'];

        $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';

        // Only act if there's a change in status
        if ($new_status !== $current_status) {
            // Update the donation status
            $update = $conn->prepare("UPDATE donations SET status = ? WHERE id = ?");
            $update->bind_param("si", $new_status, $donation_id);
            $update->execute();

            if ($new_status === 'Approved') {
                // Check if the blood_type already exists in blood_inventory
                $check = $conn->prepare("SELECT * FROM blood_inventory WHERE blood_type = ?");
                $check->bind_param("s", $blood_type);
                $check->execute();
                $check_result = $check->get_result();
            
                if ($check_result->num_rows > 0) {
                    // Exists – update total_units
                    $inventory = $conn->prepare("UPDATE blood_inventory SET total_units = total_units + ? WHERE blood_type = ?");
                    $inventory->bind_param("is", $quantity, $blood_type);
                    $inventory->execute();
                } else {
                    // Doesn't exist – insert new record
                    $insert = $conn->prepare("INSERT INTO blood_inventory (blood_type, total_units) VALUES (?, ?)");
                    $insert->bind_param("si", $blood_type, $quantity);
                    $insert->execute();
                }
            }
            elseif ($new_status === 'Rejected' && $current_status === 'Approved') {
                // Decrease inventory only if previously approved
                $inventory = $conn->prepare("UPDATE blood_inventory SET total_units = GREATEST(total_units - ?, 0) WHERE blood_type = ?");
                $inventory->bind_param("is", $quantity, $blood_type);
                $inventory->execute();
            }
        }
    }

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$donations_result = $conn->query("SELECT * FROM donations");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Blood Donations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Same styling as you had before */
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
            padding: 4px 10px;
            background-color: #dfe6e9;
            border-radius: 4px;
            margin-left: 8px;
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

<h2>Blood Donations List</h2>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Username</th>
            <th>Age</th>
            <th>Blood Type</th>
            <th>Quantity</th>
            <th>Donation Date</th>
            <th>Disease</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $id = 1;
        while ($donation = $donations_result->fetch_assoc()): 
            $status = strtolower(trim($donation['status'] ?? 'Pending'));
        ?>
            <tr>
                <td><?= $id++ ?></td>
                <td><?= htmlspecialchars($donation['username']) ?></td>
                <td><?= htmlspecialchars($donation['age']) ?></td>
                <td><?= htmlspecialchars($donation['blood_type']) ?></td>
                <td><?= htmlspecialchars($donation['quantity']) ?></td>
                <td><?= htmlspecialchars($donation['donation_date']) ?></td>
                <td><?= htmlspecialchars($donation['disease']) ?></td>
                <td><?= ucfirst($status) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="donation_id" value="<?= $donation['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn approve">Approve</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="donation_id" value="<?= $donation['id'] ?>">
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
