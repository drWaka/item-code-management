<?php
  include '../php/connection.php';
  include '../php/server-side-validation.php';
  include '../php/generic-functions.php';

  // JSON Variables
  $success = 'success';
  $content = array(
    "modal" => ''
  );

  if (isset($_POST['recordId'])) {
    $recordId = new form_validation($_POST['recordId'], 'int', 'Item Code Request ID', true);

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
      $modalLbl = array(
        "present" => 'Unpost',
        "past" => 'Unposted',
        "future" => 'Unpost'
      );

      $transactQry = '
        UPDATE item_code_req
        SET FK_request_stat = (
          SELECT PK_status FROM status 
          WHERE stat_desc = "saved" AND type = "RQ"
        ), 
        FK_poster = 0,
        date_posted = NULL
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

  // Return JSON encode
  encode_json_file(array($success, $content));
?>