<?php
// Database connection settings
$host = 'dpg-d1hv2ejuibrs7380k6gg-a.oregon-postgres.render.com';
$db = 'web_project_ll18';
$user = 'ali';
$pass = 'P42L1AGw5lAPIsJI3ryuk7r2uMSChrST';

// Create connection string
$conn_string = "host=$host dbname=$db user=$user password=$pass";

// Connect to PostgreSQL
$conn = pg_connect($conn_string);

if (!$conn) {
    die("Connection failed.");
}

// SQL to alter table and add image_url column
$sql = "ALTER TABLE courses ADD COLUMN image_url VARCHAR(255);";

$result = pg_query($conn, $sql);

if ($result) {
    echo "Column image_url added successfully.";
} else {
    echo "Error altering table: " . pg_last_error($conn);
}

pg_close($conn);
?>