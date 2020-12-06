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
    $recordId  = new form_validation($_POST['recordId'], 'str-int', 'Item Code ID', true);

    if ($recordId -> valid == 1) {
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

    if ($recordId -> valid == 1) {
      $sUnitQry = 'SELECT uom_desc FROM uom WHERE PK_uom = FK_sunit';
      $bUnitQry = 'SELECT uom_desc FROM uom WHERE PK_uom = FK_bunit';

      $itemCategQry = 'SELECT item_categ_desc FROM item_categ WHERE PK_item_categ = FK_item_categ';
      $itemGrpQry = 'SELECT item_group_desc FROM item_group WHERE PK_item_group = FK_item_group';
      $codePrefixQry = 'SELECT description FROM itemcode_prefix WHERE PK_itemcode_prefix = FK_itemcode_prefix';

      $recordRow;
      $transactQry = '
        SELECT 
          item_desc as item_desc
          , (' . $sUnitQry . ') as s_unit
          , (' . $bUnitQry . ') as b_unit
          , conv as conv
          , (' . $itemCategQry . ') as item_categ
          , (' . $itemGrpQry . ') as item_grp
          , item_code as item_code
          , isExisting as isExisting
          , (' . $codePrefixQry . ') as itemcode_prefix
          , unit_cost as unit_cost
        FROM item_code
        WHERE PK_item_code = "' . $recordId -> value . '"
      ';
      // die($transactQry);
      $recordRes = $connection -> query($transactQry);

      if ($recordRes -> num_rows > 0) {
        $recordRow = $recordRes -> fetch_assoc();

        $itemStat;
        if (!empty($recordRow['item_code'])) {
          if ($recordRow['isExisting'] == '0') {
            $itemStat = 'New Item';
          } else {
            $itemStat = 'Existing';
          }
        } else {
          $itemStat = 'TDB';
        }
        $itemCode = empty($recordRow['item_code']) ? 'TBD' : $recordRow['item_code'];

        $content['modal'] = modalize(
          '<div class="row">
            <div class="col-sm-12">
              <h2 class="header capitalize text-center">Item Code Record</h2>
              <p class="para-text text-center">Further information about the Item Code</p>
            </div>
            
            <div class="col-sm-12 item-code-req-item-mgmt">
              
              <div class="row">
                <div class="col-sm-6">
                  <div class="row">
                     <label for="" class="text-left control-label col-sm-12">Item Code : </label>
                    <div class="form-group col-sm-12">
                      <input name="itemCode" class="form-control" readonly value="' . $recordRow['item_code'] . '">
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Unit Cost : </label>
                    <div class="form-group col-sm-12">
                      <input name="unitCost" class="form-control" readonly value="' . $recordRow['unit_cost'] . '">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <label for="" class="text-left control-label col-sm-12">Item Description : </label>
                <div class="form-group col-sm-12">
                  <input name="itemDesc" class="form-control" readonly value="' . $recordRow['item_desc'] . '">
                </div>
              </div>
              
              <div class="row">
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Small Unit : </label>
                    <div class="form-group col-sm-12">
                      <input name="itemDesc" class="form-control" readonly value="' . $recordRow['s_unit'] . '">
                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Big Unit : </label>
                    <div class="form-group col-sm-12">
                      <input name="itemDesc" class="form-control" readonly value="' . $recordRow['b_unit'] . '">
                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Conversion : </label>
                    <div class="form-group col-sm-12">
                      <input name="conv" class="form-control" readonly value="' . $recordRow['conv'] . '">
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Item Code Prefix : </label>
                    <div class="form-group col-sm-12">
                      <input name="conv" class="form-control" readonly value="' . $recordRow['itemcode_prefix'] . '">
                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Item Group : </label>
                    <div class="form-group col-sm-12">
                      <input name="conv" class="form-control" readonly value="' . $recordRow['item_grp'] . '">
                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="row">
                    <label for="" class="text-left control-label col-sm-12">Item Category : </label>
                    <div class="form-group col-sm-12">
                      <input name="conv" class="form-control" readonly value="' . $recordRow['item_categ'] . '">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Close'
          ),
          'modal-lg'
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

  // Encode JSON File
  encode_json_file(array($success, $content));
?>