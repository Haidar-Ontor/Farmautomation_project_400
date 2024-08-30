<?php
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "farmauto_farmautomation400";
$password = "RTX4090ti@";
$dbname = "farmauto_farmautomation400";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('SELECT id, sensor_name, min_value, max_value FROM sensor_trigger_values');
    $sensors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($sensors);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
