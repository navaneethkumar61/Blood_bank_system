<?php
include 'connect.php';

// Fetch donor profiles
$sql = "SELECT dp.username, dob, blood_group, gender, phone, address, city, state, pincode, last_donation_date, d.email
        FROM donor_profile dp
        JOIN donors d ON dp.username = d.username";
$result = $conn->query($sql);

// Function to calculate age from date of birth
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Donor Profiles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #0c1124;
            color: white;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
            color: #00bfa6;
        }
        table {
            width: 95%;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: #0f172a;
        }
        th, td {
            border: 1px solid #374151;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #111827;
            color: #f87171;
        }
        tr:hover {
            background-color: #1f2937;
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
<h2>Donor Profiles</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Blood Type</th>
            <th>Gender</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Address</th>
            <th>City</th>
            <th>State</th>
            <th>Pin code</th>
            <th>Last Donation Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php $display_id = 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $display_id++; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo calculateAge($row['dob']); ?></td>
                    <td><?php echo htmlspecialchars($row['blood_group']); ?></td>
                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo htmlspecialchars($row['city']); ?></td>
                    <td><?php echo htmlspecialchars($row['state']); ?></td>
                    <td><?php echo htmlspecialchars($row['pincode']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_donation_date']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9">No donor profiles found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
