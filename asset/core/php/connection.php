<?php 
  // Start Session
  session_start();


  // Initialize Database Connection
  
  // MySQL Database Connection
  $connection = new mysqli(
    "localhost",
    "root",
    "",
    "itcoms_db"
  );


  // MS SQL Database Connection
  $serverName = "192.168.2.243";
  $database = "LiveDB_OLLH";
  $uid = 'sa';
  $pwd = 'OLLH@Manil@70';
  try {
    $mssqlConn = new PDO(
      "sqlsrv:server=$serverName;Database=$database",
      $uid,
      $pwd,
      array(
        //PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      )
    );
  } catch (PDOException $e) {
    die("Error connecting to SQL Server: " . $e -> getMessage());
  }
?>