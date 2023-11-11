<?php

// import dataBase class
require_once 'MyDataBase.php';

function main()
{

    $newMtaDb = new MyDataBase('localhost', 'root', '', 'mtta_main');
    $coreDb = new MyDataBase('localhost', 'root', '', 'core');
    $oldMtaDb = new MyDataBase('localhost', 'root', '', 'mta');

    // Get list of tables of newMtaDb and coreDb
    $tablesNewMtaDb = $newMtaDb->getTableList();
    $tablesCoreDb = $coreDb->getTableList();

    // Add tables and columns to oldMtaDb
    $oldMtaDb->addNewTablesAndColumns($tablesNewMtaDb, "mtta_main");
    $oldMtaDb->addNewTablesAndColumns($tablesCoreDb, "core");

    print("Tables and columns added successfully.\n");

    foreach ($tablesCoreDb as $tableName) {
        // Drop all tables that exist in core from oldMtaDb
        $query = "DROP TABLE IF EXISTS $tableName;";
        $oldMtaDb->getConnection()->query($query);

        print("Table $tableName dropped successfully.\n");
    }

    print("All tables dropped successfully.\n");
}

main();
