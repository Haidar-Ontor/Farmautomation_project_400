<?php
// Database connection details
$servername = "localhost";
$username = "farmauto_farmautomation400";
$password = "RTX4090ti@";
$dbname = "farmauto_farmautomation400";

// Set the default time zone to GMT+6
// date_default_timezone_set('Asia/Dhaka');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the JSON payload from the request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch the current status of all devices
    $sql = "SELECT Humidifier, Pump_G, Light_G, Fan_G, Door_G, Heater_G, G_mode, Pump_f1, Pump_f2, Pump_f3, Pump_Pond, FP_mode, C_mode, C_Light, C_Fan, C_Pump, C_Gate
            FROM control 
            ORDER BY Date DESC, Time DESC 
            LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "Humidifier" => $row["Humidifier"],
            "Pump_G" => $row["Pump_G"],
            "Light_G" => $row["Light_G"],
            "Fan_G" => $row["Fan_G"],
            "Door_G" => $row["Door_G"],
            "Heater_G" => $row["Heater_G"],
            "G_mode" => $row["G_mode"],
            "Pump_f1" => $row["Pump_f1"],
            "Pump_f2" => $row["Pump_f2"],
            "Pump_f3" => $row["Pump_f3"],
            "Pump_Pond" => $row["Pump_Pond"],
            "FP_mode" => $row["FP_mode"],
            "C_mode" => $row["C_mode"],
            "C_Light" => $row["C_Light"],
            "C_Fan" => $row["C_Fan"],
            "C_Pump" => $row["C_Pump"],
            "C_Gate" => $row["C_Gate"]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No data found"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the data contains the required fields
    if (isset($data['device']) && isset($data['action'])) {
        $device = $data['device'];
        $action = $data['action'];

        // Fetch the current status of the last row
        $sql = "SELECT * FROM control ORDER BY Date DESC, Time DESC LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Determine the new status based on the current status and action
            if (in_array($device, ['G_mode', 'FP_mode', 'C_mode'])) {
                $newStatus = ($row[$device] == 1) ? 0 : 1; // Toggle mode
            } else {
                $currentStatus = $row[$device];
                $newStatus = ($currentStatus == 1) ? 0 : 1; // Toggle device state
            }

            // Get the current date and time in GMT+6
            $currentDate = date('Y-m-d');
            $currentTime = date('H:i:s');

            // Insert a new row with the updated status
            $insert_sql = "INSERT INTO control (Humidifier, Pump_G, Light_G, Fan_G, Door_G, Heater_G, G_mode, Pump_f1, Pump_f2, Pump_f3, Pump_Pond, FP_mode, C_mode, C_Light, C_Fan, C_Pump, C_Gate, Date, Time) 
                           VALUES (
                               ".($device == 'Humidifier' ? $newStatus : $row['Humidifier']).",
                               ".($device == 'Pump_G' ? $newStatus : $row['Pump_G']).",
                               ".($device == 'Light_G' ? $newStatus : $row['Light_G']).",
                               ".($device == 'Fan_G' ? $newStatus : $row['Fan_G']).",
                               ".($device == 'Door_G' ? $newStatus : $row['Door_G']).",
                               ".($device == 'Heater_G' ? $newStatus : $row['Heater_G']).",
                               ".($device == 'G_mode' ? $newStatus : $row['G_mode']).",
                               ".($device == 'Pump_f1' ? $newStatus : $row['Pump_f1']).",
                               ".($device == 'Pump_f2' ? $newStatus : $row['Pump_f2']).",
                               ".($device == 'Pump_f3' ? $newStatus : $row['Pump_f3']).",
                               ".($device == 'Pump_Pond' ? $newStatus : $row['Pump_Pond']).",
                               ".($device == 'FP_mode' ? $newStatus : $row['FP_mode']).",
                               ".($device == 'C_mode' ? $newStatus : $row['C_mode']).",
                               ".($device == 'C_Light' ? $newStatus : $row['C_Light']).",
                               ".($device == 'C_Fan' ? $newStatus : $row['C_Fan']).",
                               ".($device == 'C_Pump' ? $newStatus : $row['C_Pump']).",
                               ".($device == 'C_Gate' ? $newStatus : $row['C_Gate']).",
                               '$currentDate', '$currentTime')";

            if ($conn->query($insert_sql) === TRUE) {
                echo json_encode(["success" => true, "message" => "Command executed successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Device not found"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
    }
}

$conn->close();
?>
