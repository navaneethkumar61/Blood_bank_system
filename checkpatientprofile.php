<?php
function isPatientProfileComplete($conn, $username) {
    $stmt = $conn->prepare("SELECT full_name, dob, gender FROM patient_profile WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return !empty($row['full_name']) && !empty($row['dob']) && !empty($row['gender']);
    }

    return false;
}
?>
