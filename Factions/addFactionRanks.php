<?php

require_once '../UpdateTheDatabases/MyDataBase.php';

function main()
{
    $mtaDb = new MyDataBase('localhost', 'root', '', 'mta');


    $query = "SELECT id FROM factions";
    $result = $mtaDb->getConnection()->query($query);

    while ($row = $result->fetch_assoc()) {
        $factionId = $row['id'];
        $queryONe ="INSERT INTO `faction_ranks` (`faction_id`, `name`, `permissions`, `isDefault`, `isLeader`, `wage`) VALUES ( $factionId, 'Leader Rank', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18', '0', '1', '0')";
        $queryTWO ="INSERT INTO `faction_ranks` (`faction_id`, `name`, `permissions`, `isDefault`, `isLeader`, `wage`) VALUES ( $factionId, 'Default Rank', '', '1', '0', '0')";

        $mtaDb->getConnection()->query($queryONe);
        $mtaDb->getConnection()->query($queryTWO);

        print("Added ranks to faction $factionId\n");
    }
}

main();
