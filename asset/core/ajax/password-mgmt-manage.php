<?php 
  // Connection
  require '../php/connection.php';
  // Form Validation
  require '../php/server-side-validation.php';
  // Error Handler
  require '../php/error-handler.php';
  // Misc Generic Functions
  require '../php/generic-functions.php';

  // JSON Variables
  $success = 'success';
  $content = array(
    "modal" => ''
  );


  if (isset($_POST['recordId']) && isset($_POST['password']) && isset($_POST['rePassword'])) {
    $recordId   = new form_validation($_POST['recordId'], 'str-int', 'User Record ID', true);
    $password   = new form_validation($_POST['password'], 'password', 'Password', true);
    $rePassword = new form_validation($_POST['rePassword'], 'password', 'Password', true);

    if ($recordId -> valid == 1 && $password -> valid == 1 && $rePassword -> valid == 1) {
      // User ID Validation
      if (is_numeric($recordId -> value)) {
        $checkExist = 'SELECT * FROM sys_user WHERE PK_sys_user = "' . $recordId -> value . '"';
        $checkExistRes = $connection -> query($checkExist);

        if (!($checkExistRes -> num_rows > 0)) {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'User Record Not Found';
        }
      } else {
        $recordId -> valid = 0;
        $recordId -> err_msg = 'Invalid User Record ID';
      }
    }

    if ($recordId -> valid == 1 && $password -> valid == 1 && $rePassword -> valid == 1) {
      // Password Validation
      
      if ($password -> value != $rePassword -> value) {
        $password -> valid = 0;
        $password -> err_msg = 'flag';
        $rePassword -> valid = 0;
        $rePassword -> err_msg = 'Password doesn\'t match';
      }
    }

    if ($recordId -> valid == 1 && $password -> valid == 1 && $rePassword -> valid == 1) {
      $explodePass = str_split($password -> value);
      // die(var_dump(count($explodePass)));
      $validPass = array(
        "bigChar" => false,
        "smlChar" => false,
        "number" => false
      );

      $charset = array(
        "bigChar" => str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
        "smlChar" => str_split('abcdefghijklmnopqrstuvwxyz'),
        "number" => str_split('1234567890')
      );
      for ($i = 0 ; $i < count($explodePass) ; $i++) {
        // Big Letter
        if (!$validPass['bigChar']) {
          for ($y = 0 ; $y < count($charset['bigChar']) ; $y++) {
            if ($explodePass[$i] === $charset['bigChar'][$y]) {
              $validPass['bigChar'] = true;
              break;
            }
          }
        }

        // Small Letter
        if (!$validPass['smlChar']) {
          for ($y = 0 ; $y < count($charset['smlChar']) ; $y++) {
            if ($explodePass[$i] === $charset['smlChar'][$y]) {
              $validPass['smlChar'] = true;
              break;
            }
          }
        }

        // Number
        if (!$validPass['number']) {
          for ($y = 0 ; $y < count($charset['number']) ; $y++) {
            if ($explodePass[$i] === $charset['number'][$y]) {
              $validPass['number'] = true;
              break;
            }
          }
        }

        if ($validPass['bigChar'] && $validPass['smlChar'] && $validPass['number']) {
          break;
        }
      }

      if (!$validPass['bigChar']) {
        $password -> valid = 0;
        $password -> err_msg = 'flag';
        $rePassword -> valid = 0;
        $rePassword -> err_msg = 'Password must atleast have 1 big letter';
      } else if (!$validPass['smlChar']) {
        $password -> valid = 0;
        $password -> err_msg = 'flag';
        $rePassword -> valid = 0;
        $rePassword -> err_msg = 'Password must atleast have 1 small letter';
      } else if (!$validPass['number']) {
        $password -> valid = 0;
        $password -> err_msg = 'flag';
        $rePassword -> valid = 0;
        $rePassword -> err_msg = 'Password must atleast have 1 number';
      }
    }

    if ($recordId -> valid == 1 && $password -> valid == 1 && $rePassword -> valid == 1) {

      $modalLbl = array(
        "present" => 'Updating',
        "past" => 'Updated',
        "future" => 'Update'
      );

      $selectAccQry = 'SELECT * FROM user_acc WHERE fk_sys_user = "' . $recordId -> value . '"';
      $selectAccRes = $connection -> query($selectAccQry);
      $selectAccRow = $selectAccRes -> fetch_assoc();

      $transactQry = '
        UPDATE user_acc
        SET pwd = "' . sha1($password -> value . $selectAccRow['pwd_salt']) . '",
            update_date = "' . date('Y-m-d h:i:s') . '"
        WHERE fk_sys_user = "' . $recordId -> value . '"
      ';

      // die($transactQry);
      if ($connection -> query($transactQry)) {
        $content['modal'] = modalize( 
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize">Password ' . $modalLbl['present'] . ' Success</h2>
              <p class="para-text">Password ' . $modalLbl['past'] . ' Successfully</p>
            </div>
          </div>', 
          array(
            "trasnType" => 'btn-trigger',
            "btnLbl" => 'OK'
          )
        );
      } else {
        $content['modal'] = modalize( 
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize col-12">Error Encountered</h2>
              <p class="para-text col-12">Error Details: Unable to ' . $modalLbl['future'] . ' User Password</p>
            </div>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      }

    } else {
      if ($recordId -> valid == 0) {
        $content['modal'] = modalize(
          '<div class="row text-center">
            <h2 class="header capitalize col-12">Error Encountered</h2>
            <p class="para-text col-12">Error Details: ' . $recordId -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else {
        $passwordErr   = new error_handler($password -> err_msg);
        $rePasswordErr = new error_handler($rePassword -> err_msg);

        $content['modal'] = modalize(
          '<div class="row">
            <div class="col-sm-12">
              <h2 class="header capitalize text-center">Change Password</h2>
              <p class="para-text text-center">Please fill the field with a valid information to continue.</p>
            </div>
            
            <div class="col-sm-12 password-mgmt">
              <form form-name="password-mgmt" action="../asset/core/ajax/password-mgmt-manage.php">
                <input type="text" name="recordId" hidden="hidden" value="' . $recordId -> value . '">
              
                <div class="row">
                  <label for="" class="text-left control-label col-sm-12">Password : </label>
                  <div class="form-group col-sm-12 ' . $passwordErr -> error_class . '">
                    <input name="password" type="password" class="form-control" placeholder="Password">
                    ' . $passwordErr -> error_icon . '
                    ' . $passwordErr -> error_text . '
                  </div>
                </div>

                <div class="row">
                  <label for="" class="text-left control-label col-sm-12">Re-Type Password : </label>
                  <div class="form-group col-sm-12 ' . $rePasswordErr -> error_class . '">
                    <input name="rePassword" type="password" class="form-control" placeholder="Re-Type Password">
                    ' . $rePasswordErr -> error_icon . '
                    ' . $rePasswordErr -> error_text . '
                  </div>
                </div>

              </form>
            </div>
          </div>
          ', 
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
        <h2 class="header capitalize col-12">Error Encountered</h2>
        <p class="para-text col-12">Error Details: Insufficient Data Submitted</p>
      </div>', 
      array(
        "trasnType" => 'error',
        "btnLbl" => 'Dismiss'
      )
    );
  }

  // Encode JSON File
  encode_json_file(array($success, $content));
?>