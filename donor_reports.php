<?php
session_start();

// Check if donor is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// DB connection
$conn = new mysqli("localhost", "root", "", "sepm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch donation history joined with donor's DOB
$stmt = $conn->prepare("
    SELECT d.disease, d.quantity, d.donation_date, d.status, p.dob
    FROM donations d
    JOIN donor_profile p ON d.username = p.username
    WHERE d.username = ?
    ORDER BY d.donation_date DESC
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$donations = [];

while ($row = $result->fetch_assoc()) {
    $donations[] = $row;
}
$stmt->close();
$conn->close();

// Age calculation function
function calculateAge($dob) {
    if ($dob == null || $dob == '0000-00-00') return 'N/A';
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Donation History</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to right, #000000, #0f2027);
      color: #fff;
      margin: 0;
    }
    .icon-container {
      position: absolute;
      top: 20px;
      left: 20px;
    }
    .icon-container a {
      color: #fff;
      font-size: 20px;
      text-decoration: none;
    }
    .container {
      width: 90%;
      max-width: 1000px;
      margin: 80px auto;
      background: rgba(255,255,255,0.05);
      padding: 30px;
      border-radius: 20px;
      backdrop-filter: blur(8px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #7FFFD4;
    }
    .filters {
      text-align: center;
      margin-bottom: 20px;
    }
    .filters button {
      padding: 10px 20px;
      margin: 5px;
      border: none;
      border-radius: 20px;
      background-color: #1abc9c;
      color: white;
      font-weight: bold;
      cursor: pointer;
    }
    .filters button.inactive {
      background-color: #34495e;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 14px;
      border: 1px solid rgba(255,255,255,0.1);
      text-align: center;
    }
    th {
      background-color: rgba(0,0,0,0.4);
      color: #00FFFF;
    }
    .status-approved {
      background: #d4edda;
      color: #155724;
    }
    .status-rejected {
      background: #f8d7da;
      color: #721c24;
    }
    .status-pending {
      background: #dee2e6;
      color: #6c757d;
    }
    .status-pill {
      padding: 6px 12px;
      border-radius: 8px;
      font-weight: bold;
      display: inline-block;
    }
    .message-box {
      text-align: center;
      padding: 40px;
      background: rgba(255,255,255,0.08);
      border-radius: 16px;
    }
    .message-box i {
      font-size: 40px;
      margin-bottom: 10px;
      color: #7FFFD4;
    }
  </style>
</head>
<body>
  <div class="icon-container">
    <a href="donor_dashboard.php"><i class="fas fa-home"></i></a>
  </div>

  <div class="container">
    <h2>DONATION HISTORY</h2>
    
    <div class="filters">
      <button class="filter-btn active" data-status="All">All</button>
      <button class="filter-btn inactive" data-status="Approved">Approved</button>
      <button class="filter-btn inactive" data-status="Rejected">Rejected</button>
      <button class="filter-btn inactive" data-status="Pending">Pending</button>
    </div>

    <?php if (count($donations) === 0): ?>
      <div class="message-box">
        <i class="fas fa-info-circle"></i>
        <h3>No Donations Found</h3>
        <p>You haven't donated blood yet. Your future donations will appear here.</p>
      </div>
    <?php else: ?>
      <table id="donationTable">
        <thead>
          <tr>
            <th>Donor Age</th>
            <th>Disease</th>
            <th>Unit</th>
            <th>Date and Time</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($donations as $row): ?>
            <tr data-status="<?php echo strtolower($row['status']); ?>">
              <td><?php echo calculateAge($row['dob']); ?></td>
              <td><?php echo htmlspecialchars($row['disease']); ?></td>
              <td><?php echo htmlspecialchars($row['quantity']); ?></td>
              <td><?php echo htmlspecialchars($row['donation_date']); ?></td>
              <td>
                <?php
                  $status = strtolower($row['status']);
                  if ($status === 'approved') {
                      echo '<span class="status-pill status-approved">Approved</span>';
                  } elseif ($status === 'rejected') {
                      echo '<span class="status-pill status-rejected">Rejected</span>';
                  } else {
                      echo '<span class="status-pill status-pending">Pending</span>';
                  }
                ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <script>
    const filterButtons = document.querySelectorAll('.filter-btn');
    const rows = document.querySelectorAll('#donationTable tbody tr');

    filterButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const status = btn.dataset.status.toLowerCase();

        filterButtons.forEach(b => b.classList.remove('active'));
        filterButtons.forEach(b => b.classList.add('inactive'));
        btn.classList.add('active');
        btn.classList.remove('inactive');

        rows.forEach(row => {
          const rowStatus = row.dataset.status;
          if (status === "all" || rowStatus === status) {
            row.style.display = "";
          } else {
            row.style.display = "none";
          }
        });
      });
    });
  </script>
</body>
</html>
