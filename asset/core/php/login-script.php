<?php
  include 'connection.php';
  include 'server-side-validation.php';

  if (isset($_POST['userId']) && isset($_POST['userPassword'])) {
    $userId = new form_validation($_POST['userId'], 'str-int', 'User ID', true);
    $userPassword = new form_validation($_POST['userPassword'], 'password', 'Password', true);

    if ($userId -> valid == 1 && $userPassword -> valid == 1) {
      $checkUserIdQry = '
        SELECT * FROM user_acc
        WHERE user_id = "' . $userId -> value . '"
          AND isActive = "1"
      ';
      // die($checkUserIdQry);
      $checkUserIdRes = $connection -> query($checkUserIdQry);

      if ($checkUserIdRes -> num_rows > 0) {
        // User ID is Correct
        $checkUserIdRow = $checkUserIdRes -> fetch_object();

        $checkUserAccQry = '
          SELECT a.fname as fname, a.lname as lname, a.pk_sys_user as userId, c.user_type_desc as user_type
          FROM sys_user as a INNER JOIN user_acc as b ON a.pk_sys_user = b.fk_sys_user
          INNER JOIN user_type as c ON b.FK_user_type = c.PK_user_type
          WHERE b.user_id = "' . $userId -> value . '"
            AND b.pwd = "' . sha1($userPassword -> value . $checkUserIdRow -> pwd_salt) . '"
        ';
        // die($checkUserAccQry);
        $checkUserAccRes = $connection -> query($checkUserAccQry);

        if ($checkUserAccRes -> num_rows > 0) {
          // Login Credentials are Correct
          $checkUserAccRow = $checkUserAccRes -> fetch_assoc();

          // Initialize Session Variables
          $_SESSION['userId']    = $checkUserAccRow['userId'];
          $_SESSION['firstName'] = $checkUserAccRow['fname'];
          $_SESSION['lastName']  = $checkUserAccRow['lname'];
          $_SESSION['userType']  = $checkUserAccRow['user_type'];
        } else {
          // Password is Invalid
          $userPassword -> valid = 0;
          $userPassword -> err_msg = 'Password is Incorrect';

          // User ID Field Value
          $_SESSION['userIdField'] = $userId -> value;
        }
      } else {
        // User ID is Invalid
        $userId -> valid = 0;
        $userId -> err_msg = 'flag';
        $userPassword -> valid = 0;
        $userPassword -> err_msg = 'Invalid Login Credentials';
      }
    }

    if ($userId -> valid == 1 && $userPassword -> valid == 1) {
      // Redirect to Landing Page
      header('Location: ../../../html/index.php');
    } else {
      if ($userId -> valid == 0) {
        $_SESSION['userIdErr'] = $userId -> err_msg;
      }

      if ($userPassword -> valid == 0) {
        $_SESSION['userPasswordErr'] = $userPassword -> err_msg;
      }

      // Redirect to Login Page
      header('Location: ../../../index.php');
    }
  } else {
    // Incomplete/No Data Submitted
    header('Location: ../../../index.php');
  }
?>