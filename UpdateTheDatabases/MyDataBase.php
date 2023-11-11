<?php

class MyDataBase
{

    private $dbHost;
    private $dbUser;
    private $dbPass;
    private $dbName;
    private $connection;

    // Constructor
    public function __construct($dbHost, $dbUser, $dbPass, $dbName)
    {
        $this->dbHost = $dbHost;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;
        $this->dbName = $dbName;
        $this->connection = $this->connect($dbHost, $dbUser, $dbPass, $dbName);
    }

    public function getDbHost()
    {
        return $this->dbHost;
    }

    public function setDbHost($dbHost)
    {
        $this->dbHost = $dbHost;
    }

    public function getDbUser()
    {
        return $this->dbUser;
    }

    public function setDbUser($dbUser)
    {
        $this->dbUser = $dbUser;
    }

    public function getDbPass()
    {
        return $this->dbPass;
    }

    public function setDbPass($dbPass)
    {
        $this->dbPass = $dbPass;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    // Connection
    private function connect($dbHost, $dbUser, $dbPass, $dbName)
    {
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        if ($conn->connect_error) {
            die("Connection failed to " . $dbName . ": " . $conn->connect_error);
        }
        return $conn;
    }

    // Get list of tables
    public function getTableList()
    {
        $tableList = array();
        $result = $this->connection->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tableList[] = $row[0];
        }
        return $tableList;
    }

    // Get list of columns
    public function getColumnList($tableName, $sourceDbName = null)
    {
        $columnList = array();
        
        $query = $sourceDbName ? "SHOW COLUMNS FROM $sourceDbName.$tableName" : "SHOW COLUMNS FROM $tableName";
        $result = $this->connection->query($query);

        while ($row = $result->fetch_assoc()) {
            $columnList[] = $row['Field'] . ' ' . $row['Type'];
        }

        return $columnList;
    }

    // Check if table exists
    public function tableExists($tableName)
    {
        $result = $this->connection->query("SHOW TABLES LIKE '$tableName'");
        return $result->num_rows > 0;
    }

    // Create table if it doesn't exist from other database
    public function createTableIfNotExists($tableName, $sourceDbName = null)
    {
        if (!$this->tableExists($tableName)) {

            $createTableQuery = "CREATE TABLE $tableName ";

            if ($sourceDbName) {
                $createTableQuery .= "AS SELECT * FROM $sourceDbName.$tableName WHERE 1=0;";
            }

            $createTableQuery .= ";";

            $this->connection->query($createTableQuery);
        }
    }

    // Add Tables And Columns 
    public function addNewTablesAndColumns($tables, $sourceDbName = null)
    {

        // Loop through all the tables
        foreach ($tables as $tableName) {
            // Check if the table exists 
            if ($this->tableExists($tableName)) {


                // Add the columns that do not exist in the target table from the source table
                $sourceColumns = $this->getColumnList($tableName, $sourceDbName);
                $targetColumns = $this->getColumnList($tableName);

                $missingColumns = array_diff($sourceColumns, $targetColumns);
                print("Missing columns for table $tableName: ");
                print_r($missingColumns);
                foreach ($missingColumns as $column) {

                    // Split the column string into name and type
                    list($columnName, $columnType) = explode(" ", $column);
                
                    $addColumnQuery = "ALTER TABLE $tableName ADD COLUMN $columnName $columnType";
                
                    try {
                        $this->connection->query($addColumnQuery);
                        print("Column $columnName added successfully to table $tableName\n");

                    } catch (mysqli_sql_exception $e) {

                        // Check if the error message contains "Duplicate column name"
                        if (strpos($e->getMessage(), "Duplicate column name") !== false) {

                            // Handle the case where the column already exists
                            print("Column $columnName already exists in table $tableName. Modifying its type...\n");
                
                            // Create a query to modify the column type
                            $modifyColumnQuery = "ALTER TABLE $tableName MODIFY COLUMN $columnName $columnType";
                
                            try {
                                $this->connection->query($modifyColumnQuery);
                                print("Column $columnName type modified successfully in table $tableName\n");
                            } catch (mysqli_sql_exception $modifyException) {

                                // Handle any errors that occur during column type modification
                                print("Error modifying column $columnName type: " . $modifyException->getMessage() . "\n");
                            }

                        } else {
                            // Handle other SQL exceptions that are not related to duplicate columns
                            print("Error adding column $columnName: " . $e->getMessage() . "\n");
                        }
                    }
                }
                
                continue;
            }

            // if the table do not exists 
            // Create the table 
            $this->createTableIfNotExists($tableName, $sourceDbName);
            print("Table $tableName created successfully\n");
        }
    }
}
