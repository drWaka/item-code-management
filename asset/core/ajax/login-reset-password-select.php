<?php 
  // Connection
  require '../php/connection.php';
  // Misc Generic Functions
  require '../php/generic-functions.php';

  // JSON Variables
  $success = 'success';
  $content = array(
    "modal" => ''
  );

  $content['modal'] = modalize(
    '<div class="row">
      <div class="col-sm-12">
        <h2 class="header capitalize text-center">Employee Account Recovery</h2>
        <p class="para-text text-center">Please fill the field with a valid information to continue.</p>
      </div>
      
      <div class="col-sm-12 account-recovery">
        <form form-name="account-recovery" action="asset/core/ajax/login-reset-password-validate.php" class="form-material" onsubmit="return false;">
        
          <label for="" class="text-left control-label col-xs-12">User ID : </label>
          <div class="form-group">
            <input type="text" class="form-control capitalize" name="userId" placeholder="Enter User ID" autocomplete="off">
          </div>

        </form>
      </div>
    </div>', 
    array(
      "trasnType" => 'regular',
      "btnLbl" => 'Recover'
    )
  );

  // Return JSON encode
  encode_json_file(array($success, $content));
?>