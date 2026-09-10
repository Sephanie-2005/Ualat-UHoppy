<?php

    $host     = 'localhost';
    $db_name  = 'uhoppy_db'; 
    $username = 'root';     
    $password = '';         

    try {
        $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
            PDO::ATTR_EMULATE_PREPARES   => false,                  
        ]);
    } 
    catch (PDOException $e) {
        die("PDO Database connection failed: " . $e->getMessage());
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn = new mysqli($host, $username, $password, $db_name);


    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        die("MySQLi Database connection failed: " . $conn->connect_error);
    }

?>
