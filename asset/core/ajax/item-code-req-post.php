<?php 
  // Connection
  require '../php/connection.php';
  // Form Validation
  require '../php/server-side-validation.php';
  // Misc Generic Functions
  require '../php/generic-functions.php';
  // Generic Mail Function
  require '../php/generic-mail-function.php';

  // JSON Variables
  $success = 'success';
  $content = array(
    "totalRec" => 0,
    "recContent" => []
  );

  if (isset($_POST['recordId'])) {
    $recordId = new form_validation($_POST['recordId'], 'str-int', 'Item Code Request ID', true);

    if ($recordId -> valid == 1) {
      if (is_numeric($recordId -> value)) {
        $guideRecQry = '
          SELECT * FROM item_code_req 
          WHERE pk_item_code_req = "' . $recordId -> value . '"
        ';
        $guideRecRes = $connection -> query($guideRecQry);

        if (!($guideRecRes -> num_rows > 0)) {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Item Code Request Record Not Found';
        } else {
          $guideRecRow = $guideRecRes -> fetch_assoc();
        }
      } else {
        $recordId -> valid = 0;
        $recordId -> err_msg = 'Invalid Item Code Request ID';
      }
    }

    if ($recordId -> valid == 1) {
      $checkItemRec = 'SELECT * FROM item_code WHERE FK_item_code_req = "' . $recordId -> value . '"';
      // die($checkItemRec);
      $checkItemRecRes = $connection -> query($checkItemRec);

      if (!($checkItemRecRes -> num_rows > 0)) {
        $recordId -> valid = 0;
        $recordId -> err_msg = 'Unable to post request with 0 item';
      }
    }

    if ($recordId -> valid == 1) {
      $modalLbl = array(
        "present" => 'Post',
        "past" => 'Posted',
        "future" => 'Post'
      );

      $transactQry = '
        UPDATE item_code_req
        SET FK_request_stat = (
          SELECT PK_status FROM status 
          WHERE stat_desc = "posted" AND type = "RQ" 
        ),
        FK_verify_stat = (
          SELECT PK_status FROM status 
          WHERE stat_desc = "pending" AND type = "VF" 
        )
        WHERE pk_item_code_req = "' . $recordId -> value . '"
      ';

      if ($connection -> query($transactQry)) {
        // Select the Active Verifier Records
        $verifierQry = '
          SELECT a.email as email, a.fname as fname, a.lname as lname 
          FROM sys_user AS a
          INNER JOIN user_acc AS b ON a.PK_sys_user = b.FK_sys_user
          INNER JOIN user_type AS c ON b.FK_user_type = c.PK_user_type
          WHERE c.user_type_desc IN ("administrator", "verifier") AND b.isActive = 1
        ';
        $verifierRes = $connection -> query($verifierQry);

        // Email Reievers
        $recieverInfo = array();
        if ($verifierRes -> num_rows > 0) {
          while ($verifierRow = $verifierRes -> fetch_object()) {
            // Email Message Initialization
            $recieverInfo[] = new stdClass();
            $recieverInfo[(count($recieverInfo) - 1)] -> email = $verifierRow -> email;
            $recieverInfo[(count($recieverInfo) - 1)] -> fullname = ucfirst($verifierRow -> fname . ' ' . $verifierRow -> lname);
          }
        } 

        // Email Message Initialization
        $emailContent = new stdClass();
        $emailContent -> title = 'ICM #' . $recordId -> value . ' : New Item Code Request Posted by ' . ucfirst($_SESSION['firstName']) . ' ' .  ucfirst($_SESSION['lastName']);
        $emailContent -> mainBody = '
          <div style="width : 100%; background-color : #F0F0F0; padding-top: 20px; padding-bottom: 20px; text-align : center; font-family: Arial, sans-serif;">
            <div style="width : 80%; text-align : left; background-color : #FFF; margin : auto; padding: 15px;">
              
              Good Day!<br>
              
              <br><br>
              This is an automated message generated by <b>Item Code Management (ICM) System</b> to notify you that <b>' . ucfirst($_SESSION['firstName']) . ' ' .  ucfirst($_SESSION['lastName']) . '</b> has posted a new Item Code Request <b>#' . $recordId -> value . '</b>.
              
              <br><br><br>
              Please use this <a href="http://192.168.3.144:8080/item-code-management/html/item-code-verify.php">link</a> to verify the Item Code Request.
              
              <br><br><br>
              Our Lady of Lourdes Hospital &minus; Item Code Management (ICM) System Support Team
            </div>
          </div>
        ';
        $emailContent -> alternateBody = '
          Good Day!
          This is an automated message generated by ITEM CODE MANAGEMENT (ICM) SYSTEM to Notify you that ' . strtoupper($_SESSION['firstName'] . ' ' . $_SESSION['lastName']) . 'has posted a new Item Code Request Record.
          
          Please use go to this link: "http://192.168.3.144:8080/item-code-management/html/item-code-verify.php" to verify the Item Code Request.
          
          <b>Our Lady of Lourdes Hospital &minus; Item Code Management (ICM) System Support Team</b>
        ';

        // Invoke Send Email Function
        $result = sendEmail($recieverInfo, $emailContent);

        $content['modal'] = modalize( 
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize">Item Code Request ' . $modalLbl['present'] . ' Success</h2>
              <p class="para-text">Item Code Request ' . $modalLbl['past'] . ' Successfully</p>
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
              <h2 class="header capitalize col-12">Error Encountered</h2>
              <p class="para-text col-12">Error Details: Unable to Cancel Item Code Request</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      }
    } else {
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
  } else {
    $success = 'failed';
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