<?php
    $host = "localhost";
    $database = "db_garcia";
    $user = "root";
    $password = "";
    $dsn = "mysql:host={$host};dbname={$database};";

    try
    {
        $conn = new PDO($dsn, $user, $password);
        // if ($con) echo "Successfully connected to database.";
    }
    catch (PDOException $th)
    {
        echo $th->getMessage();
    }
?>