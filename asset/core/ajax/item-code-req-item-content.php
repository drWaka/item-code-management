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
    "totalRec" => 0,
    "recContent" => []
  );

  if (isset($_POST['itemDesc']) && isset($_POST['itemRecId'])) {
    $itemDesc  = new form_validation($_POST['itemDesc'], 'str-int', 'RIS Number', false);
    $itemRecId = new form_validation($_POST['itemRecId'], 'int', 'Item Code Request Id', true);

    if ($itemDesc -> valid == 1 && $itemRecId -> valid == 1) {
      if (is_numeric($itemRecId -> value)) {
        $transactQry = 'SELECT * FROM item_code_req WHERE PK_item_code_req = "' . $itemRecId -> value . '"';
        $transactRes = $connection -> query($transactQry);

        if (!($transactRes -> num_rows > 0)) {
          $itemRecId -> valid = 1;
          $itemRecId -> err_msg = 'Invalid Item Code Doesn\'t Exist';
        }
      } else {
        $itemRecId -> valid = 1;
        $itemRecId -> err_msg = 'Invalid Item Code Record ID';
      }
    }

    if ($itemDesc -> valid == 1 && $itemRecId -> valid == 1) {
      $filter = '';

      if (!empty($itemDesc -> value)) {
        $filter .= 'AND a.item_desc LIKE "%' . $itemDesc -> value . '%"';
      }

      $transactQry = '
        SELECT a.item_code as item_code, a.item_desc as item_desc, c.item_categ_desc as item_categ, d.item_group_desc as item_group, a.isExisting as isExisting, a.PK_item_code as PK_item_code, a.FK_item_code_req as FK_item_code_req, e.stat_desc as status
        FROM item_code as a INNER JOIN item_code_req as b ON a.FK_item_code_req = b.PK_item_code_req
        INNER JOIN item_categ as c ON c.PK_item_categ = a.FK_item_categ
        INNER JOIN item_group as d ON d.PK_item_group = a.FK_item_group
        INNER JOIN status as e ON b.FK_request_stat = e.PK_status
        WHERE a.FK_item_code_req = "' . $itemRecId -> value . '"' . $filter . '
        ORDER BY a.item_desc ASC
      ';
      // die($transactQry);
      $recResult = $connection -> query($transactQry);
      
      if ($recResult -> num_rows > 0) {
        $content['totalRec'] = $recResult -> num_rows;
        while ($recRow = $recResult -> fetch_assoc()) {

          $itemCode = !empty($recRow['item_code']) ? $recRow['item_code'] : 'TDB';

          $itemCodeStat;
          if (!empty($recRow['item_code'])) {
            if ($recRow['isExisting'] == '0') {
              $itemCodeStat = 'New Item';
            } else {
              $itemCodeStat = 'Existing';
            }
          } else {
            $itemCodeStat = 'TDB';
          }

          $hidden = '';
          if ($recRow['status'] != 'saved') {
            $hidden = 'disabled';
          }

          $content['recContent'][] = '
            <tr>
              <td>' . $itemCode . '</td>
              <td class="uppercase">' . $recRow['item_desc'] . '</td>
              <td>' . $recRow['item_categ'] . '</td>
              <td>' . $recRow['item_group'] . '</td>
              <td>' . $itemCodeStat . '</td>
              <td>
                <button type="submit" class="btn btn-light transaction-btn" data title="View Item" data-link="../asset/core/ajax/item-code-req-item-view.php" data-content="{
                    &quot;recordId&quot; : &quot;' . $recRow['PK_item_code'] . '&quot;
                  }" data-container="modal-container" trans-name="modal-rec"><i class="fas fa-eye"></i></button>

                <button class="btn btn-info transaction-btn" data-link="../asset/core/ajax/item-code-req-item-select.php" data-targe="modal-container" data-content="{
                    &quot;recordId&quot; : &quot;' . $recRow['PK_item_code'] . '&quot;,
                    &quot;itemRecId&quot; : &quot;' . $recRow['FK_item_code_req'] . '&quot;
                  }" trans-name="modal-rec" title="Edit Item" ' . $hidden . '><i class="fas fa-pencil-alt"></i></button>
                
                <button class="btn btn-danger transaction-btn" title="Delete" data-link="../asset/core/ajax/generic-warning-delete.php" data-target="modal-container" trans-name="modal-rec" data-content="{
                    &quot;link&quot;        : &quot;../asset/core/ajax/item-code-req-item-delete.php&quot;,
                    &quot;dataContent&quot; : {
                      &quot;recordId&quot;    : &quot;' . $recRow['PK_item_code'] . '&quot;,
                      &quot;itemRecId&quot;    : &quot;' . $itemRecId -> value  . '&quot;
                    },
                    &quot;headerTitle&quot; : &quot;Item Code Request&quot;
                  }" ' . $hidden . '><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          ';
        }
      } else {
        $content['totalRec'] = 1;
        $content['recContent'][] = '
          <tr>
            <td colspan="6" class="text-center">No Record Found</td>
          </tr>
        ';
      }
    } else {
      if ($itemDesc -> valid == 0) {
        $success = 'failed';
        $content['modal'] = modalize(
          '<div class="row text-center">
            <h2 class="header capitalize col-12">Error Encountered</h2>
            <p class="para-text col-12">Error Details: ' . $itemDesc -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else if ($itemRecId -> valid == 0) {
        $success = 'failed';
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
    $success = 'failed';
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