<?php
/**
 * inc_connect.php
 *
 */

$DB_HOST = "localhost";

// Ausgabe der BPOs
$DB_Name = "";
$DB_USER = "";
$DB_PASS = "";


$link = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_Name);
if($link->connect_errno > 0){
    die('Unable to connect to database [' . $link>connect_error . ']');
}

$dbh = new PDO('mysql:host=localhost;dbname=', '', '');

function secondsToTime($seconds) {
    $dtF = new DateTime("@0");
    $dtT = new DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%r%a Tage %H:%I St.');
}

function secondsToHour($seconds) {
    $dtF = new DateTime("@0");
    $dtT = new DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%H');
}

?>
