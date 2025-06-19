<?php
function isDonorProfileComplete($conn, $username) {
    $sql = "SELECT * FROM donor_profile WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $donor = $result->fetch_assoc();

    if (!$donor) {
        return false;
    }

    $required_fields = ['full_name', 'phone', 'dob', 'blood_group', 'address', 'city', 'state', 'pincode'];
    foreach ($required_fields as $field) {
        if (empty($donor[$field])) {
            return false;
        }
    }

    return true;
}
?>
