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

  if (isset($_POST['recordId']) && isset($_POST['itemRecId'])) {
    $recordId  = new form_validation($_POST['recordId'], 'str-int', 'Item Code ID', true);
    $itemRecId = new form_validation($_POST['itemRecId'], 'str-int', 'Item Code Request ID', true);

    if ($recordId -> valid == 1 && $itemRecId -> valid == 1) {
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
                <div class="form-group col-sm-12">
                  <input name="itemCode" class="form-control" placeholder="Item Code">
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

  // Encode JSON File
  encode_json_file(array($success, $content));
?>