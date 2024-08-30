<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone to GMT+6
date_default_timezone_set('Asia/Dhaka');

// Database connection
$servername = "localhost";
$username = "farmauto_farmautomation400";
$password = "RTX4090ti@";
$dbname = "farmauto_farmautomation400";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set MySQL server time zone to GMT+6
$conn->query("SET time_zone = '+06:00'");

// Get device ID and action from request
$device_id = $_GET['device_id'] ?? '';
$action = $_GET['action'] ?? '';

if ($action === 'ping' && !empty($device_id)) {
    // Get the current date and time in GMT+6
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');

    // Insert or update the ping date and time for the device
    $sql = "INSERT INTO device_status (device_id, last_ping_date, last_ping_time) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE last_ping_date = ?, last_ping_time = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issss', $device_id, $current_date, $current_time, $current_date, $current_time);
    $stmt->execute();
    $stmt->close();
    echo "Ping received for device $device_id.";
} else {
    // Fetch all devices and their statuses
    $sql = "SELECT device_id, last_ping_date, last_ping_time FROM device_status";
    $result = $conn->query($sql);

    $inactive_time = 2 * 60; // 2 minutes in seconds
    $devices = [];

    // Get the current time in GMT+6
    $current_time = time(); // Current Unix timestamp adjusted to GMT+6 by default timezone

    while ($row = $result->fetch_assoc()) {
        $last_ping = strtotime($row['last_ping_date'] . ' ' . $row['last_ping_time']); // Combine date and time
        $time_diff = $current_time - $last_ping;

        if ($time_diff > $inactive_time) {
            $status = 'Inactive';
            $countdown_seconds = 0; // Set to 0 when inactive
            $countdown = '00:00:00';
        } else {
            $status = 'Active';
            $countdown_seconds = max(0, $inactive_time - $time_diff); // Ensure countdown is never negative
            $countdown = formatCountdown($countdown_seconds);
        }
        $time_since_last_ping = formatCountdown($time_diff);

        $devices[] = [
            'device_id' => $row['device_id'],
            'status' => $status,
            'time_since_last_ping' => $time_since_last_ping,
            'countdown' => $countdown,
            'current_time' => date('Y-m-d H:i:s') // Format current time as a human-readable string in GMT+6
        ];
    }

    echo json_encode($devices);
}

// Close connection
$conn->close();

// Function to format countdown
function formatCountdown($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}
?>
