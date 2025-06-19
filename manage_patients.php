<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Patients</title>
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
<h2>Patient Profiles</h2>

<div class="table-container">
  <table>
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Age</th>
      <th>Gender</th>
      <th>Blood Group</th>
      <th>Contact</th>
      <th>Email</th>
      <th>Address</th>
      <th>City</th>
      <th>State</th>
      <th>Pin code</th>
    </tr>

    <?php
    session_start();
    include 'connect.php';

    $query = "SELECT p.username, p.dob, p.gender, u.blood_type, p.phone, u.email ,p.street_address,p.city,p.state,p.zip_code
              FROM patient_profile p 
              JOIN patients u ON p.username = u.username";

    $result = $conn->query($query);

    function calculateAge($dob) {
        $birthDate = new DateTime($dob);
        $today = new DateTime('today');
        return $birthDate->diff($today)->y;
    }

    if ($result && $result->num_rows > 0):
        $display_id = 1;
        while($row = $result->fetch_assoc()):
    ?>
        <tr>
            <td><?= $display_id++ ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= calculateAge($row['dob']) ?></td>
            <td><?= htmlspecialchars($row['gender']) ?></td>
            <td><?= htmlspecialchars($row['blood_type']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['street_address']) ?></td>
            <td><?= htmlspecialchars($row['city']) ?></td>
            <td><?= htmlspecialchars($row['state']) ?></td>
            <td><?= htmlspecialchars($row['zip_code']) ?></td>
        </tr>
    <?php
        endwhile;
    else:
    ?>
        <tr>
            <td colspan="11" style="text-align:center;">No patient profiles found.</td>
        </tr>
    <?php endif; ?>
  </table>
</div>

</body>
</html>
