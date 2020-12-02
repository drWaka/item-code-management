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

  if (isset($_POST['recordId'])) {
    $recordId  = new form_validation($_POST['recordId'], 'str-int', 'Item Code Request ID', true);

    $recordRow;
    if ($recordId -> valid == 1) {
      if (is_numeric($recordId -> value)) {
        $checkExist = 'SELECT * FROM item_code_req WHERE PK_item_code_req = "' . $recordId -> value . '"';
        $checkExistRes = $connection -> query($checkExist);

        if (!($checkExistRes -> num_rows > 0)) {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Item Code Request Not Found';
        } else {
          $recordRow = $checkExistRes -> fetch_assoc();
        }
      } else {
        if ($recordId -> value != 'new-rec') {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Item Code Request ID is Invalid';
        } else {
          $recordRow = array(
            "ris_no" => '',
            "dept_access" => ''
          );
        }
      }
    }

    if ($recordId -> valid == 1) {
      $content['modal'] = modalize(
        '<div class="row">
          <div class="col-sm-12">
            <h2 class="header capitalize text-center">Item Code Request</h2>
            <p class="para-text text-center">Please fill the field with a valid information to continue.</p>
          </div>
          
          <div class="col-sm-12 item-code-req-mgmt">
            <form form-name="item-code-req-mgmt" action="../asset/core/ajax/item-code-req-manage.php">
              <input type="text" name="recordId" hidden="hidden" value="' . $recordId -> value . '">
            
              <div class="row">
                <label for="" class="text-left control-label col-sm-12">RIS No : </label>
                <div class="form-group col-sm-12">
                  <input name="risNo" class="form-control" placeholder="RIS No" value="' . $recordRow['ris_no'] . '">
                </div>
              </div>

              <div class="row">
                <label for="" class="text-left control-label col-sm-12">RIS Image : </label>
                <div class="form-group col-sm-12">
                  <input type="file" class="form-control" name="risImg" />
                </div>
              </div>

              <div class="row">
                <label for="" class="text-left control-label col-sm-12">Quotation Image : </label>
                <div class="form-group col-sm-12">
                  <input type="file" class="form-control" name="quotImg" />
                </div>
              </div>

              <div class="row">
                <label for="" class="text-left control-label col-sm-12">Requesting Department(s) : </label>
                <div class="form-group col-sm-12">
                  <textarea name="deptAccess" class="form-control" placeholder="Requesting Department(s)">' . $recordRow['dept_access'] . '</textarea>
                </div>
              </div>

            </form>
          </div>
        </div>', 
        array(
          "trasnType" => 'regular',
          "btnLbl" => 'Submit'
        ),
        'modal-lg'
      );
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

  // Encode JSON File
  encode_json_file(array($success, $content));
?>