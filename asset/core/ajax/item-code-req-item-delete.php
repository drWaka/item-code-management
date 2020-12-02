<?php
  include '../php/connection.php';
  include '../php/server-side-validation.php';
  include '../php/generic-functions.php';

  // JSON Variables
  $success = 'success';
  $content = array(
    "modal" => ''
  );

  if (isset($_POST['recordId']) && isset($_POST['itemRecId'])) {
    $recordId = new form_validation($_POST['recordId'], 'int', 'Item ID', true);
    $itemRecId = new form_validation($_POST['itemRecId'], 'int', 'Item Code Request ID', true);

   if ($recordId -> valid == 1 && $itemRecId -> valid == 1) {
      // Item Code ID Validation
      if (is_numeric($recordId -> value)) {
        $checkExist = 'SELECT * FROM item_code WHERE PK_item_code = "' . $recordId -> value . '"';
        // die($checkExist);
        $checkExistRes = $connection -> query($checkExist);

        if (!($checkExistRes -> num_rows > 0)) {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Item Record Not Found';
        }
      } else {
        $recordId -> valid = 0;
        $recordId -> err_msg = 'Invalid Item ID';
      }
    }

    if ($recordId -> valid == 1 && $itemRecId -> valid == 1) {
      // Item Code Requets ID Validation
      if (is_numeric($itemRecId -> value)) {
        $checkExist = 'SELECT * FROM item_code_req WHERE PK_item_code_req = "' . $itemRecId -> value . '"';
        // die($checkExist);
        $checkExistRes = $connection -> query($checkExist);

        if (!($checkExistRes -> num_rows > 0)) {
          $itemRecId -> valid = 0;
          $itemRecId -> err_msg = 'Item Code Request Record Not Found';
        }
      } else {
        $itemRecId -> valid = 0;
        $itemRecId -> err_msg = 'Invalid Item Code Request ID';
      }
    }

    if ($recordId -> valid == 1 && $itemRecId -> valid == 1) {
      $modalLbl = array(
        "present" => 'Delete',
        "past" => 'Deleted',
        "future" => 'Delete'
      );

      $transactQry = '
        DELETE FROM item_code
        WHERE PK_item_code = "' . $recordId -> value . '"
      ';

      if ($connection -> query($transactQry)) {
        $content['modal'] = modalize( 
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize">Item Code Request Item ' . $modalLbl['present'] . ' Success</h2>
              <p class="para-text">Item Record ' . $modalLbl['past'] . ' Successfully</p>
            </div>
          </div>', 
          array(
            "trasnType" => 'success',
            "btnLbl" => 'OK',
            "container" => 'record-container',
            "link" => '../asset/core/ajax/item-code-req-item-content.php',
            "transName" => 'recordList',
            "content" => '{&quot;itemDesc&quot; : &quot;&quot;, &quot;itemRecId&quot; : &quot;' . $itemRecId -> value . '&quot;}'
          )
        );
      } else {
        $content['modal'] = modalize( 
          '<div class="row text-center">
              <h2 class="header capitalize col-12">Error Encountered</h2>
              <p class="para-text col-12">Error Details: Unable to ' . $transactType['future'] . ' Item Record</p>
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
        $content['modal'] = modalize( 
          '<div class="row text-center">
              <h2 class="header capitalize col-12">Error Encountered</h2>
              <p class="para-text col-12">Error Details: ' . $itemRecId -> err_msg . '</p>
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

  // Return JSON encode
  encode_json_file(array($success, $content));
?>