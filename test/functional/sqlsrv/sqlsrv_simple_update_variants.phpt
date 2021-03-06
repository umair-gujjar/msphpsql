--TEST--
Test simple insert and update sql_variants using parameters of some different data types
--DESCRIPTION--
ORDER BY should work with sql_variants
--FILE--
﻿<?php
require_once('MsCommon.inc');

class Country
{
    public $id;
    public $country;
    public $continent;

    public function getCountry()
    {
        return $this->country;
    }
    public function getContinent()
    {
        return $this->continent;
    }
}

function CreateVariantTable($conn, $tableName)
{
    // create a table for testing
    $dataType = "[id] sql_variant, [country] sql_variant, [continent] sql_variant";
    createTableEx($conn, $tableName, $dataType);
}

function AddCountry($conn, $tableName, $id, $country, $continent)
{
    $query = "INSERT $tableName ([id], [country], [continent]) VALUES (?, ?, ?)";

    // set parameters
    $params = array($id, $country, $continent);
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt) {
        echo "\nAdded $country in $continent with ID $id.";
    } else {
        fatalError("Failed to insert country $country.\n");
    }
}

function UpdateID($conn, $tableName, $id, $country, $continent)
{
    $query = "UPDATE $tableName SET id = ? WHERE country = ? AND continent = ?";
    $param1 = $id;
    $param2 = $country;
    $param3 = $continent;
    $params = array( &$param1, &$param2, &$param3);

    if ($stmt = sqlsrv_prepare($conn, $query, $params)) {
        if (sqlsrv_execute($stmt)) {
            echo "\nCountry $country now updated with new id $id.";
        }

        sqlsrv_free_stmt($stmt);
    } else {
        fatalError("Failed to update ID.\n");
    }
}

function UpdateCountry($conn, $tableName, $id, $country, $continent)
{
    $query = "UPDATE $tableName SET country = ? WHERE id = ? AND continent = ?";
    $param1 = $country;
    $param2 = $id;
    $param3 = $continent;
    $params = array( &$param1, &$param2, &$param3);

    if ($stmt = sqlsrv_prepare($conn, $query, $params)) {
        if (sqlsrv_execute($stmt)) {
            echo "\nThe country in $continent is now $country.";
        }

        sqlsrv_free_stmt($stmt);
    } else {
        fatalError("Failed to update country.\n");
    }
}

function Fetch($conn, $tableName)
{
    $select = "SELECT * FROM $tableName ORDER BY id";
    $stmt = sqlsrv_query($conn, $select);

    while ($country = sqlsrv_fetch_object($stmt, "Country")) {
        echo "\nID: " . $country->id . " ";
        echo $country->getCountry() . ", ";
        echo $country->getContinent();
    }

    sqlsrv_free_stmt($stmt);
}

//--------------------------------------------------------------------
// RunTest
//
//--------------------------------------------------------------------
function RunTest()
{
    startTest("sqlsrv_simple_update_variants");
    try {
        setup();

        // connect
        $conn = connect();

        // Create a temp table that will be automatically dropped once the connection is closed
        $tableName = GetTempTableName();
        CreateVariantTable($conn, $tableName);

        // Add three countries
        AddCountry($conn, $tableName, 1, 'Canada', 'North America');
        AddCountry($conn, $tableName, 3, 'France', 'Europe');
        AddCountry($conn, $tableName, 5, 'Australia', 'Australia');

        // Read data
        Fetch($conn, $tableName);

        // Update id
        UpdateID($conn, $tableName, 4, 'Canada', 'North America');

        // Read data
        Fetch($conn, $tableName);

        // Update country
        UpdateCountry($conn, $tableName, 4, 'Mexico', 'North America');

        // Read data
        Fetch($conn, $tableName);

        // Add two more countries
        AddCountry($conn, $tableName, 6, 'Brazil', 'South America');
        AddCountry($conn, $tableName, 2, 'Egypt', 'Africa');

        // Read data
        Fetch($conn, $tableName);

        sqlsrv_close($conn);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    echo "\nDone\n";
    endTest("sqlsrv_simple_update_variants");
}

RunTest();

?>
--EXPECT--
﻿
Added Canada in North America with ID 1.
Added France in Europe with ID 3.
Added Australia in Australia with ID 5.
ID: 1 Canada, North America
ID: 3 France, Europe
ID: 5 Australia, Australia
Country Canada now updated with new id 4.
ID: 3 France, Europe
ID: 4 Canada, North America
ID: 5 Australia, Australia
The country in North America is now Mexico.
ID: 3 France, Europe
ID: 4 Mexico, North America
ID: 5 Australia, Australia
Added Brazil in South America with ID 6.
Added Egypt in Africa with ID 2.
ID: 2 Egypt, Africa
ID: 3 France, Europe
ID: 4 Mexico, North America
ID: 5 Australia, Australia
ID: 6 Brazil, South America
Done
Test "sqlsrv_simple_update_variants" completed successfully.
