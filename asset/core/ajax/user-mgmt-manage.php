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


  if (
      isset($_POST['recordId']) && isset($_POST['fName']) && isset($_POST['lName']) 
      && isset($_POST['email']) && isset($_POST['userType']) && isset($_POST['accountId'])
      && isset($_POST['accStat']) 
    ) {
    $recordId = new form_validation($_POST['recordId'], 'str-int', 'User Record ID', true);
    $accountId = new form_validation($_POST['accountId'], 'str-int', 'User Account ID', true);
    $fName    = new form_validation($_POST['fName'], 'str', 'First Name', true);
    $lName    = new form_validation($_POST['lName'], 'str', 'Last Name', true);
    $email    = new form_validation($_POST['email'], 'email', 'Email Address', true);
    $userType = new form_validation($_POST['userType'], 'int', 'User Type', true);
    $accStat  = new form_validation($_POST['accStat'], 'int', 'Account Status', true);

    if (
        $recordId -> valid == 1 && $fName -> valid == 1 && $lName -> valid == 1 
        && $email -> valid == 1 && $userType -> valid == 1 && $accountId -> valid == 1
        && $accStat -> valid == 1
      ) {
      // User ID Validation
      if (is_numeric($recordId -> value)) {
        $checkExist = 'SELECT * FROM sys_user WHERE PK_sys_user = "' . $recordId -> value . '"';
        $checkExistRes = $connection -> query($checkExist);

        if (!($checkExistRes -> num_rows > 0)) {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'User Record Not Found';
        }
      } else {
        if ($recordId -> value != 'new-user') {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Invalid User Record ID';
        }
      }
    }

    if (
        $recordId -> valid == 1 && $fName -> valid == 1 && $lName -> valid == 1 
        && $email -> valid == 1 && $userType -> valid == 1 && $accountId -> valid == 1
        && $accStat -> valid == 1
      ) {
      // Validate Account ID
      if (!is_numeric($recordId -> value)) {
        $selectAccQry = 'SELECT * FROM user_acc WHERE user_id = "' . $accountId -> value . '"';
        $selectAccRes = $connection -> query($selectAccQry);

        if ($selectAccRes -> num_rows > 0) {
          $accountId -> valid = 0;
          $accountId -> err_msg = 'Account ID already used by other account';
        }
      }
    }

    if (
        $recordId -> valid == 1 && $fName -> valid == 1 && $lName -> valid == 1 
        && $email -> valid == 1 && $userType -> valid == 1 && $accountId -> valid == 1
        && $accStat -> valid == 1
      ) {

      $modalLbl = array(
        "present" => '',
        "past" => '',
        "future" => ''
      );
      
      $transactOk = true;
      if ($recordId -> value == 'new-user') {
        $modalLbl = array(
          "present" => 'Registration',
          "past" => 'Registered',
          "future" => 'Register'
        );

        $transactQry = '
          INSERT INTO sys_user (
            fname, lname, email, reg_date, update_date
          ) VALUES (
            "' . $fName -> value . '", "' . $lName -> value . '", "' . $email -> value . '",
            "' . date('Y-m-d h:i:s') . '", "' . date('Y-m-d h:i:s') . '"
          )
        ';
        // echo $transactQry;
        if ($connection -> query($transactQry)) {
          // Get Last User Record ID
          $userId = $connection -> insert_id;

          // Generation of Password Salt
          $salt = sha1(uniqid(mt_rand(), true));
          $password = sha1($accountId -> value . $salt);
          $transactQry = '
            INSERT INTO user_acc (
              user_id, pwd, pwd_salt, reg_date, update_date, fk_sys_user, FK_user_type
            ) VALUES (
              "' . $accountId -> value . '", "' . $password . '", "' . $salt . '",
              "' . date('Y-m-d') . '", "' . date('Y-m-d h:i:s') . '", "' . $userId . '",
              "' . $userType -> value . '"
            )
          ';
          // echo $transactQry;
          if (!($connection ->query($transactQry))) {
            $transactOk = false;
            $content['modal'] = modalize( 
              '<div class="row text-center">
                <div class="col-sm-12">
                  <h2 class="header capitalize col-12">Error Encountered</h2>
                  <p class="para-text col-12">Error Details: Unable to Register User Account Details</p>
                </div>
              </div>', 
              array(
                "trasnType" => 'error',
                "btnLbl" => 'Dismiss'
              )
            );
          }
        } else {
          $transactOk = false;
        }
      } else {
        $modalLbl = array(
          "present" => 'Updating',
          "past" => 'Updated',
          "future" => 'Update'
        );

        $transactQry = '
          UPDATE sys_user
          SET fname = "' . $fName -> value . '",
              lname = "' . $lName -> value . '",
              email = "' . $email -> value . '",
              update_date = "' . date('Y-m-d h:i:s') . '"
          WHERE PK_sys_user = "' . $recordId -> value . '"
        ';

        if ($connection -> query($transactQry)) {
          $transactQry = '
            UPDATE user_acc
            SET FK_user_type = "' . $userType -> value . '",
                isActive = "' . $accStat -> value . '"
            WHERE PK_user_acc = "' . $recordId -> value . '"
          ';

          if (!($connection -> query($transactQry))) {
            $transactOk = false;
            $content['modal'] = modalize( 
              '<div class="row text-center">
                <div class="col-sm-12">
                  <h2 class="header capitalize col-12">Error Encountered</h2>
                  <p class="para-text col-12">Error Details: Unable to Update User Account Details</p>
                </div>
              </div>', 
              array(
                "trasnType" => 'error',
                "btnLbl" => 'Dismiss'
              )
            );
          }
        } else {
          $transactOk = false;
        }
      }

      // die($transactQry);
      if ($transactOk) {
        $content['modal'] = modalize( 
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize">User ' . $modalLbl['present'] . ' Success</h2>
              <p class="para-text">User ' . $modalLbl['past'] . ' Successfully</p>
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
              <p class="para-text col-12">Error Details: Unable to ' . $modalLbl['future'] . ' User Record</p>
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
        $fNameErr = new error_handler($fName -> err_msg);
        $lNameErr = new error_handler($lName -> err_msg);
        $emailErr = new error_handler($email -> err_msg);
        $userTypeErr = new error_handler($userType -> err_msg);
        $accountIdErr= new error_handler($accountId -> err_msg);
        $accStatErr = new error_handler($accStat -> err_msg);

        $userTypeField = '';
        $userTypeQry = 'SELECT * FROM user_type';
        $userTypeRes = $connection -> query($userTypeQry);

        if ($userTypeRes -> num_rows > 0) {
          while ($userTypeRow = $userTypeRes -> fetch_assoc()) {
            $itemGrpSelected = $userType -> value == $userTypeRow['PK_user_type'] ? 'selected' : '';
            $userTypeField .= '<option value="' . $userTypeRow['PK_user_type'] . '" ' . $itemGrpSelected . '>' . ucfirst($userTypeRow['user_type_desc']) . '</option>';
          }
        }

        $hide = strtolower($_SESSION['userType']) == 'administrator' && $_SESSION['userId'] != $recordId -> value ? '' : 'hide';
        
        $isDeactivated = $isActivated = '';
        if ($accStat -> value == '0') {
          $isDeactivated = 'selected';
        } else {
          $isActivated = 'selected';
        }

        $content['modal'] = modalize(
          '<div class="row">
            <div class="col-sm-12">
              <h2 class="header capitalize text-center">User Record Management</h2>
              <p class="para-text text-center">Please fill the field with a valid information to continue.</p>
            </div>
            
            <div class="col-sm-12 item-code-req-item-mgmt">
              <form form-name="user-rec-mgmt" action="../asset/core/ajax/user-mgmt-manage.php">
                <input type="text" name="recordId" hidden="hidden" value="' . $recordId -> value . '">

                <div class="row ' . $hide . '">
                  <div class="col-sm-6">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">User ID : </label>
                      <div class="form-group col-sm-12  ' . $accountIdErr -> error_class . '">
                        <input name="accountId" class="form-control" placeholder="Account ID" value="' . $accountId -> value . '">
                        ' . $accountIdErr -> error_icon . '
                        ' . $accountIdErr -> error_text . '
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Account Status : </label>
                      <div class="form-group col-sm-12 ' . $accStatErr -> error_class . '">
                        <select class="form-control" name="accStat">
                          <option value="1" ' . $isActivated . '>Active</option>
                          <option value="0" ' . $isDeactivated . '>Disabled</option>
                        </select>
                        ' . $accStatErr -> error_icon . '
                        ' . $accStatErr -> error_text . '
                      </div>
                    </div>
                  </div>                
                </div>
              
                <div class="row">
                  <div class="col-sm-6">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">First Name : </label>
                      <div class="form-group col-sm-12 ' . $fNameErr -> error_class . '">
                        <input name="fName" class="form-control" placeholder="First Name" value="' . $fName -> value . '">
                        ' . $fNameErr -> error_icon . '
                        ' . $fNameErr -> error_text . '
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Last Name : </label>
                      <div class="form-group col-sm-12 ' . $lNameErr -> error_class . '">
                        <input name="lName" class="form-control" placeholder="Last Name" value="' . $lName -> value . '">
                        ' . $lNameErr -> error_icon . '
                        ' . $lNameErr -> error_text . '
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-sm-6">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Email : </label>
                      <div class="form-group col-sm-12 ' . $emailErr -> error_class . '">
                        <input name="email" class="form-control" placeholder="Email" value="' . $email -> value . '">
                        ' . $emailErr -> error_icon . '
                        ' . $emailErr -> error_text . '
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-6 ' . $hide . '">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">User Type : </label>
                      <div class="form-group col-sm-12 ' . $userTypeErr -> error_class . '">
                        <select class="form-control" name="userType">
                          <option value="">Select User Type</option>
                          ' . $userTypeField . ' 
                        </select>
                        ' . $userTypeErr -> error_icon . '
                        ' . $userTypeErr -> error_text . '
                      </div>
                    </div>
                  </div>
                </div>

              </form>
            </div>
          </div>
          ', 
          array(
            "trasnType" => 'regular',
            "btnLbl" => 'Submit'
          ),
          'modal-lg'
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