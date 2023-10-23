<?php

/**
 * This script will output a CSV containing the keyfob IDs and names associated with all active memberships
 * 
 * Our Raspberry Pi's within the space currently poll this script every few minutes. We wish to move this logic within
 * Laravel later, but we're saving that work until we can develop a more sensible API and can update the space's access
 * system (either the existing Pi's, or something else that's easier to manage)
 */

// Enable access to Composer dependencies (we'll indirectly have phpdotenv through our Laravel)
require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();
$dotenv->required([
    'QUERY2_ACCESS_KEY',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD'
]);

if (!isset($_GET['key']) || $_GET['key'] !== $_ENV['QUERY2_ACCESS_KEY']) die();

//Our MySQL connection details.
$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$user = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$database = $_ENV['DB_DATABASE'];

//Connect to MySQL using PDO.
$pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $user, $password);

//Create our SQL query.
$sql = <<<SQL
SELECT key_fobs.key_id,
    users.announce_name
FROM key_fobs
    LEFT JOIN users ON users.id = key_fobs.user_id
WHERE (
        users.status = 'active'
        OR users.status = 'leaving'
    )
    AND key_fobs.Active = '1'
SQL;

//Prepare our SQL query.
$statement = $pdo->prepare($sql);

//Executre our SQL query.
$statement->execute();

//Fetch all of the rows from our MySQL table.
$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

//Get the column names.
$columnNames = array();
if (!empty($rows)) {
    //We only need to loop through the first row of our result
    //in order to collate the column names.
    $firstRow = $rows[0];
    foreach ($firstRow as $colName => $val) {
        $columnNames[] = $colName;
    }
}

//Setup the filename that our CSV will have when it is downloaded.
$fileName = 'mysql-export.csv';

//Set the Content-Type and Content-Disposition headers to force the download.
header('Content-Type: application/excel');
header('Content-Disposition: attachment; filename="' . $fileName . '"');

//Open up a file pointer
$fp = fopen('php://output', 'w');

//Start off by writing the column names to the file.
fputcsv($fp, $columnNames);

//Then, loop through the rows and write them to the CSV file.
foreach ($rows as $row) {
    fputcsv($fp, $row);
}

//Close the file pointer.
fclose($fp);
