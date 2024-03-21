<?php

// Database connection parameters
$db_host = "sdb-68.hosting.stackcp.net";
$db_username = "example-353034399002";
$db_password = "p7vwx43bt9";
$db_name = "example-353034399002";

// Create MySQLi connection
$db = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}