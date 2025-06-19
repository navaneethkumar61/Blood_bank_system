<?php
include 'connect.php';

// Get total number of patients (total requests)
$request_result = $conn->query("SELECT COUNT(*) AS total_requests FROM patients");
$request_count = 0;
if ($request_result && $request_result->num_rows > 0) {
    $row = $request_result->fetch_assoc();
    $request_count = $row['total_requests'];
}

// Get total number of donors
$donor_result = $conn->query("SELECT COUNT(*) AS total_donors FROM donors");
$donor_count = 0;
if ($donor_result && $donor_result->num_rows > 0) {
    $row = $donor_result->fetch_assoc();
    $donor_count = $row['total_donors'];
}

// Query for blood inventory
$inventory_result = $conn->query("SELECT * FROM blood_inventory");

if ($inventory_result === false) {
    echo "Database query failed: " . $conn->error;
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
      color: white;
      transition: margin-left 0.3s ease;
    }

    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 60px;
      background-color: #00bfa6;
      display: flex;
      align-items: center;
      padding: 0 20px;
      z-index: 1000;
    }

    .navbar #menuIcon {
      font-size: 24px;
      cursor: pointer;
      background-color: #00796b;
      padding: 8px 12px;
      border-radius: 4px;
      color: white;
    }

    .navbar-title {
      font-size: 20px;
      font-weight: bold;
      margin-left: 15px;
    }

    .sidebar {
      position: fixed;
      top: 60px;
      left: 0;
      width: 250px;
      height: calc(100vh - 60px);
      background-color: #00897b;
      align-items: center;
      padding-top: 20px;
      transition: transform 0.3s ease;
      transform: translateX(-250px);
      z-index: 999;
      overflow-y: auto;
    }
    

    .sidebar.active {
      transform: translateX(0);
    }

    .sidebar h2 {
      text-align: center;
      color: #b2fef7;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar ul li {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .sidebar ul li a {
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 16px;
      transition: background-color 0.3s ease;
    }

    .sidebar ul li a:hover {
      background-color: rgba(0, 0, 0, 0.2);
      border-radius: 0 20px 20px 0;
    }

    .main-content {
      margin-top: 80px;
      margin-left: auto;
      margin-right: auto;
      max-width: 1000px;
      padding: 20px;
      transition: margin-left 0.3s ease, transform 0.3s ease;
    }

    .main-content.shifted {
      margin-left: 250px;
    }

    /* .inventory-boxes {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 20px;
    } */

    /* Styles for individual boxes (both stats and inventory) */
.box {
  background-color: rgba(0, 150, 136, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  padding: 20px;
  text-align: center;
  backdrop-filter: blur(6px);
  box-shadow: 0 4px 8px rgba(0, 191, 166, 0.2);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.box:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 191, 166, 0.4);
}

.box h3 {
  font-size: 18px;
  margin-bottom: 10px;
  color: #b2fef7;
}

.box p {
  font-size: 16px;
  color: #ffffff;
}

/* For consistent spacing and grid layout */
.inventory-boxes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 20px;
}


    

    .stat-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.stat-box i {
  font-size: 36px;
  color: #00bfa6;
  margin-bottom: 10px;
}

.stat-label {
  font-size: 16px;
  color: #b2fef7;
  margin-bottom: 8px;
}

.stat-count {
  font-size: 24px;
  font-weight: bold;
  color: #ffffff;
}

    .btn {
      padding: 10px 20px;
      background-color: #00bfa6;
      color: white;
      border: none;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #00897b;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      background-color: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(6px);
    }

    table, th, td {
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    th {
      background-color: rgba(0, 150, 136, 0.8);
      color: #b2fef7;
    }

    th, td {
      padding: 10px;
      text-align: left;
      color: white;
    }

    
  </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
  <div id="menuIcon" onclick="toggleSidebar()"><i class="fas fa-bars"></i></div>
  <div class="navbar-title">Admin Dashboard</div>
</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <h2>Welcome Admin!</h2>
  <ul>
    <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
    <li><a href="manage_donors.php"><i class="fas fa-user-plus"></i> Donor Profiles</a></li>
    <li><a href="manage_patients.php"><i class="fas fa-procedures"></i> Patient Profiles</a></li>
    <li><a href="manage_events.php"><i class="fas fa-calendar-check"></i> Manage Events</a></li>
    <li><a href="manage_messages.php"><i class="fas fa-envelope"></i> Manage Messages</a></li>
    <li><a href="manage_donations.php"><i class="fa-solid fa-hand-holding-medical"></i> Manage Donations</a></li>
    <li><a href="manage_requests.php"><i class="fa-solid fa-person-circle-question"></i> Manage requests</a></li>
    <li><a href="update_inventory.php"><i class="fa-solid fa-warehouse"></i> Update inventory</a></li>
    <li><a href="manage_hospitals.php"><i class="fa-solid fa-house-medical"></i> Manage hospitals</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
  <?php
  switch ($page) {
    case 'dashboard':
      echo "<h1><i class='fas fa-warehouse'></i> Blood Inventory</h1><div class='inventory-boxes'>";
      if ($inventory_result->num_rows > 0) {
        while ($row = $inventory_result->fetch_assoc()) { ?>
          <div class="box">
            <h3><i class="fas fa-tint"></i> <?php echo htmlspecialchars($row['blood_type']); ?></h3>
            <p><?php echo htmlspecialchars($row['total_units']); ?> units available</p>
          </div>
        <?php }
      } else { ?>
        <p>No blood inventory data available.</p>
      <?php }
      echo "</div>";

      echo "<h2><i class='fas fa-chart-bar'></i> Statistics</h2>";
      echo "<div class='inventory-boxes'>";
      echo "<div class='box'>
  <div class='stat-box'>
    <i class='fas fa-users'></i>
    <div class='stat-label'>No. of Donors</div>
    <div class='stat-count'>$donor_count</div>
  </div>
</div>";

echo "<div class='box'>
  <div class='stat-box'>
    <i class='fas fa-user-injured'></i>
    <div class='stat-label'>No. of Patients</div>
    <div class='stat-count'>$request_count</div>
  </div>
</div>";


      echo "</div>";
      break;

    case 'manage_donors':
      echo "<h1><i class='fas fa-user-plus'></i> Manage Donors</h1>";
      include 'manage_donors.php';
      break;

    case 'manage_patients':
      echo "<h1><i class='fas fa-procedures'></i> Manage Patients</h1>";
      include 'manage_patients.php';
      break;

    case 'manage_events':
      echo "<h1><i class='fas fa-calendar-check'></i> Manage Events</h1>";
      include 'manage_events.php';
      break;

    case 'manage_messages':
      echo "<h1><i class='fas fa-envelope'></i> Manage Messages</h1>";
      include 'manage_messages.php';
      break;

    default:
      echo "<h1><i class='fas fa-home'></i> Dashboard</h1>";
      break;
  }
  ?>
</div>

<script>
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');

  function toggleSidebar() {
    sidebar.classList.toggle('active');
    mainContent.classList.toggle('shifted');
  }
</script>

</body>
</html>
