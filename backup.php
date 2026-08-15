<?php
// backup.php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db.php';

if (!$conn || $conn->connect_error) {
    die("Database connection failed");
}

$tables = array();
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
}

$return = "-- EduPro Database SQL Backup\n-- Generated on " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    $result = $conn->query("SELECT * FROM `$table`");
    $numFields = $result ? $result->field_count : 0;
    
    $return .= "DROP TABLE IF EXISTS `$table`;\n";
    $createRow = $conn->query("SHOW CREATE TABLE `$table`")->fetch_row();
    $return .= $createRow[1] . ";\n\n";
    
    if ($result) {
        while ($row = $result->fetch_row()) {
            $return .= "INSERT INTO `$table` VALUES(";
            for ($j = 0; $j < $numFields; $j++) {
                if (isset($row[$j])) {
                    $val = addslashes($row[$j]);
                    $val = str_replace("\n", "\\n", $val);
                    $return .= '"' . $val . '"';
                } else {
                    $return .= 'NULL';
                }
                if ($j < ($numFields - 1)) {
                    $return .= ', ';
                }
            }
            $return .= ");\n";
        }
        $return .= "\n\n";
    }
}

$return .= "SET FOREIGN_KEY_CHECKS=1;\n";

$fileName = 'EduPro_Database_Backup_' . date('Y-m-d') . '.sql';
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($fileName));
echo $return;
exit;
?>