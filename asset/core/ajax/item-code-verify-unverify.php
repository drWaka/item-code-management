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
        $recordId -> err_msg = 'Unable to verify request with 0 item';
      }
    }

    if ($recordId -> valid == 1) {
      $modalLbl = array(
        "present" => 'Unverify',
        "past" => 'Unverified',
        "future" => 'Unverify'
      );

      $transactQry = '
        UPDATE item_code_req
        SET FK_encode_stat = 0,
        FK_verify_stat = (
          SELECT PK_status FROM status 
          WHERE stat_desc = "pending" AND type = "VF" 
        ),
        FK_verifier = 0,
        date_verified = NULL
        WHERE pk_item_code_req = "' . $recordId -> value . '"
      ';

      if ($connection -> query($transactQry)) {
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