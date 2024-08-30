<?php
// Database connection details
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request: retrieve the latest sensor data
    $sql = "SELECT * FROM `Sensor reading` ORDER BY `Date` DESC, `Time` DESC LIMIT 1";
    $result = $conn->query($sql);

    $data = array();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $data = array(
            "date" => $row["Date"],
            "time" => $row["Time"],
            "temperature" => $row["G_Temp"],
            "light" => $row["G_Light"],
            "humidity" => $row["G_Humidity"],
            "soilMoisture" => $row["G_Moisture"],
            "C_Humidity"=> $row["C_Humidity"],
            "C_Temp"=> $row["C_Temp"],
            "C_Light"=> $row["C_Light"],
            "C_WaterL"=> $row["C_WaterL"],
            "F1_SoilM"=> $row["F1_SoilM"],
            "F2_SoilM"=> $row["F2_SoilM"],
            "F3_SoilM"=> $row["F3_SoilM"],
          

            
            // add column name in DB to add sensor ""=> $row[""],
        );
    } else {
        $data = array(
            "date" => "N/A",
            "time" => "N/A",
            "temperature" => "N/A",
            "light" => "N/A",
            "humidity" => "N/A",
            "soilMoisture" => "N/A",
            
            "C_Light" => "N/A",
            "C_Humidity" => "N/A",
            "C_Temp" => "N/A",
            "C_WaterL" => "N/A",

                        "F1_SoilM" => "N/A",
                        "F2_SoilM" => "N/A",
                        "F3_SoilM" => "N/A"

            
        );
    }

    echo json_encode($data);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST request: update the database with new sensor data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Check if the data contains the required fields
    $requiredFields = ['temperature', 'humidity', 'soilMoisture', 'light'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            echo json_encode(["success" => false, "message" => "Invalid input"]);
            $conn->close();
            exit();
        }
    }

    $temperature = $conn->real_escape_string($data['temperature']);
    $humidity = $conn->real_escape_string($data['humidity']);
    $soilMoisture = $conn->real_escape_string($data['soilMoisture']);
    $light = $conn->real_escape_string($data['light']);
  

    // Insert the sensor data into the database
    $sql = "INSERT INTO `Sensor reading` (`Date`, `Time`, `G_Temp`, `G_Light`, `G_Humidity`, `G_Moisture`,`C_Temp`, `C_Humidity`, `C_Light`, `C_WaterL`, `F1_SoilM`, `F2_SoilM`, `F3_SoilM` )
            VALUES (CURDATE(), localtime(), '$temperature', '$light', '$humidity', '$soilMoisture')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "message" => "Data inserted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
    }
}

$conn->close();
?>
