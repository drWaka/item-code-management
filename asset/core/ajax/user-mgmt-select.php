<?php 
  // Connection
  require '../php/connection.php';
  // Form Validation
  require '../php/server-side-validation.php';
  // Misc Generic Functions
  require '../php/generic-functions.php';

  // JSON Variables
  $success = 'success';
  $content = array(
    "modal" => ''
  );

  if (isset($_POST['userId'])) {
    $recordId  = new form_validation($_POST['userId'], 'str-int', 'User ID', true);

    if ($recordId -> valid == 1) {
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

    if ($recordId -> valid == 1) {

      $transactQry = '
        SELECT a.fname as fname, a.lname as lname, a.email as email, c.PK_user_type as user_type, b.user_id as user_id, b.isActive as is_active
        FROM sys_user as a 
        INNER JOIN user_acc as b ON a.PK_sys_user = b.FK_sys_user
        INNER JOIN user_type as c ON b.FK_user_type = c.PK_user_type
        WHERE a.PK_sys_user = "' . $recordId -> value . '"
      ';
      // die($transactQry);
      $transactRes = $connection -> query($transactQry);
      if ($transactRes -> num_rows > 0) {
        $transactRow = $transactRes -> fetch_assoc();  
      } else {
        $transactRow = array();
        $transactRow['fname'] = '';
        $transactRow['lname'] = '';
        $transactRow['email'] = '';
        $transactRow['user_type'] = '';
        $transactRow['user_id'] = '';
        $transactRow['is_active'] = 1;
      }

      $userTypeField = '';
      $userTypeQry = 'SELECT * FROM user_type';
      $userTypeRes = $connection -> query($userTypeQry);

      if ($userTypeRes -> num_rows > 0) {
        while ($userTypeRow = $userTypeRes -> fetch_assoc()) {
          $itemGrpSelected = $transactRow['user_type'] == $userTypeRow['PK_user_type'] ? 'selected' : '';
          $userTypeField .= '<option value="' . $userTypeRow['PK_user_type'] . '" ' . $itemGrpSelected . '>' . ucfirst($userTypeRow['user_type_desc']) . '</option>';
        }
      }

      $hide = strtolower($_SESSION['userType']) == 'administrator' && $_SESSION['userId'] != $recordId -> value ? '' : 'hide';
      $isDeactivated = $transactRow['is_active'] == 0 ? 'selected' : '';

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
                    <div class="form-group col-sm-12">
                      <input name="accountId" class="form-control" placeholder="Account ID" value="' . $transactRow['user_id'] . '">
                    </div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Account Status : </label>
                    <div class="form-group col-sm-12">
                      <select class="form-control" name="accStat">
                        <option value="1">Active</option>
                        <option value="0" ' . $isDeactivated . '>Disabled</option>
                      </select>
                    </div>
                  </div>
                </div>                
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">First Name : </label>
                    <div class="form-group col-sm-12">
                      <input name="fName" class="form-control" placeholder="First Name" value="' . $transactRow['fname'] . '">
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Last Name : </label>
                    <div class="form-group col-sm-12">
                      <input name="lName" class="form-control" placeholder="Last Name" value="' . $transactRow['lname'] . '">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Email : </label>
                    <div class="form-group col-sm-12">
                      <input name="email" class="form-control" placeholder="Email" value="' . $transactRow['email'] . '">
                    </div>
                  </div>
                </div>

                <div class="col-sm-6 ' . $hide . '">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">User Type : </label>
                    <div class="form-group col-sm-12">
                      <select class="form-control" name="userType">
                        <option value="">Select User Type</option>
                        ' . $userTypeField . ' 
                      </select>
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