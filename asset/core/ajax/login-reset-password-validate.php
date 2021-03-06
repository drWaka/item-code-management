<?php 
  require '../php/connection.php';
  require '../php/server-side-validation.php';
  require '../php/error-handler.php';
  require '../php/generic-functions.php';
  require '../php/generic-mail-function.php';

  // JSON Variables
  $success = 'success';
  $content = array(
    "modal" => ''
  );

  if (isset($_POST['userId'])) {
    $userId = new form_validation($_POST['userId'], 'str-int', 'User ID', true);

    if ($userId -> valid == 1) {
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

    if ($userId -> valid == 1) {

      if (!isset($_SESSION['accRecoverKey'])) {
        $str_keys = 'abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . '0123456789';
        $str_keys = str_split($str_keys);
        shuffle($str_keys);
        $str_keys = implode('', $str_keys);

        $_SESSION['accRecoverKey'] = substr($str_keys, 0, 5);
      }

      $userAccRow = $userAccRes -> fetch_object();

      // Email Message Initialization
      $recieverInfo = array();
      $recieverInfo[] = new stdClass();
      $recieverInfo[0] -> email = $userAccRow -> email;
      $recieverInfo[0] -> fullname = ucfirst($userAccRow -> fname . ' ' . $userAccRow -> lname);

      $emailContent = new stdClass();
      $emailContent -> title = 'Account Recovery Code';
      $emailContent -> mainBody = '
        <div style="width : 100%; background-color : #F0F0F0; padding-top: 20px; padding-bottom: 20px; text-align : center; font-family: Verdana, sans-serif;">
          <div style="width : 80%; text-align : left; background-color : #FFF; margin : auto; padding: 15px;">
            <h3 style="text-align : center;  text-transform : uppercase">Account Recovery Code</h3>
            Hello ' . ucfirst($userAccRow -> fname) . ' ' . ucfirst($userAccRow -> lname) . ',<br>
            <br>
            This is an automated message generated by <b>Our Lady of Lourdes Hospital &minus; Website Administrator</b> to help you reset your Account Password.

            Please enter the following code into the <b>Verification Code</b> field of the <b>Account Recovery</b> dialog. (Enter the code exactly as written. You can use copy/paste operations to enter the code):

            <h1 style="text-align : center">' . $_SESSION['accRecoverKey'] . '</h1>
            <br>
            NOTICE: If haven\'t done anything that concerns this action, please disregard this message.
            <br>
            <b>Our Lady of Lourdes Hospital &minus; Website Administrator Support Team</b>
          </div>
        </div>
      ';
      $emailContent -> alternateBody = '
        Account Recovery Code
        Hello ' . ucfirst($userAccRow -> fname) . ' ' . ucfirst($userAccRow -> lname) . ',<br>
        <br>
        This is an automated message generated by Our Lady of Lourdes Hospital &minus; Website Administrator to help you reset your Account Password.

        Please enter the following code into the Verification Code field of the Account Recovery dialog. (Enter the code exactly as written. You can use copy/paste operations to enter the code):
        
         ' . $_SESSION['accRecoverKey'] . '
        NOTICE: If haven\'t done anything that concerns this action, please disregard this message.
        Our Lady of Lourdes Hospital &minus; Website Administrator Support Team
      ';

      // Invoke Send Email Function
      $result = sendEmail($recieverInfo, $emailContent);
      
      if ($result) {
        $email_raw = explode('@', $userAccRow -> email);
        $email_hashed = substr($email_raw[0], 0, 1);
        for ($index = (strlen($email_raw[0]) - 2) ; $index > 0 ; $index--) {
          $email_hashed .= '*';
        }
        $email_hashed .= substr($email_raw[0], strlen($email_raw[0]) - 1, 1) . '@' . $email_raw[1];

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
                <div class="form-group">
                  <input type="text" class="form-control capitalize" name="recoveryCode" placeholder="Enter Recovery Code" autocomplete="off">
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
      } else {
        $content['modal'] = modalize(
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize">Error Encountered</h2>
              <p class="para-text">Error Details: Unable to send verification code. Please make sure that you are connected to the internet.</p>
            </div>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );   
      }
    } else {
      $userIdErr = new error_handler($userId -> err_msg);
      
      $content['modal'] = modalize(
        '<div class="row">
          <div class="col-sm-12">
            <h2 class="header capitalize text-center">Employee Account Recovery</h2>
            <p class="para-text text-center">Please fill the field with a valid information to continue.</p>
          </div>
          
          <div class="col-sm-12 account-recovery">
            <form form-name="account-recovery" action="asset/core/ajax/login-reset-password-validate.php" method="post" class="form-material" onsubmit="return false;">
            
              <label for="" class="text-left control-label col-xs-12">User ID : </label>
              <div class="form-group ' . $userIdErr -> error_class . '">
                <input type="text" class="form-control capitalize" name="userId" placeholder="Enter User ID" autocomplete="off">
                ' . $userIdErr -> error_icon . '
                ' . $userIdErr -> error_text . '
              </div>

            </form>
          </div>
        </div>', 
        array(
          "trasnType" => 'regular',
          "btnLbl" => 'Recover'
        )
      );
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