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
  
  if (isset($_POST['recordId']) && isset($_POST['itemRecId']) && isset($_POST['itemCode'])) {
    $recordId  = new form_validation($_POST['recordId'], 'str-int', 'Item Code ID', true);
    $itemRecId = new form_validation($_POST['itemRecId'], 'int', 'Item Code Request ID', true);
    $itemCode  = new form_validation($_POST['itemCode'], 'str-int', 'Item Code', true);

    if ($recordId -> valid == 1 && $itemRecId -> valid == 1 && $itemCode -> valid == 1) {
      // Item Code ID Validation
      if (is_numeric($recordId -> value)) {
        $checkExist = 'SELECT * FROM item_code WHERE PK_item_code = "' . $recordId -> value . '"';
        $checkExistRes = $connection -> query($checkExist);

        if (!($checkExistRes -> num_rows > 0)) {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Item Record Not Found';
        }
      } else {
        $recordId -> valid = 0;
        $recordId -> err_msg = 'Invalid Item Record ID';
      }
    }

    if ($recordId -> valid == 1 && $itemRecId -> valid == 1 && $itemCode -> valid == 1) {
      // Item Code Requets ID Validation
      if (is_numeric($itemRecId -> value)) {
        $checkExist = 'SELECT * FROM item_code_req WHERE PK_item_code_req = "' . $itemRecId -> value . '"';
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

    if ($recordId -> valid == 1 && $itemRecId -> valid == 1 && $itemCode -> valid == 1) {
      $modalLbl = array(
        "present" => 'Encode',
        "past" => 'Encoded',
        "future" => 'Encode'
      );
      $transactQry = '
        UPDATE item_code
        SET item_code = "' . $itemCode -> value . '"
        WHERE PK_item_code = "' . $recordId -> value . '"
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
            "trasnType" => 'success',
            "btnLbl" => 'OK',
            "container" => 'record-container',
            "link" => '../asset/core/ajax/item-code-encode-item-content.php',
            "transName" => 'recordList',
            "content" => '{&quot;itemDesc&quot; : &quot;&quot;, &quot;itemRecId&quot; : &quot;' . $itemRecId -> value . '&quot;}'
          )
        );
      } else {
        $content['modal'] = modalize( 
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize col-12">Error Encountered</h2>
              <p class="para-text col-12">Error Details: Unable to ' . $modalLbl['future'] . ' Item Record</p>
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
      } else if ($itemRecId -> valid == 0) {
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
      } else {
        $itemCodeErr  = new error_handler($itemCode -> err_msg);

         $content['modal'] = modalize(
          '<div class="row">
            <div class="col-sm-12">
              <h2 class="header capitalize text-center">Item Code Request Item</h2>
              <p class="para-text text-center">Please fill the field with a valid information to continue.</p>
            </div>
            
            <div class="col-sm-12 item-code-req-item-mgmt">
              <form form-name="item-code-req-item-mgmt" action="../asset/core/ajax/item-code-encode-item-manage.php">
                <input type="text" name="recordId" hidden="hidden" value="' . $recordId -> value . '">
                <input type="text" name="itemRecId" hidden="hidden" value="' . $itemRecId -> value . '">
              
                <div class="row">
                  <label for="" class="text-left control-label col-sm-12">Item Code : </label>
                  <div class="form-group col-sm-12 ' . $itemCodeErr -> error_class . '">
                    <input name="itemCode" class="form-control" placeholder="Item Code" value="' . $itemCode -> value . '">
                    ' . $itemCodeErr -> error_icon . '
                    ' . $itemCodeErr -> error_text . '
                  </div>
                </div>

              </form>
            </div>
          </div>', 
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