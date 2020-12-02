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


  if (isset($_POST['recordId']) && isset($_POST['risNo']) && isset($_FILES['risImg']) && isset($_FILES['quotImg']) && isset($_POST['deptAccess'])) {
    $recordId   = new form_validation($_POST['recordId'], 'str-int', 'Item Code Request ID', true);
    $risNo      = new form_validation($_POST['risNo'], 'str-int', 'RIS No', true);
    $deptAccess = new form_validation($_POST['deptAccess'], 'str', 'Department Access', true);

    $isRequired = !is_numeric($recordId -> value);
    $risImg     = new form_validation($_FILES['risImg'], 'file-image', 'RIS Image', $isRequired);
    $quotImg    = new form_validation($_FILES['quotImg'], 'file-image', 'Quotation Image', $isRequired);

    if ($recordId -> valid == 1 && $risNo -> valid == 1 && $risImg -> valid == 1 && $quotImg -> valid == 1 && $deptAccess -> valid == 1) {

      if (is_numeric($recordId -> value)) {
        $checkExist = 'SELECT * FROM item_code_req WHERE PK_item_code_req = "' . $recordId -> value . '"';
        $checkExistRes = $connection -> query($checkExist);

        if (!($checkExistRes -> num_rows > 0)) {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Item Code Request Not Found';
        }
      } else {
        if ($recordId -> value != 'new-rec') {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Item Code Request ID is Invalid';
        }
      }
    }

    if ($recordId -> valid == 1 && $risNo -> valid == 1 && $risImg -> valid == 1 && $quotImg -> valid == 1 && $deptAccess -> valid == 1) {
      if (!is_numeric($recordId -> value)) {
         $checkRisNo = '
          SELECT * FROM item_code_req WHERE ris_no = "' . $risNo -> value . '" AND FK_request_stat != (
            SELECT PK_status FROM status WHERE stat_desc = "cancelled"
          )
        ';
        $checkRisNoRes = $connection -> query($checkRisNo);

        if ($checkRisNoRes -> num_rows > 0) {
          $risNo -> valid = 0;
          $risNo -> err_msg = 'RIS No already exists';
        }
      }
    }

    if ($recordId -> valid == 1 && $risNo -> valid == 1 && $risImg -> valid == 1 && $quotImg -> valid == 1 && $deptAccess -> valid == 1) {

      $modalLbl = array(
        "present" => '',
        "past" => '',
        "future" => ''
      );

      if ($recordId -> value == 'new-rec') {
        $modalLbl = array(
          "present" => 'Registration',
          "past" => 'Registered',
          "future" => 'Register'
        );
        $statusQry = 'SELECT PK_status FROM status WHERE stat_desc = "saved"';
        $transactQry = '
          INSERT INTO item_code_req (
            ris_no, dept_access, FK_requestor, date_requested, FK_request_stat
          ) VALUES (
            "' . $risNo -> value . '", "' . $deptAccess -> value . '", "' . $_SESSION['userId'] . '", "' . date('Y-m-d') . '", (' . $statusQry . ')
          )
        ';
      } else {
        $modalLbl = array(
          "present" => 'Updating',
          "past" => 'Updated',
          "future" => 'Update'
        );

        $transactQry = '
          UPDATE item_code_req
          SET ris_no = "' . $risNo -> value . '",
              dept_access  = "' . $deptAccess -> value . '"
          WHERE pk_item_code_req = "' . $recordId -> value . '"
        ';
      }

      // die($transactQry);
      if ($connection -> query($transactQry)) {
        // Flag that will determine if the transaction is successful
        $transactionSuccess = true;
        // Get Last Inserted Record
        $lastInsertId = ($recordId -> value == 'new-rec') ? $connection -> insert_id : $recordId -> value;
        
        if (!empty($risImg -> value)) {
          // Determine Image File Name
          $fileXtn = explode('.', $risImg -> value['name']);
          $fileXtn = end($fileXtn);
          $fileName = 'ris-' . $lastInsertId . '.' . $fileXtn;
          // Set File Directory
          $fileDirec = '../img/ris/' . $fileName;

          if (file_exists($fileDirec)) {
            if (!unlink($fileDirec)) {
              $transactionSuccess = false;
              $content['modal'] = modalize( 
                '<div class="row text-center">
                  <div class="col-sm-12">
                    <h2 class="header capitalize col-12">Error Encountered</h2>
                    <p class="para-text col-12">Error Details: Unable to Delete Previous RIS Image</p>
                  </div>
                </div>', 
                array(
                  "trasnType" => 'error',
                  "btnLbl" => 'Dismiss'
                )
              );
            }
          }

          if ($transactionSuccess) {
            if (move_uploaded_file($risImg -> value['tmp_name'], $fileDirec)) {
              $updateNewRec = '
                UPDATE item_code_req
                SET ris_img = "' . $fileName . '"
                WHERE pk_item_code_req = "' . $lastInsertId . '"
              ';

              if (!($connection -> query($updateNewRec))) {
                $transactionSuccess = false;
                $content['modal'] = modalize( 
                  '<div class="row text-center">
                    <div class="col-sm-12">
                      <h2 class="header capitalize col-12">Error Encountered</h2>
                      <p class="para-text col-12">Error Details: Unable to Update RIS Field</p>
                    </div>
                  </div>', 
                  array(
                    "trasnType" => 'error',
                    "btnLbl" => 'Dismiss'
                  )
                );
              }
            } else {
              $transactionSuccess = false;
              $content['modal'] = modalize( 
                '<div class="row text-center">
                  <div class="col-sm-12">
                    <h2 class="header capitalize col-12">Error Encountered</h2>
                    <p class="para-text col-12">Error Details: Unable to Upload RIS Image</p>
                  </div>
                </div>', 
                array(
                  "trasnType" => 'error',
                  "btnLbl" => 'Dismiss'
                )
              );
            }
          }
          
        }


        if ($transactionSuccess && !empty($quotImg -> value)) {
          // Determine Image File Name
          $fileXtn = explode('.', $quotImg -> value['name']);
          $fileXtn = end($fileXtn);
          $fileName = 'quot-' . $lastInsertId . '.' . $fileXtn;
          // Set File Directory
          $fileDirec = '../img/quot/' . $fileName;

          if (file_exists($fileDirec)) {
            if (!unlink($fileDirec)) {
              $transactionSuccess = false;
              $content['modal'] = modalize( 
                '<div class="row text-center">
                  <div class="col-sm-12">
                    <h2 class="header capitalize col-12">Error Encountered</h2>
                    <p class="para-text col-12">Error Details: Unable to Delete Previous Quotation Image</p>
                  </div>
                </div>', 
                array(
                  "trasnType" => 'error',
                  "btnLbl" => 'Dismiss'
                )
              );
            }
          }

          if ($transactionSuccess) {
            if (move_uploaded_file($quotImg -> value['tmp_name'], $fileDirec)) {
              $updateNewRec = '
                UPDATE item_code_req
                SET quot_img = "' . $fileName . '"
                WHERE pk_item_code_req = "' . $lastInsertId . '"
              ';

              if (!($connection -> query($updateNewRec))) {
                $transactionSuccess = false;
                $content['modal'] = modalize( 
                  '<div class="row text-center">
                    <div class="col-sm-12">
                      <h2 class="header capitalize col-12">Error Encountered</h2>
                      <p class="para-text col-12">Error Details: Unable to Update Quotation Field</p>
                    </div>
                  </div>', 
                  array(
                    "trasnType" => 'error',
                    "btnLbl" => 'Dismiss'
                  )
                );
              }
            } else {
              $transactionSuccess = false;
              $content['modal'] = modalize( 
                '<div class="row text-center">
                  <div class="col-sm-12">
                    <h2 class="header capitalize col-12">Error Encountered</h2>
                    <p class="para-text col-12">Error Details: Unable to Upload Quotation Image</p>
                  </div>
                </div>', 
                array(
                  "trasnType" => 'error',
                  "btnLbl" => 'Dismiss'
                )
              );
            }
          }
        }

        if ($transactionSuccess) {
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
        }
        
      } else {
        $content['modal'] = modalize( 
          '<div class="row text-center">
            <div class="col-sm-12">
              <h2 class="header capitalize col-12">Error Encountered</h2>
              <p class="para-text col-12">Error Details: Unable to ' . $modalLbl['future'] . ' Item Code Request Record</p>
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
      } else {
        $risNoErr = new error_handler($risNo -> err_msg);
        $risImgErr = new error_handler($risImg -> err_msg);
        $quotImgErr = new error_handler($quotImg -> err_msg);
        $deptAccessErr = new error_handler($deptAccess -> err_msg);

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
                  <div class="form-group col-sm-12 ' . $risNoErr -> error_class . '">
                    <input name="risNo" class="form-control" placeholder="RIS No" value="' . $risNo -> value . '">
                    ' . $risNoErr -> error_icon . '
                    ' . $risNoErr -> error_text . '
                  </div>
                </div>

                <div class="row">
                  <label for="" class="text-left control-label col-sm-12">RIS Image : </label>
                  <div class="form-group col-sm-12 ' . $risImgErr -> error_class . '">
                    <input type="file" class="form-control" name="risImg" />
                    ' . $risImgErr -> error_icon . '
                    ' . $risImgErr -> error_text . '
                  </div>
                </div>

                <div class="row">
                  <label for="" class="text-left control-label col-sm-12">Quotation Image : </label>
                  <div class="form-group col-sm-12 ' . $quotImgErr -> error_class . '">
                    <input type="file" class="form-control" name="quotImg" />
                    ' . $quotImgErr -> error_icon . '
                    ' . $quotImgErr -> error_text . '
                  </div>
                </div>

                <div class="row">
                  <label for="" class="text-left control-label col-sm-12">Requesting Department(s) : </label>
                  <div class="form-group col-sm-12 ' . $deptAccessErr -> error_class . '">
                    <textarea name="deptAccess" class="form-control" placeholder="Requesting Department(s)">' . $deptAccess -> value . '</textarea>
                    ' . $deptAccessErr -> error_icon . '
                    ' . $deptAccessErr -> error_text . '
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