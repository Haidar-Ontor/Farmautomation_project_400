<?php
$servername = "localhost";
$username = "farmauto_farmautomation400";
$password = "RTX4090ti@";
$dbname = "farmauto_farmautomation400";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the latest control statuses
$control_sql = "SELECT * FROM control ORDER BY Date DESC, Time DESC LIMIT 1";
$control_result = $conn->query($control_sql);

// Default control statuses in case no data is available
$control_data = array(
    "Date" => "N/A",
    "Time" => "N/A",
    "Humidifier" => "N/A",
    "WaterPump" => "N/A",
    "Light" => "N/A",
    "Fan" => "N/A",
    "Door" => "N/A",
    "Heater" => "N/A",
    "G_mode"=>"N/A",
     "Pump_f1" => "N/A",
    "Pump_f2" => "N/A",
    "Pump_f3" => "N/A",
    "Pump_pond" => "N/A",
    "FP_mode"=>"N/A",
    "C_mode"=>"N/A",
    "C_Light"=>"N/A",
    "C_Fan"=>"N/A",
    "C_Pump"=>"N/A",
    "C_Gate"=>"N/A",
    
);

if ($control_result->num_rows > 0) {
    $control_row = $control_result->fetch_assoc();
    $control_data = array(
        "Date" => $control_row["Date"],
        "Time" => $control_row["Time"],
        "Humidifier" => $control_row["Humidifier"] ? "on" : "off",
        "WaterPump" => $control_row["Pump_G"] ? "on" : "off",
        "Light" => $control_row["Light_G"] ? "on" : "off",
        "Fan" => $control_row["Fan_G"] ? "on" : "off",
        "Door" => $control_row["Door_G"] ? "on" : "off",
        "Heater" => $control_row["Heater_G"] ? "on" : "off",
        "G_mode"=>$control_row["G_mode"] ? "on" : "off",
        "Pump_f1" => $control_row["Pump_f1"] ? "on" : "off",
        "Pump_f2" => $control_row["Pump_f2"] ? "on" : "off",
        "Pump_f3" => $control_row["Pump_f3"] ? "on" : "off",
        "Pump_Pond" => $control_row["Pump_Pond"] ? "on" : "off",
        "FP_mode"=> $control_row["FP_mode"] ? "on": "off",
        "C_mode"=> $control_row["C_mode"] ? "on": "off",
        "C_Light"=> $control_row["C_Light"] ? "on": "off",
        "C_Fan"=> $control_row["C_Fan"] ? "on": "off",
        "C_Pump"=> $control_row["C_Pump"] ? "on": "off",
        "C_Gate"=> $control_row["C_Gate"] ? "on": "off",
    );
}

$conn->close();

// Output JSON response
echo json_encode($control_data);
?>
