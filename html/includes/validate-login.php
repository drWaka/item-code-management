<?php
  // Script that will validate the current login session of the user 
  if (isset($_SESSION['userId'])) {
    $currentFile = basename($_SERVER['PHP_SELF']);
    if ($currentFile != 'index.php') {
      $checkUserAccessQry = "
        SELECT a.PK_sys_user
        FROM sys_user AS a 
        INNER JOIN sys_user_modules AS b ON a.PK_sys_user = b.FK_sys_user
        INNER JOIN sys_modules AS c ON b.FK_sys_module = c.PK_sys_module
        WHERE a.PK_sys_user = '{$_SESSION['userId']}'
          AND c.FK_sys_system = '1'
          AND b.isValid = 1
          AND c.addr_link = '{$currentFile}'
        
        UNION ALL

        SELECT a.PK_sys_user
        FROM sys_user AS a 
        INNER JOIN sys_user_modules AS b ON a.PK_sys_user = b.FK_sys_user
        INNER JOIN sys_modules AS c ON b.FK_sys_module = c.PK_sys_module
        INNER JOIN sys_sub_modules AS d ON c.PK_sys_module = d.FK_sys_module
        INNER JOIN sys_user_sub_modules AS e 
          ON (d.PK_sys_sub_module = e.FK_sys_sub_module AND e.FK_sys_user = a.PK_sys_user)
        WHERE a.PK_sys_user = '{$_SESSION['userId']}'
          AND c.FK_sys_system = '1'
          AND b.isValid = 1
          AND e.isValid = 1
          AND d.addr_link = '{$currentFile}'
      ";
      // die($checkUserAccessQry);
      $checkUserAccessRes = $connection -> query($checkUserAccessQry);

      if (!($checkUserAccessRes -> num_rows > 0)) {
        header('Location: index.php');
        die();
      }
    }
  } else {
    header('Location: ../index.php');
    die();
  }
?>