<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "sepm";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add Event
if (isset($_POST['add'])) {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_location = $_POST['event_location'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO events (event_name, event_date, event_location, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $event_name, $event_date, $event_location, $description);
    $stmt->execute();
    $stmt->close();
    echo "<script>window.location.href='manage_events.php';</script>";
    exit();
}

// Delete Event
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM events WHERE id = $id");
    echo "<script>window.location.href='manage_events.php';</script>";
    exit();
}

// Update Event
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_location = $_POST['event_location'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE events SET event_name=?, event_date=?, event_location=?, description=? WHERE id=?");
    $stmt->bind_param("ssssi", $event_name, $event_date, $event_location, $description, $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>window.location.href='manage_events.php';</script>";
    exit();
}

// Fetch event for editing
$edit_event = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM events WHERE id = $edit_id");
    if ($result->num_rows > 0) {
        $edit_event = $result->fetch_assoc();
    }
}

// Fetch all events
$events = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f6f8;
        }

        h2 {
            color: #007BFF;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 25px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        form {
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #e6f9f6;
        }

        textarea {
            resize: vertical;
            height: 120px;
        }

        .btn {
            display: inline-block;
            margin-top: 15px;
            margin-right: 10px;
            padding: 10px 18px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #00bfa6;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .actions a {
            margin-right: 8px;
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
<div class="container">
    <h2><?= $edit_event ? "Edit Event" : "Add Event" ?></h2>
    <form method="post" action="manage_events.php">
        <?php if ($edit_event): ?>
            <input type="hidden" name="id" value="<?= $edit_event['id'] ?>">
        <?php endif; ?>

        <label>Event Name:</label>
        <input type="text" name="event_name" value="<?= $edit_event['event_name'] ?? '' ?>" required>

        <label>Event Date:</label>
        <input type="date" name="event_date" value="<?= $edit_event['event_date'] ?? '' ?>" required>

        <label>Location:</label>
        <input type="text" name="event_location" value="<?= $edit_event['event_location'] ?? '' ?>" required>

        <label>Description:</label>
        <textarea name="description" required><?= $edit_event['description'] ?? '' ?></textarea>

        <?php if ($edit_event): ?>
            <button type="submit" name="update" class="btn">Update</button>
        <?php else: ?>
            <button type="submit" name="add" class="btn">Add</button>
        <?php endif; ?>
    </form>

    <h2>Event List</h2>
    <table>
        <thead>
        <tr>
            <th>Id</th>
            <th>Event Name</th>
            <th>Date</th>
            <th>Location</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $display_id = 1;
        while ($row = $events->fetch_assoc()): ?>
            <tr>
                <td><?= $display_id++ ?></td>
                <td><?= htmlspecialchars($row['event_name']) ?></td>
                <td><?= $row['event_date'] ?></td>
                <td><?= htmlspecialchars($row['event_location']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td class="actions">
                    <a href="manage_events.php?edit=<?= $row['id'] ?>" class="btn">Edit</a>
                    <a href="manage_events.php?delete=<?= $row['id'] ?>" class="btn" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
