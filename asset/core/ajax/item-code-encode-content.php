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

  if (
    isset($_POST['risNo']) && isset($_POST['useDateRng']) && isset($_POST['dateRngMin']) 
    && isset($_POST['dateRngMax']) && isset($_POST['useIdRng']) && isset($_POST['idRngMin']) 
    && isset($_POST['idRngMax']) && isset($_POST['checkedStatBox'])
  ) {
    $risNo          = new form_validation($_POST['risNo'], 'str-int', 'RIS Number', false);
    $useDateRng     = new form_validation($_POST['useDateRng'], 'int', 'Use Date Range', true);
    $dateRngMin     = new form_validation($_POST['dateRngMin'], 'date', 'Date Range Min Val', true);
    $dateRngMax     = new form_validation($_POST['dateRngMax'], 'date', 'Date Range Max Val', true);
    $useIdRng       = new form_validation($_POST['useIdRng'], 'str-int', 'Use ID Range', true);
    $idRngMin       = new form_validation($_POST['idRngMin'], 'int', 'ID Range Min Val', true);
    $idRngMax       = new form_validation($_POST['idRngMax'], 'int', 'ID Range Max Val', true);
    $checkedStatBox = new form_validation($_POST['checkedStatBox'], 'str-int', 'Status', true);

    if (
      $risNo -> valid == 1 && $useDateRng -> valid == 1 && $dateRngMin -> valid == 1 
      && $dateRngMax -> valid == 1 && $useIdRng -> valid == 1 && $idRngMin -> valid == 1 
      && $idRngMax -> valid == 1 && $checkedStatBox -> valid == 1
    ) {
      $filter = '';

      if (!empty($risNo -> value)) {
        $filter .= 'AND a.ris_no LIKE "%' . $risNo -> value . '%"';
      }

      if ($useDateRng -> value == 1) {
        $filter .= 'AND a.date_requested BETWEEN "' . $dateRngMin -> value . '" AND "' . $dateRngMax -> value . '"';
      }

      if ($useIdRng -> value == 1) {
        $filter .= 'AND a.pk_item_code_req BETWEEN "' . $idRngMin -> value . '" AND "' . $idRngMax -> value . '"';
      }

      if (!empty($checkedStatBox -> value)) {
        $tempVar = explode('-', $checkedStatBox -> value);
        $stats = '';

        for ($i = 0 ; $i < count($tempVar) ; $i++) {
          $stats .= strlen($stats) > 0 ? ',"' . $tempVar[$i] . '"' : '"' . $tempVar[$i] . '"';
        }

        $filter .= 'AND a.FK_encode_stat IN (SELECT sb.PK_status FROM status as sb WHERE stat_desc IN ('. $stats .') AND sb.type = "EN")';
      }

      $transactQry = '
        SELECT (
          SELECT COUNT(sa.PK_item_code) FROM item_code as sa 
          WHERE sa.FK_item_code_req = a.PK_item_code_req
        ) as noOfItems, (
          SELECT sa.stat_desc FROM status as sa 
          WHERE sa.PK_status =  a.FK_encode_stat
        ) as encodeStat, (
          SELECT CONCAT(sc.lname, ", " , sc.fname) FROM sys_user as sc
          WHERE sc.pk_sys_user = a.FK_requestor
        ) as requestor, (
          SELECT CONCAT(sc.lname, ", " , sc.fname) FROM sys_user as sc
          WHERE sc.pk_sys_user = a.FK_encoder
        ) as encoder, a.*
        FROM item_code_req as a  
        WHERE a.FK_request_stat = (
          SELECT sc.PK_status FROM status as sc WHERE sc.stat_desc = "posted"
        ) AND a.FK_verify_stat = (
          SELECT sc.PK_status FROM status as sc WHERE sc.stat_desc = "verified"
        )
      ' . $filter . '
        ORDER BY a.date_requested DESC, a.PK_item_code_req DESC
      ';
      // die($transactQry);
      $recResult = $connection -> query($transactQry);
      
      if ($recResult -> num_rows > 0) {
        $content['totalRec'] = $recResult -> num_rows;
        while ($recRow = $recResult -> fetch_assoc()) {
          $endcodeUnencodeBtn = '';
          if ($recRow['encodeStat'] == 'encoded') {
            $endcodeUnencodeBtn = '
              <button 
                class="btn btn-danger transaction-btn" 
                title="Unencode Request" 
                data-link="../asset/core/ajax/generic-warning-encode.php" 
                data-target="modal-container" 
                trans-name="modal-rec" 
                data-content="{
                  &quot;link&quot;        : &quot;../asset/core/ajax/item-code-encode-unencode.php&quot;,
                  &quot;dataContent&quot; : {
                    &quot;recordId&quot;    : &quot;' . $recRow['pk_item_code_req'] . '&quot;
                  },
                  &quot;headerTitle&quot; : &quot;Item Code Request&quot;,
                  &quot;transactionType&quot; : &quot;unencode&quot;
                }"><i class="fas fa-times"></i></button>
            ';
          } else {
            $endcodeUnencodeBtn = '
              <button 
                class="btn btn-success transaction-btn" 
                title="Encode Request" 
                data-link="../asset/core/ajax/generic-warning-encode.php" 
                data-target="modal-container" 
                trans-name="modal-rec" 
                data-content="{
                  &quot;link&quot;        : &quot;../asset/core/ajax/item-code-encode-encode.php&quot;,
                  &quot;dataContent&quot; : {
                    &quot;recordId&quot;    : &quot;' . $recRow['pk_item_code_req'] . '&quot;
                  },
                  &quot;headerTitle&quot; : &quot;Item Code Request&quot;,
                  &quot;transactionType&quot; : &quot;encode&quot;
                }"><i class="fas fa-check"></i></button>
            ';
          }

          $classType;
          if ($recRow['encodeStat'] == 'pending') {
            $classType = 'rec-pending';
          } else {
            $classType = 'rec-posted';
          }

          $itemEncoder = !empty($recRow['encoder']) ? $recRow['encoder'] : 'TBD';
          $hideEncodeBtn = strtolower($_SESSION['userType']) == 'requestor' ? 'hide' : '';
          $content['recContent'][] = '
            <tr class="' . $classType . '">
              <td>#' . $recRow['pk_item_code_req'] . '</td>
              <td>' . date('F d, Y', strtotime($recRow['date_requested'])) . '</td>
              <td>#' . $recRow['ris_no'] . '</td>
              <td>' . $recRow['noOfItems'] . ' Item(s)</td>
              <td>' . ucfirst($recRow['encodeStat']) . '</td>
              <td class="capitalize">' . $recRow['requestor'] . '</td>
              <td class="capitalize">' . $itemEncoder . '</td>
              <td>
                <form action="item-code-encode-items.php" method="post" class="d-inline">
                  <input type="text" name="requestId" hidden value="' . $recRow['pk_item_code_req'] . '">
                  <button type="submit" class="btn btn-light" title="View Items"><i class="fas fa-eye"></i></button>
                </form>

                <button class="btn btn-info transaction-btn" data-link="../asset/core/ajax/item-code-req-view.php" 
                data-targe="modal-container" data-content="{
                  &quot;recordId&quot; : &quot;' . $recRow['pk_item_code_req'] . '&quot;
                }" trans-name="modal-rec" title="View Request"><i class="fas fa-file"></i></button>
                ' . $endcodeUnencodeBtn . '
              </td>
            </tr>
          ';
        }
      } else {
        $content['totalRec'] = 1;
        $content['recContent'][] = '
          <tr>
            <td colspan="8" class="text-center">No Record Found</td>
          </tr>
        ';
      }
    } else {
      if ($risNo -> valid == 0) {
        $success = 'failed';
        $content['modal'] = modalize(
          '<div class="row text-center">
            <h2 class="header capitalize col-12">Error Encountered</h2>
            <p class="para-text col-12">Error Details: ' . $risNo -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else if ($useDateRng -> valid == 0) {
        $success = 'failed';
        $content['modal'] = modalize(
          '<div class="row text-center">
            <h2 class="header capitalize col-12">Error Encountered</h2>
            <p class="para-text col-12">Error Details: ' . $useDateRng -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else if ($dateRngMin -> valid == 0) {
        $success = 'failed';
        $content['modal'] = modalize(
          '<div class="row text-center">
            <h2 class="header capitalize col-12">Error Encountered</h2>
            <p class="para-text col-12">Error Details: ' . $dateRngMin -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else if ($dateRngMax -> valid == 0) {
        $success = 'failed';
        $content['modal'] = modalize(
          '<div class="row text-center">
            <h2 class="header capitalize col-12">Error Encountered</h2>
            <p class="para-text col-12">Error Details: ' . $dateRngMax -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else if ($useIdRng -> valid == 0) {
        $success = 'failed';
        $content['modal'] = modalize(
          '<div class="row text-center">
            <h2 class="header capitalize col-12">Error Encountered</h2>
            <p class="para-text col-12">Error Details: ' . $useIdRng -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else if ($idRngMin -> valid == 0) {
        $success = 'failed';
        $content['modal'] = modalize(
          '<div class="row text-center">
            <h2 class="header capitalize col-12">Error Encountered</h2>
            <p class="para-text col-12">Error Details: ' . $idRngMin -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else if ($idRngMax -> valid == 0) {
        $success = 'failed';
        $content['modal'] = modalize(
          '<div class="row text-center">
            <h2 class="header capitalize col-12">Error Encountered</h2>
            <p class="para-text col-12">Error Details: ' . $idRngMax -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else if ($checkedStatBox -> valid == 0) {
        $success = 'failed';
        $content['modal'] = modalize(
          '<div class="row text-center">
            <h2 class="header capitalize col-12">Error Encountered</h2>
            <p class="para-text col-12">Error Details: ' . $checkedStatBox -> err_msg . '</p>
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