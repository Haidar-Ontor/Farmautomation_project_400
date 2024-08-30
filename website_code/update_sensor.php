<?php
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "farmauto_farmautomation400";
$password = "RTX4090ti@";
$dbname = "farmauto_farmautomation400";

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON received']);
    exit;
}

$id = $data['id'];
$minValue = $data['minValue'];
$maxValue = $data['maxValue'];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('UPDATE sensor_trigger_values SET min_value = ?, max_value = ? WHERE id = ?');
    $stmt->execute([$minValue, $maxValue, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Values updated successfully']);
    } else {
        echo json_encode(['message' => 'No changes made or invalid sensor ID']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
