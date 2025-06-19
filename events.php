<?php
include 'connect.php';

// Select only events with event_date today or in the future
$sql = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='event-box'>";
        echo "<h3>" . htmlspecialchars($row["event_name"]) . "</h3>";
        echo "<p><strong>Date:</strong> " . htmlspecialchars($row["event_date"]) . "</p>";
        echo "<p><strong>Location:</strong> " . htmlspecialchars($row["event_location"]) . "</p>";
        echo "<p>" . htmlspecialchars($row["description"]) . "</p>";
        echo "</div>";
    }
} else {
    echo "<p>No upcoming events</p>";
}

$conn->close();
?>
