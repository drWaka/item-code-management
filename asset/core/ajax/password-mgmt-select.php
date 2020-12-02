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
        $recordId -> valid = 0;
        $recordId -> err_msg = 'Invalid User Record ID';
      }
    }

    if ($recordId -> valid == 1) {
      
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
                <div class="form-group col-sm-12">
                  <input name="password" type="password" class="form-control" placeholder="Password">
                </div>
              </div>

              <div class="row">
                <label for="" class="text-left control-label col-sm-12">Re-Type Password : </label>
                <div class="form-group col-sm-12">
                  <input name="rePassword" type="password" class="form-control" placeholder="Re-Type Password">
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