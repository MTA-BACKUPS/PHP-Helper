<?php

require_once "MyDatabase.php";

$hostName = "localhost";
$username = "root";
$password = "";
$dbname = "mta";

$mtaDb = new MyDataBase($hostName, $username, $password, $dbname);

// Get list of tables in the database
$tables_query = "SHOW TABLES";
$tables_result = $mtaDb->getConnection()->query($tables_query);

if ($tables_result->num_rows > 0) {
    // Iterate through each table

    try {
        while ($row = $tables_result->fetch_assoc()) {
            $table_name = $row["Tables_in_" . $dbname];

            // Check if the table has a primary key
            $primary_key_query = "SHOW KEYS FROM $table_name WHERE Key_name = 'PRIMARY'";
            $primary_key_result = $mtaDb->getConnection()->query($primary_key_query);

            if ($primary_key_result->num_rows == 0) {
                // If no primary key, check if 'id' column exists
                $id_column_query = "SHOW COLUMNS FROM $table_name LIKE 'id'";
                $id_column_result = $mtaDb->getConnection()->query($id_column_query);

                if ($id_column_result->num_rows > 0) {
                    // Set 'id' column as primary key with auto-increment
                    try {
                        $alter_query = "ALTER TABLE $table_name MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY";
                        $mtaDb->getConnection()->query($alter_query);
                    } catch (mysqli_sql_exception $th) {
                        print("Error with table $table_name: " . $th->getMessage() . "\n");
                        continue;
                    }


                    print("Table $table_name updated with 'id' as primary key.\n");
                } else {
                    print("Table $table_name does not have a primary key or 'id' column.\n");
                }
            } else {

                print("Table $table_name already has a primary key.\n");

                // Get the name of the primary key
                $primary_key_row = $primary_key_result->fetch_assoc();
                $primary_key_name = $primary_key_row["Column_name"];

                try {
                    $alter_query = "ALTER TABLE $table_name MODIFY COLUMN `$primary_key_name` INT AUTO_INCREMENT";
                    $mtaDb->getConnection()->query($alter_query);
                    print("Table $table_name updated with auto-increment for $primary_key_name.\n");
                } catch (mysqli_sql_exception $th) {
                    print("Error with table $table_name when trying to add auto-increment: " . $th->getMessage() . "\n");
                    continue;
                }
            }
        }
    } catch (mysqli_sql_exception $th) {
        print("---Unkown error: " . $th->getMessage() . "\n");
    }
} else {
    print("No tables found in the database.\n");
}
