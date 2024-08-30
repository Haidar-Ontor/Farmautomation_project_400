<?php
// get_control_data.php

header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "farmauto_farmautomation400";
$password = "RTX4090ti@";
$dbname = "farmauto_farmautomation400";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(array("error" => "Connection failed: " . $conn->connect_error));
    exit();
}

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$control_columns = isset($_GET['control_columns']) ? $_GET['control_columns'] : array();

// Ensure control_columns is an array
if (!is_array($control_columns)) {
    $control_columns = array();
}

// Define valid control columns to prevent SQL injection
$valid_columns = array(
    "Pump_G", "Light_G", "Fan_G", "Door_G", "Heater_G", "G_mode",
    "Pump_f1", "Pump_f2", "Pump_f3", "Pump_Pond", "FP_mode",
    "C_mode", "C_Light", "C_Fan", "C_Pump", "C_Gate"
);

// Filter control_columns to include only valid columns
$selected_columns = array_intersect($control_columns, $valid_columns);

// If no columns selected, default to all columns
if (empty($selected_columns)) {
    $selected_columns = $valid_columns;
}

// Build the SELECT clause
$select_columns = array_merge(array("Date", "Time"), $selected_columns);
$select_clause = implode(", ", array_map(function($col) { return "`$col`"; }, $select_columns));

// Prepare the SQL query with filtering
$query = "SELECT $select_clause FROM `control` WHERE 1=1";

$types = "";
$params = array();

if (!empty($start_date)) {
    $query .= " AND `Date` >= ?";
    $types .= "s";
    $params[] = $start_date;
}

if (!empty($end_date)) {
    $query .= " AND `Date` <= ?";
    $types .= "s";
    $params[] = $end_date;
}

$query .= " ORDER BY `Date` DESC, `Time` DESC LIMIT 100";

// Prepare and execute the statement
$stmt = $conn->prepare($query);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(array("error" => "Failed to prepare statement: " . $conn->error));
    exit();
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$data = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Close connections
$stmt->close();
$conn->close();

// Output JSON data
echo json_encode($data);
?>
