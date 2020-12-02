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
        if ($recordId -> value != 'new-rec') {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Invalid Item Record ID';
        }
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
      $recRow = '';
      if (is_numeric($recordId -> value)) {

        $transactQry = '
          SELECT * FROM item_code 
          WHERE PK_item_code = "' . $recordId -> value . '" AND FK_item_code_req = "' . $itemRecId -> value . '"
        ';
        $recordRes = $connection -> query($transactQry);

        if ($recordRes -> num_rows > 0) {
          $recRow = $recordRes -> fetch_assoc();
        }
      } else {
        $recRow = array(
          "item_desc" => '',
          "FK_sunit" => '',
          "FK_bunit" => '',
          "conv" => '',
          "FK_item_categ" => '',
          "FK_item_group" => '',
          "FK_itemcode_prefix" => ''
        );
      }

      $bigUnitSelect = $smallUnitSelect = '';
      $unitQry = 'SELECT * FROM uom';
      $unitRes = $connection -> query($unitQry);

      if ($unitRes -> num_rows > 0) {
        while ($unitRow = $unitRes -> fetch_assoc()) {
          $sUnitSelect = $unitRow['PK_uom'] == $recRow['FK_sunit'] ? 'selected' : '';
          $smallUnitSelect .= '<option value="' . $unitRow['PK_uom'] . '" ' . $sUnitSelect . '>' . $unitRow['uom_desc'] . '</option>';

          $bUnitSelect = $unitRow['PK_uom'] == $recRow['FK_bunit'] ? 'selected' : '';
          $bigUnitSelect .= '<option value="' . $unitRow['PK_uom'] . '" ' . $bUnitSelect . '>' . $unitRow['uom_desc'] . '</option>';
        }
      }

      $itemCategSelect = '';
      $itemCategQry = 'SELECT * FROM item_categ ORDER BY item_categ_desc';
      $itemCategRes = $connection -> query($itemCategQry);

      if ($itemCategRes -> num_rows > 0) {
        while ($itemCategRow = $itemCategRes -> fetch_assoc()) {
          $itemCategSelected = $recRow['FK_item_categ'] == $itemCategRow['PK_item_categ'] ? 'selected' : '';
          $itemCategSelect .= '<option value="' . $itemCategRow['PK_item_categ'] . '" ' . $itemCategSelected . '>' . $itemCategRow['item_categ_desc'] . '</option>';
        }
      }

      $itemGrpSelect = '';
      $itemGrpQry = 'SELECT * FROM item_group ORDER BY item_group_desc';
      $itemGrpRes = $connection -> query($itemGrpQry);

      if ($itemGrpRes -> num_rows > 0) {
        while ($itemGrpRow = $itemGrpRes -> fetch_assoc()) {
          $itemGrpSelected = $recRow['FK_item_group'] == $itemGrpRow['PK_item_group'] ? 'selected' : '';
          $itemGrpSelect .= '<option value="' . $itemGrpRow['PK_item_group'] . '" ' . $itemGrpSelected . '>' . $itemGrpRow['item_group_desc'] . '</option>';
        }
      }

      $itemcodePrefixSelect = '';
      $itemcodePrefixQry = 'SELECT * FROM itemcode_prefix ORDER BY description';
      $itemcodePrefixRes = $connection -> query($itemcodePrefixQry);

      if ($itemcodePrefixRes -> num_rows > 0) {
        while ($itemcodePrefixRow = $itemcodePrefixRes -> fetch_assoc()) {
          $isSelected = $recRow['FK_itemcode_prefix'] == $itemcodePrefixRow['PK_itemcode_prefix'] ? 'selected' : '';
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
                <div class="form-group col-sm-12">
                  <input name="itemDesc" class="form-control" placeholder="Item Description" value="' . $recRow['item_desc'] . '">
                </div>
              </div>
              
              <div class="row">
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Small Unit : </label>
                    <div class="form-group col-sm-12">
                      <select class="form-control" name="sUnit">
                        <option value="">Select Small Unit</option>
                        ' . $smallUnitSelect . '
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Big Unit : </label>
                    <div class="form-group col-sm-12">
                      <select class="form-control" name="bUnit">
                        <option value="">Select Big Unit</option>
                        ' . $bigUnitSelect . ' </select>
                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Conversion : </label>
                    <div class="form-group col-sm-12">
                      <input name="conv" class="form-control" placeholder="Conversion" value="' . $recRow['conv'] . '">
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Item Code Prefix : </label>
                    <div class="form-group col-sm-12">
                      <select class="form-control" name="prefixId">
                        <option value="">Select Item Code Prefix</option>
                        ' . $itemcodePrefixSelect . ' </select>
                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Item Category : </label>
                    <div class="form-group col-sm-12">
                      <select class="form-control" name="itemCateg">
                        <option value="">Select Item Category</option>
                        ' . $itemCategSelect . ' </select>
                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Item Group : </label>
                    <div class="form-group col-sm-12">
                      <select class="form-control" name="itemGrp">
                        <option value="">Select Item Group</option>
                        ' . $itemGrpSelect . ' </select>
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