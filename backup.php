<?php
error_reporting(0);
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "eduprom_db"; // Make sure this matches your exact database name

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables = array();
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

$return = "";
foreach ($tables as $table) {
    $result = $conn->query("SELECT * FROM $table");
    $numFields = $result->field_count;
    
    $return .= "DROP TABLE IF EXISTS $table;";
    $row2 = $conn->query("SHOW CREATE TABLE $table")->fetch_row();
    $return .= "\n\n" . $row2[1] . ";\n\n";
    
    for ($i = 0; $i < $numFields; $i++) {
        while ($row = $result->fetch_row()) {
            $return .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $numFields; $j++) {
                $row[$j] = addslashes($row[$j]);
                $row[$j] = str_replace("\n", "\\n", $row[$j]);
                if (isset($row[$j])) {
                    $return .= '"' . $row[$j] . '"';
                } else {
                    $return .= '""';
                }
                if ($j < ($numFields - 1)) {
                    $return .= ',';
                }
            }
            $return .= ");\n";
        }
    }
    $return .= "\n\n\n";
}

$fileName = 'EduPro_Database_Backup_' . date('Y-m-d') . '.sql';
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($fileName));
echo $return;
exit;
?>