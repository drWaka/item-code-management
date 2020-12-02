<?php 
  require '../php/connection.php';
  require '../php/server-side-validation.php';
  require '../php/error-handler.php';
  require '../php/generic-functions.php';

  // JSON Variables
  $success = 'success';
  $content = array(
    "modal" => ''
  );

  if (isset($_POST['newPwd']) && isset($_POST['reNewPwd']) && isset($_POST['userId'])) {
    $newPwd = new form_validation($_POST['newPwd'], 'str-int', 'Password', true);
    $reNewPwd = new form_validation($_POST['reNewPwd'], 'str-int', 'Password', true);
    $userId = new form_validation($_POST['userId'], 'str-int', 'User ID', true);

    if ($newPwd -> valid == 1 && $reNewPwd -> valid == 1 && $userId -> valid == 1) {
      $empAccRecQry = 'SELECT * FROM user_acc WHERE user_id = "' . $userId -> value . '"';
      $empAccRecRes = $connection -> query($empAccRecQry);

      if (!($empAccRecRes -> num_rows > 0)) {
        $userId -> valid = 0;
        $userId -> err_msg = 'Employee Account doesn\'t exists';
      }
    }

    if ($newPwd -> valid == 1 && $reNewPwd -> valid == 1 && $userId -> valid == 1) {
      if (strlen($newPwd -> value) < 8) {
        $newPwd -> valid = 0;
        $newPwd -> err_msg = 'Password must be atleast 8 characters';
      } else {
        for ($index = 0 ; $index <= strlen($newPwd -> value) ; $index++) {
          if (filter_var(substr($newPwd -> value, $index, 1), FILTER_VALIDATE_INT)) {
            break;
          }
          if ($index == strlen($newPwd -> value)) {
            if (!(filter_var(substr($newPwd -> value, $index, 1), FILTER_VALIDATE_INT))) {
              $newPwd -> valid = 0;
              $newPwd -> err_msg = 'New Password must contain atleast 1 number';
            }
          }
        }
      }
    }

    if ($newPwd -> valid == 1 && $reNewPwd -> valid == 1 && $userId -> valid == 1) {
      if ($newPwd -> value != $reNewPwd -> value) {
        $newPwd -> valid = 0;
        $newPwd -> err_msg = 'flag';
        $reNewPwd -> valid = 0;
        $reNewPwd -> err_msg = 'Password doesn\'t match';
      }
    }

    if ($newPwd -> valid == 1 && $reNewPwd -> valid == 1 && $userId -> valid == 1) {
      $empAccQry = 'SELECT * FROM user_acc WHERE user_id = "' . $userId -> value . '"';
      $empAccRes = $connection -> query($empAccQry);
      $empAccRow = $empAccRes -> fetch_object();

      $updatePwdQry = '
        UPDATE user_acc
          SET pwd = "' . sha1($newPwd -> value . $empAccRow -> pwd_salt) . '"
        WHERE user_id = "' . $userId -> value  . '"
      ';
      if ($connection -> query($updatePwdQry)) {
        $content['modal'] = modalize(
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize">Account Recovery Success</h2>
              <p class="para-text">Account Password Updated Successfully</p>
            </div>        
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Close'
          )
        );
      } else {
        $content['modal'] = modalize(
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize">Internal Error Encountered</h2>
              <p class="para-text">Error Details: Unable to Update Employee Account</p>
            </div>        
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      }
    } else {
      if ($userId -> valid == 0) {
        $content['modal'] = modalize(
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize">Internal Error Encountered</h2>
              <p class="para-text">Error Details: ' . $userId -> err_msg . '</p>
            </div>        
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else {
        // Form Error Management
        $newPwdErr = new error_handler($newPwd -> err_msg);
        $reNewPwdErr = new error_handler($reNewPwd -> err_msg);

        $content['modal'] = modalize(
          '<div class="row">
            <div class="col-sm-12">
              <h2 class="header capitalize text-center">Employee Account Recovery</h2>
              <p class="para-text text-center">Please enter your new password to continue.</p>
            </div>
            
            <div class="col-sm-12 account-recovery-3">
              <form form-name="account-recovery-3" action="asset/core/ajax/login-reset-password-update.php" method="post" class="form-material">
                <input type="text" name="userId" value="' . $userId -> value . '" hidden/>
              
                <label for="" class="text-left control-label col-xs-12">New Password : </label>
                <div class="form-group '. $newPwdErr -> error_class .'">
                  <input type="password" class="form-control capitalize" name="newPwd" placeholder="Enter Password">
                  ' . $newPwdErr -> error_icon . '
                  ' . $newPwdErr -> error_text . '
                </div>

                <label for="" class="text-left control-label col-xs-12">Re-Type Password : </label>
                <div class="form-group '. $reNewPwdErr -> error_class .'">
                  <input type="password" class="form-control capitalize" name="reNewPwd" placeholder="Re-Enter Password">
                  '. $reNewPwdErr -> error_icon .'
                  '. $reNewPwdErr -> error_text .'
                </div>

              </form>
            </div>
          </div>', 
          array(
            "trasnType" => 'regular',
            "btnLbl" => 'Update'
          )
        );
      }
    }
  } else {
    $content['modal'] = modalize(
      '<div class="row text-center">
        <div class="col-sm-12">
          <h2 class="header capitalize">Error Encountered</h2>
          <p class="para-text">Error Details: Insufficient Data Submitted</p>
        </div>        
      </div>', 
      array(
        "trasnType" => 'error',
        "btnLbl" => 'Dismiss'
      )
    );
  }


  // Return JSON encode
  encode_json_file(array($success, $content));
?>