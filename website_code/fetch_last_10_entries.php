<?php
// get_sensor_data.php

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
$sensor_columns = isset($_GET['sensor_columns']) ? $_GET['sensor_columns'] : array();

// Ensure sensor_columns is an array
if (!is_array($sensor_columns)) {
    $sensor_columns = array();
}

// Define valid sensor columns to prevent SQL injection
$valid_columns = array(
    "G_Temp", "G_Light", "G_Humidity", "G_Moisture",
    "C_Temp", "C_Humidity", "C_Light", "C_WaterL",
    "F1_SoilM", "F2_SoilM", "F3_SoilM","Pond_Water_Level"
);

// Filter sensor_columns to include only valid columns
$selected_columns = array_intersect($sensor_columns, $valid_columns);

// If no sensors selected, default to all sensors
if (empty($selected_columns)) {
    $selected_columns = $valid_columns;
}

// Build the SELECT clause
$select_columns = array_merge(array("Date", "Time"), $selected_columns);
$select_clause = implode(", ", array_map(function($col) { return "`$col`"; }, $select_columns));

// Prepare the SQL query with filtering
$query = "SELECT $select_clause FROM `Sensor reading` WHERE 1=1";

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
