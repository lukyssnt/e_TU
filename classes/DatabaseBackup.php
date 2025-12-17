<?php
require_once __DIR__ . '/../config/database.php';

class DatabaseBackup
{
    private $db;
    private $dbname;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        // Get database name
        $stmt = $this->db->query("SELECT DATABASE()");
        $this->dbname = $stmt->fetchColumn();
    }

    public function generateBackup()
    {
        $tables = [];
        $stmt = $this->db->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sqlScript = "-- E-ADMIN TU DATABASE BACKUP\n";
        $sqlScript .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sqlScript .= "-- Database: " . $this->dbname . "\n\n";
        $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Structure
            $sqlScript .= "-- Table structure for table `$table`\n";
            $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
            $row = $this->db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
            $sqlScript .= $row[1] . ";\n\n";

            // Data
            $sqlScript .= "-- Dumping data for table `$table`\n";
            $stmt = $this->db->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                $sqlScript .= "INSERT INTO `$table` VALUES\n";
                $valuesArr = [];
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $valuesArr[] = "(" . implode(", ", $values) . ")";
                }
                $sqlScript .= implode(",\n", $valuesArr) . ";\n";
            }
            $sqlScript .= "\n";
        }

        $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sqlScript;
    }
}
?>