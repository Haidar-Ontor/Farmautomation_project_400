<?php
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

// Get filter parameters from POST request
$columns = isset($_POST['columns']) ? $_POST['columns'] : [];
$columns = array_map('mysqli_real_escape_string', $columns); // Sanitize input

// Default columns to select if none are provided
$defaultColumns = ["Date", "Time"];
$columns = array_merge($defaultColumns, $columns);

$columnString = implode(",", $columns);

// Query to fetch data based on selected columns
$sql = "SELECT $columnString FROM `Sensor reading` ORDER BY `Date` DESC, `Time` DESC LIMIT 100";
$result = $conn->query($sql);

$data = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $entry = array();
        foreach ($columns as $col) {
            $entry[$col] = $row[$col];
        }
        $data[] = $entry;
    }
} else {
    $data = array(); // Empty array if no rows found
}

$conn->close();

// Set response headers to JSON
header('Content-Type: application/json');

// Output JSON data
echo json_encode($data);
?>
