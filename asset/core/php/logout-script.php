<?php 
  // Start the Session
  session_start();

  // Remove all of the Session Variables
  session_unset();
  
  // Stop the Session
  session_destroy();

  // Go to the website homepage
  header('Location: ../../../index.php');
?>