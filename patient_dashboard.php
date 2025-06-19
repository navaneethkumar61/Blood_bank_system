<?php
// Start session
session_start();

// === DATABASE CONNECTION CODE === //
$host = "localhost";       // or your DB host
$user = "root";            // your DB username
$password = "";            // your DB password
$database = "sepm";   // your DB name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// === FETCH DONOR STATS === //
$username = $_SESSION['username'] ?? '';

$totalDonations = $acceptedDonations = $pendingDonations = $rejectedDonations = 0;

if ($username) {
    $query = "SELECT status, COUNT(*) as count FROM blood_requests WHERE username = ? GROUP BY status";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['status']);
        $count = $row['count'];
        $totalDonations += $count;

        if ($status === 'approved') $acceptedDonations = $count;
        elseif ($status === 'pending') $pendingDonations = $count;
        elseif ($status === 'rejected') $rejectedDonations = $count;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Patient Dashboard</title>

  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      height: 100vh;
      background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
      color: white;
      display: flex;
      overflow-x: hidden;
    }

    .toggle-btn {
      position: fixed;
      top: 12px;
      left: 12px;
      z-index: 1001;
      background-color: #00bfa6;
      padding: 6px;
      border-radius: 4px;
      cursor: pointer;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .toggle-btn:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .toggle-btn .material-symbols-outlined {
      font-size: 24px;
      color: white;
      line-height: 1;
    }

    .menu {
      position: fixed;
      top: 0;
      left: 0;
      width: 250px;
      height: 100%;
      background-color: #00897b;
      padding-top: 60px;
      z-index: 1000;
      transition: all 0.3s ease;
    }

    .menu.active {
      width: 0;
      overflow: hidden;
    }

    .menu-content {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .menu-content li {
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .menu-content li a {
      text-decoration: none;
      display: flex;
      align-items: center;
      padding: 15px 20px;
      color: white;
      transition: background-color 0.3s ease;
      gap: 10px;
    }

    .menu-content li a:hover {
      background-color: rgba(0, 0, 0, 0.2);
      border-radius: 0 20px 20px 0;
    }

    .menu-content li a span,
    .menu-content li a i {
      margin-right: 10px;
      font-size: 20px;
    }

    .material-symbols-outlined {
      font-size: 24px;
    }

    .dashboard-content {
      margin-left: 250px;
      padding: 30px;
      transition: margin-left 0.3s ease;
    }

    .dashboard-content.active {
      margin-left: 0;
    }

    h1 {
      font-size: 32px;
      margin-bottom: 10px;
      color: #b2fef7;
    }

    p {
      font-size: 18px;
      color: #d9f9f6;
    }

    .stats-container {
      display: flex;
      gap: 20px;
      margin-top: 30px;
      flex-wrap: wrap;
    }

    .stat-box {
      background: rgba(255, 255, 255, 0.1);
      padding: 20px;
      border-radius: 10px;
      min-width: 200px;
      text-align: center;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .stat-box i {
      font-size: 24px;
      color: #00bfa6;
      margin-bottom: 10px;
    }

    .stat-box h3 {
      font-size: 16px;
      margin: 10px 0;
      color: #b2fef7;
    }

    .stat-box p {
      font-size: 24px;
      font-weight: bold;
      margin: 0;
      color: #ffffff;
    }

    .stat-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 191, 166, 0.4);
}

  </style>

  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<body>
  <div class="toggle-btn" onclick="toggleMenu()">
    <span class="material-symbols-outlined">menu</span>
  </div>

  <div class="menu" id="sidebar">
    <ul class="menu-content">
      <li><a href="#"><span class="material-symbols-outlined">dashboard</span><span>Dashboard</span></a></li>
      <li><a href="patient_request.php"><i class="fa-solid fa-hand-holding-medical"></i><span>Request Blood</span></a></li>
      <li><a href="patient_reports.php"><span class="material-symbols-outlined">report</span><span>My Requests</span></a></li>
      <li><a href="patient_profile.php"><i class="fa-regular fa-address-card"></i><span>Update Profile</span></a></li>
      <li><a href="logout.php"><span class="material-symbols-outlined">logout</span><span>Logout</span></a></li>
    </ul>
  </div>

  <div class="dashboard-content" id="mainContent">
    <h1>Patient Dashboard</h1>
    <p>Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>!</p>

    <div class="stats-container">
      <div class="stat-box">
        <i class="fa-solid fa-droplet"></i>
        <h3>Total Requests</h3>
        <p><?php echo $totalDonations; ?></p>
      </div>
      <div class="stat-box">
        <i class="fa-solid fa-check-circle"></i>
        <h3>Accepted</h3>
        <p><?php echo $acceptedDonations; ?></p>
      </div>
      <div class="stat-box">
        <i class="fa-solid fa-hourglass-half"></i>
        <h3>Pending</h3>
        <p><?php echo $pendingDonations; ?></p>
      </div>
      <div class="stat-box">
        <i class="fa-solid fa-times-circle"></i>
        <h3>Rejected</h3>
        <p><?php echo $rejectedDonations; ?></p>
      </div>
    </div>
  </div>

  <script>
    function toggleMenu() {
      const menu = document.getElementById('sidebar');
      const content = document.getElementById('mainContent');
      menu.classList.toggle('active');
      content.classList.toggle('active');
    }
  </script>
</body>
</html>
