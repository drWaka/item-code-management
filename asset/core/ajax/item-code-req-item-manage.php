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
    isset($_POST['itemCateg']) && isset($_POST['itemGrp']) && isset($_POST['prefixId'])
  ) {
    $recordId  = new form_validation($_POST['recordId'], 'str-int', 'Item Code ID', true);
    $itemRecId = new form_validation($_POST['itemRecId'], 'int', 'Item Code Request ID', true);
    $itemDesc  = new form_validation($_POST['itemDesc'], 'str-int', 'Item Description', true);
    $sUnit     = new form_validation($_POST['sUnit'], 'int', 'Small Unit', true);
    $bUnit     = new form_validation($_POST['bUnit'], 'int', 'Big Unit', true);
    $conv      = new form_validation($_POST['conv'], 'int', 'Conversion', true);
    $itemCateg = new form_validation($_POST['itemCateg'], 'int', 'Item Category', true);
    $itemGrp   = new form_validation($_POST['itemGrp'], 'int', 'Item Group', true);
    $prefixId  = new form_validation($_POST['prefixId'], 'int', 'Item Code Prefix', true);

    // Variable that will handle the item details if it exists at Bizbox DB
     $itemExistBB = array(
      "exists" => 0,
      "itemCode" => '',
      "itemDesc" => ''
    );
    if (
      $recordId -> valid == 1 && $itemRecId -> valid == 1 && $itemDesc -> valid == 1 && 
      $sUnit -> valid == 1 && $bUnit -> valid == 1 && $conv -> valid == 1 && 
      $itemCateg -> valid == 1 && $itemGrp -> valid == 1 && $prefixId -> valid == 1
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
        if ($recordId -> value != 'new-rec') {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Invalid Item Record ID';
        }
      }
    }

    if (
      $recordId -> valid == 1 && $itemRecId -> valid == 1 && $itemDesc -> valid == 1 && 
      $sUnit -> valid == 1 && $bUnit -> valid == 1 && $conv -> valid == 1 && 
      $itemCateg -> valid == 1 && $itemGrp -> valid == 1 && $prefixId -> valid == 1
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
      $itemCateg -> valid == 1 && $itemGrp -> valid == 1 && $prefixId -> valid == 1
    ) {
      // Verify Item Description
      if (strlen(htmlspecialchars_decode($itemDesc -> value)) > 40) {
        $itemDesc -> valid = 0;
        $itemDesc -> err_msg = 'Item Description reached maximum of 40 characters';
      } else {
        $checkItemDesc = '
          SELECT * FROM item_code 
          WHERE item_desc = "' . $itemDesc -> value . '" AND PK_item_code != "' . $recordId -> value . '"
            AND FK_item_code_req NOT IN (
              SELECT FK_item_code_req FROM item_code_req WHERE FK_request_stat NOT IN (
                SELECT PK_status FROM status WHERE stat_desc = "cancelled"
              )
            )
        ';
        // die($checkItemDesc);
        $checkItemDescRes = $connection -> query($checkItemDesc);

        if ($checkItemDescRes -> num_rows > 0) {
          $itemDesc -> valid = 0;
          $itemDesc -> err_msg = 'Item Already Exists';
        } else {
          // Perform BizBox Verification Here
          $checkItemDescBB = 'SELECT * FROM iwItems WHERE itemdesc = \'' . str_replace("'", "''", $itemDesc -> value) . '\' AND isActive <> 0';
          $checkItemDescBBRes = $mssqlConn -> query($checkItemDescBB);

          $recRow = $checkItemDescBBRes->fetch(PDO::FETCH_ASSOC);
          if (is_array($recRow)) {
            if (count($recRow) > 0) {
              $recordId -> valid = 0;
              $itemExistBB['exists'] = 1;
              $itemExistBB['itemCode'] = $recRow['PK_iwItems'];
              $itemExistBB['itemDesc'] = $recRow['itemdesc'];
            }
          }
        }
      }
    }

    if (
      $recordId -> valid == 1 && $itemRecId -> valid == 1 && $itemDesc -> valid == 1 && 
      $sUnit -> valid == 1 && $bUnit -> valid == 1 && $conv -> valid == 1 && 
      $itemCateg -> valid == 1 && $itemGrp -> valid == 1 && $prefixId -> valid == 1
    ) {
      $modalLbl = array(
        "present" => '',
        "past" => '',
        "future" => ''
      );
      $transactQry = '';

      if ($recordId -> value == 'new-rec') {
        $modalLbl = array(
          "present" => 'Registration',
          "past" => 'Registered',
          "future" => 'Register'
        );
        $transactQry = '
          INSERT INTO item_code (
            item_desc, FK_sunit, FK_bunit, 
            conv, FK_item_categ, FK_item_group, 
            FK_item_code_req, FK_itemcode_prefix
          ) VALUES (
            "' . $itemDesc -> value . '", "' . $sUnit -> value . '", "' . $bUnit -> value . '",
            "' . $conv -> value . '", "' . $itemCateg -> value . '", "' . $itemGrp -> value . '",
            "' . $itemRecId -> value . '", "' . $prefixId -> value . '"
          )
        ';
      } else {
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
          FK_itemcode_prefix = "' . $prefixId -> value . '"
          WHERE PK_item_code = "' . $recordId -> value . '"
        ';
      }

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
            "link" => '../asset/core/ajax/item-code-req-item-content.php',
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
        if ($itemExistBB['exists'] == 1) {
          $content['modal'] = modalize( 
            '<div class="row text-center">
                <h2 class="header capitalize col-12">Item Management</h2>
                <p class="para-text col-12">Item Already Exists at Bizbox Database.</p>
                
                <div class="row">
                  <div class="col-12">
                    <b>Item Code</b> : ' . $itemExistBB['itemCode'] . '
                  </div>
                  <div class="col-12">
                    <b>Item Description</b> : ' . $itemExistBB['itemDesc'] . '
                  </div>
                </div>
            </div>', 
            array(
              "trasnType" => 'error',
              "btnLbl" => 'OK'
            )
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
        $itemDescErr  = new error_handler($itemDesc -> err_msg);
        $sUnitErr     = new error_handler($sUnit -> err_msg);
        $bUnitErr     = new error_handler($bUnit -> err_msg);
        $convErr      = new error_handler($conv -> err_msg);
        $itemCategErr = new error_handler($itemCateg -> err_msg);
        $itemGrpErr   = new error_handler($itemGrp -> err_msg);
        $prefixIdErr  = new error_handler($prefixId -> err_msg);

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
        $itemCategQry = 'SELECT * FROM item_categ ORDER BY item_categ_desc';
        $itemCategRes = $connection -> query($itemCategQry);

        if ($itemCategRes -> num_rows > 0) {
          while ($itemCategRow = $itemCategRes -> fetch_assoc()) {
            $itemCategSelected = $itemCateg -> value == $itemCategRow['PK_item_categ'] ? 'selected' : '';
            $itemCategSelect .= '<option value="' . $itemCategRow['PK_item_categ'] . '" ' . $itemCategSelected . '>' . $itemCategRow['item_categ_desc'] . '</option>';
          }
        }

        $itemGrpSelect = '';
        $itemGrpQry = 'SELECT * FROM item_group ORDER BY item_group_desc';
        $itemGrpRes = $connection -> query($itemGrpQry);

        if ($itemGrpRes -> num_rows > 0) {
          while ($itemGrpRow = $itemGrpRes -> fetch_assoc()) {
            $itemGrpSelected = $itemGrp -> value == $itemGrpRow['PK_item_group'] ? 'selected' : '';
            $itemGrpSelect .= '<option value="' . $itemGrpRow['PK_item_group'] . '" ' . $itemGrpSelected . '>' . $itemGrpRow['item_group_desc'] . '</option>';
          }
        }

        $itemcodePrefixSelect = '';
        $itemcodePrefixQry = 'SELECT * FROM itemcode_prefix ORDER BY description';
        $itemcodePrefixRes = $connection -> query($itemcodePrefixQry);

        if ($itemcodePrefixRes -> num_rows > 0) {
          while ($itemcodePrefixRow = $itemcodePrefixRes -> fetch_assoc()) {
            $isSelected = $prefixId -> value == $itemcodePrefixRow['PK_itemcode_prefix'] ? 'selected' : '';
            $itemcodePrefixSelect .= '<option value="' . $itemcodePrefixRow['PK_itemcode_prefix'] . '" ' . $isSelected . '>' . $itemcodePrefixRow['description'] . '</option>';
          }
        }

        $content['modal'] = modalize(
          '<div class="row">
            <div class="col-sm-12">
              <h2 class="header capitalize text-center">Item Code Request Item</h2>
              <p class="para-text text-center">Please fill the field with a valid information to continue.</p>
            </div>
            
            <div class="col-sm-12 item-code-req-item-mgmt">
              <form form-name="item-code-req-item-mgmt" action="../asset/core/ajax/item-code-req-item-manage.php">
                <input type="text" name="recordId" hidden="hidden" value="' . $recordId -> value . '">
                <input type="text" name="itemRecId" hidden="hidden" value="' . $itemRecId -> value . '">
              
                <div class="row">
                  <label for="" class="text-left control-label col-sm-12">Item Description : </label>
                  <div class="form-group col-sm-12 ' . $itemDescErr -> error_class . '">
                    <input name="itemDesc" class="form-control" placeholder="Item Description" value="' . $itemDesc -> value . '">
                    ' . $itemDescErr -> error_icon . '
                    ' . $itemDescErr -> error_text . '
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
                  <div class="col-sm-4">
                    <div class="row">
                      <label for="" class="text-left control-label col-sm-12">Item Code Prefix : </label>
                      <div class="form-group col-sm-12  ' . $prefixIdErr -> error_class . '">
                        <select class="form-control" name="prefixId">
                          <option value="">Select Item Code Prefix</option>
                          ' . $itemcodePrefixSelect . ' </select>
                          ' . $prefixIdErr -> error_class . '
                          ' . $prefixIdErr -> error_class . '
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-4">
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
                  <div class="col-sm-4">
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