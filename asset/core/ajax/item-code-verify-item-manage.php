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
  
  if (
    isset($_POST['recordId']) && isset($_POST['itemRecId']) && isset($_POST['itemDesc']) && 
    isset($_POST['sUnit']) && isset($_POST['bUnit']) && isset($_POST['conv']) && 
    isset($_POST['itemCateg']) && isset($_POST['itemGrp']) && isset($_POST['itemCode'])
  ) {
    $recordId  = new form_validation($_POST['recordId'], 'str-int', 'Item Code ID', true);
    $itemRecId = new form_validation($_POST['itemRecId'], 'int', 'Item Code Request ID', true);
    $itemCode  = new form_validation($_POST['itemCode'], 'str-int', 'Item Code', true);
    $itemDesc  = new form_validation($_POST['itemDesc'], 'str-int', 'Item Description', true);
    $sUnit     = new form_validation($_POST['sUnit'], 'int', 'Small Unit', true);
    $bUnit     = new form_validation($_POST['bUnit'], 'int', 'Big Unit', true);
    $conv      = new form_validation($_POST['conv'], 'int', 'Conversion', true);
    $itemCateg = new form_validation($_POST['itemCateg'], 'int', 'Item Category', true);
    $itemGrp   = new form_validation($_POST['itemGrp'], 'int', 'Item Group', true);

    if (
      $recordId -> valid == 1 && $itemRecId -> valid == 1 && $itemDesc -> valid == 1 && 
      $sUnit -> valid == 1 && $bUnit -> valid == 1 && $conv -> valid == 1 && 
      $itemCateg -> valid == 1 && $itemGrp -> valid == 1 && $itemCode -> valid == 1
    ) {
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

    if (
      $recordId -> valid == 1 && $itemRecId -> valid == 1 && $itemDesc -> valid == 1 && 
      $sUnit -> valid == 1 && $bUnit -> valid == 1 && $conv -> valid == 1 && 
      $itemCateg -> valid == 1 && $itemGrp -> valid == 1 && $itemCode -> valid == 1
    ) {
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

    if (
      $recordId -> valid == 1 && $itemRecId -> valid == 1 && $itemDesc -> valid == 1 && 
      $sUnit -> valid == 1 && $bUnit -> valid == 1 && $conv -> valid == 1 && 
      $itemCateg -> valid == 1 && $itemGrp -> valid == 1 && $itemCode -> valid == 1
    ) {
      // Verify Item Description
      if (strlen($itemDesc -> value) > 40) {
        $itemDesc -> valid = 0;
        $itemDesc -> err_msg = 'Item Description reached maximum of 40 characters';
      }
    }

    if (
      $recordId -> valid == 1 && $itemRecId -> valid == 1 && $itemDesc -> valid == 1 && 
      $sUnit -> valid == 1 && $bUnit -> valid == 1 && $conv -> valid == 1 && 
      $itemCateg -> valid == 1 && $itemGrp -> valid == 1 && $itemCode -> valid == 1
    ) {
      $modalLbl = array(
        "present" => 'Updating',
        "past" => 'Updated',
        "future" => 'Update'
      );
      $transactQry = '
        UPDATE item_code
        SET item_desc = "' . $itemDesc -> value . '",
            FK_sunit = "' . $sUnit -> value . '",
            FK_bunit = "' . $bUnit -> value . '",
            conv = "' . $conv -> value . '",
            FK_item_categ = "' .$itemCateg -> value  . '",
            FK_item_group = "' . $itemGrp -> value . '",
            item_code = "' . $itemCode -> value . '",
            isExisting = "1"
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
            "link" => '../asset/core/ajax/item-code-verify-item-content.php',
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
        $itemDescErr  = new error_handler($itemDesc -> err_msg);
        $sUnitErr     = new error_handler($sUnit -> err_msg);
        $bUnitErr     = new error_handler($bUnit -> err_msg);
        $convErr      = new error_handler($conv -> err_msg);
        $itemCategErr = new error_handler($itemCateg -> err_msg);
        $itemGrpErr   = new error_handler($itemGrp -> err_msg);

        $bigUnitSelect = $smallUnitSelect = '';
        $unitQry = 'SELECT * FROM uom';
        $unitRes = $connection -> query($unitQry);

        if ($unitRes -> num_rows > 0) {
          while ($unitRow = $unitRes -> fetch_assoc()) {
            $sUnitSelect = $unitRow['PK_uom'] == $sUnit -> value ? 'selected' : '';
            $smallUnitSelect .= '<option value="' . $unitRow['PK_uom'] . '" ' . $sUnitSelect . '>' . $unitRow['uom_desc'] . '</option>';

            $bUnitSelect = $unitRow['PK_uom'] == $bUnit -> value ? 'selected' : '';
            $bigUnitSelect .= '<option value="' . $unitRow['PK_uom'] . '" ' . $bUnitSelect . '>' . $unitRow['uom_desc'] . '</option>';
          }
        }

        $itemCategSelect = '';
        $itemCategQry = 'SELECT * FROM item_categ';
        $itemCategRes = $connection -> query($itemCategQry);

        if ($itemCategRes -> num_rows > 0) {
          while ($itemCategRow = $itemCategRes -> fetch_assoc()) {
            $itemCategSelected = $itemCateg -> value == $itemCategRow['PK_item_categ'] ? 'selected' : '';
            $itemCategSelect .= '<option value="' . $itemCategRow['PK_item_categ'] . '" ' . $itemCategSelected . '>' . $itemCategRow['item_categ_desc'] . '</option>';
          }
        }

        $itemGrpSelect = '';
        $itemGrpQry = 'SELECT * FROM item_group';
        $itemGrpRes = $connection -> query($itemGrpQry);

        if ($itemGrpRes -> num_rows > 0) {
          while ($itemGrpRow = $itemGrpRes -> fetch_assoc()) {
            $itemGrpSelected = $itemGrp -> value == $itemGrpRow['PK_item_group'] ? 'selected' : '';
            $itemGrpSelect .= '<option value="' . $itemGrpRow['PK_item_group'] . '" ' . $itemGrpSelected . '>' . $itemGrpRow['item_group_desc'] . '</option>';
          }
        }

        $content['modal'] = modalize(
          '<div class="row">
            <div class="col-sm-12">
              <h2 class="header capitalize text-center">Item Code Request Item</h2>
              <p class="para-text text-center">Please fill the field with a valid information to continue.</p>
            </div>
            
            <div class="col-sm-12 item-code-req-item-mgmt">
              <form form-name="item-code-req-item-mgmt" action="../asset/core/ajax/item-code-verify-item-manage.php">
                <input type="text" name="recordId" hidden="hidden" value="' . $recordId -> value . '">
                <input type="text" name="itemRecId" hidden="hidden" value="' . $itemRecId -> value . '">
              
                <div class="row">
                  <div class="col-sm-6">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Item Code : </label>
                      <div class="form-group col-sm-12 ' . $itemCodeErr -> error_class . '">
                        <input name="itemCode" class="form-control" placeholder="Item Code" value="' . $itemCode -> value . '">
                        ' . $itemCodeErr -> error_icon . '
                        ' . $itemCodeErr -> error_text . '
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">  
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Item Description : </label>
                      <div class="form-group col-sm-12 ' . $itemDescErr -> error_class . '">
                        <input name="itemDesc" class="form-control" placeholder="Item Description" value="' . $itemDesc -> value . '">
                        ' . $itemDescErr -> error_icon . '
                        ' . $itemDescErr -> error_text . '
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-sm-4">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Small Unit : </label>
                      <div class="form-group col-sm-12 ' . $sUnitErr -> error_class . '">
                        <select class="form-control" name="sUnit"> 
                          <option value="">Select Small Unit</option>
                          ' . $smallUnitSelect . '
                        </select>
                        ' . $sUnitErr -> error_icon . '
                        ' . $sUnitErr -> error_text . '
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Big Unit : </label>
                      <div class="form-group col-sm-12 ' . $bUnitErr -> error_class . '">
                        <select class="form-control" name="bUnit"> 
                          <option value="">Select Big Unit</option>
                          ' . $bigUnitSelect . ' 
                        </select>
                        ' . $bUnitErr -> error_icon . '
                        ' . $bUnitErr -> error_text . '
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Conversion : </label>
                      <div class="form-group col-sm-12 ' . $convErr -> error_class . '">
                        <input name="conv" class="form-control" placeholder="Conversion" value="' . $conv -> value . '">
                        ' . $convErr -> error_icon . '
                        ' . $convErr -> error_text . '
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-sm-6">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Item Category : </label>
                      <div class="form-group col-sm-12 ' . $itemCategErr -> error_class . '">
                        <select class="form-control" name="itemCateg"> 
                          <option value="">Select Item Category</option>
                          ' . $itemCategSelect . ' 
                        </select>
                        ' . $itemCategErr -> error_icon . '
                        ' . $itemCategErr -> error_text . '
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Item Group : </label>
                      <div class="form-group col-sm-12 ' . $itemGrpErr -> error_class . '">
                        <select class="form-control" name="itemGrp"> 
                          <option value="">Select Item Group</option>
                          ' . $itemGrpSelect . ' 
                        </select>
                        ' . $itemGrpErr -> error_icon . '
                        ' . $itemGrpErr -> error_text . '
                      </div>
                    </div>
                  </div>
                </div>
                
              </form>
            </div>
          </div>', 
          array(
            "trasnType" => 'regular',
            "btnLbl" => 'Update'
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