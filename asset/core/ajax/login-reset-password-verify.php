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

  if (isset($_POST['recoveryCode']) && isset($_POST['userId'])) {
    $recoveryCode = new form_validation($_POST['recoveryCode'], 'str-int', 'Recovery Code', true);
    $userId = new form_validation($_POST['userId'], 'str-int', 'User ID', true);
    
    if ($recoveryCode -> valid == 1 && $userId -> valid == 1) {
      $userAccQry = '
        SELECT a.fname as fname, a.lname as lname, a.email as email
        FROM sys_user as a INNER JOIN user_acc as b ON a.pk_sys_user = b.fk_sys_user
        WHERE b.user_id = "' . $userId -> value . '"
      ';
      // die($userAccQry);
      $userAccRes = $connection -> query($userAccQry);

      if (!($userAccRes -> num_rows > 0)) {
        $userId -> valid = 0;
        $userId -> err_msg = 'User ID doesn\'t exists';
      }
    }

    if ($recoveryCode -> valid == 1 && $userId -> valid == 1) {
      if ($recoveryCode -> value == $_SESSION['accRecoverKey']) {
        unset($_SESSION['accRecoverKey']);
      } else {
        $recoveryCode -> valid = 0;
        $recoveryCode -> err_msg = 'Incorrect Recovery Key';
      }
    }

    if ($recoveryCode -> valid == 1 && $userId -> valid == 1) {
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
              <div class="form-group">
                <input type="password" class="form-control capitalize" name="newPwd" placeholder="Enter Password">
              </div>

              <label for="" class="text-left control-label col-xs-12">Re-Type Password : </label>
              <div class="form-group">
                <input type="password" class="form-control capitalize" name="reNewPwd" placeholder="Re-Enter Password">
              </div>

            </form>
          </div>
        </div>', 
        array(
          "trasnType" => 'regular',
          "btnLbl" => 'Update'
        )
      );
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
        // User Email Selection
        $userRecQry = '
          SELECT * FROM sys_user WHERE pk_sys_user = (
            SELECT fk_sys_user FROM user_acc WHERE user_id = "' . $userId -> value . '"
          )
        ';
        $userRecRes = $connection -> query($userRecQry);
        $userRecRow = $userRecRes -> fetch_object();

        $email_raw = explode('@', $userRecRow -> email);
        $email_hashed = substr($email_raw[0], 0, 1);
        for ($index = (strlen($email_raw[0]) - 2) ; $index > 0 ; $index--) {
          $email_hashed .= '*';
        }
        $email_hashed .= substr($email_raw[0], strlen($email_raw[0]) - 1, 1) . '@' . $email_raw[1];

        // Form Error Management
        $recoveryCodeErr = new error_handler($recoveryCode -> err_msg);

        $content['modal'] = modalize(
          '<div class="row">
            <div class="col-sm-12">
              <h2 class="header capitalize text-center">Employee Account Recovery</h2>
              <p class="para-text text-center">Please enter the account recovery code sent to <b>' . $email_hashed . '</b>.</p>
            </div>
            
            <div class="col-sm-12 account-recovery-2">
              <form form-name="account-recovery-2" action="asset/core/ajax/login-reset-password-verify.php" method="post" class="form-material">
                <input type="text" name="userId" value="' . $userId -> value . '" hidden/>
              
                <label for="" class="text-left control-label col-xs-12">Account Recovery Code : </label>
                <div class="form-group ' . $recoveryCodeErr -> error_class . '">
                  <input type="text" class="form-control capitalize" name="recoveryCode" placeholder="Enter Recovery Code">
                  ' . $recoveryCodeErr -> error_icon . '
                  ' . $recoveryCodeErr -> error_text . '
                </div>

              </form>
            </div>
          </div>', 
          array(
            "trasnType" => 'ajax-next-footer',
            "btnLbl1" => 'Resend',
            "btnLbl2" => 'Continue',
            "container" => 'modal-container',
            "link" =>  'asset/core/ajax/login-reset-password-validate.php',
            "transName" => 'modal-rec',
            "content" => '{&quot;userId&quot; : &quot;' . $userId -> value . '&quot;}'
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