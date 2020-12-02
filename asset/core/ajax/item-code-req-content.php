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
        $filter .= strlen($filter) > 0 ? 'AND ' : 'WHERE ';
        $filter .= ' a.ris_no LIKE "%' . $risNo -> value . '%"';
      }

      if ($useDateRng -> value == 1) {
        $filter .= strlen($filter) > 0 ? 'AND ' : 'WHERE ';
        $filter .= 'a.date_requested BETWEEN "' . $dateRngMin -> value . '" AND "' . $dateRngMax -> value . '"';
      }

      if ($useIdRng -> value == 1) {
        $filter .= strlen($filter) > 0 ? 'AND ' : 'WHERE ';
        $filter .= 'a.pk_item_code_req BETWEEN "' . $idRngMin -> value . '" AND "' . $idRngMax -> value . '"';
      }

      if (!empty($checkedStatBox -> value)) {
        $tempVar = explode('-', $checkedStatBox -> value);
        $stats = '';

        for ($i = 0 ; $i < count($tempVar) ; $i++) {
          $stats .= strlen($stats) > 0 ? ',"' . $tempVar[$i] . '"' : '"' . $tempVar[$i] . '"';
        }

        $filter .= strlen($filter) > 0 ? 'AND ' : 'WHERE ';
        $filter .= 'a.FK_request_stat IN (SELECT sb.PK_status FROM status as sb WHERE stat_desc IN ('. $stats .') AND sb.type = "RQ")';
      }

      $transactQry = '
        SELECT (
          SELECT COUNT(sa.PK_item_code) FROM item_code as sa 
          WHERE sa.FK_item_code_req = a.PK_item_code_req
        ) as noOfItems
        , (SELECT sa.stat_desc FROM status as sa WHERE sa.PK_status =  a.FK_request_stat) as reqStat
        , (SELECT CONCAT(xx.fname, \' \', xx.lname) FROM sys_user AS xx WHERE xx.pk_sys_user = a.FK_requestor) AS requestedBy
        , IFNULL((SELECT CONCAT(xx.fname, \' \', xx.lname) FROM sys_user AS xx WHERE xx.pk_sys_user = a.FK_poster), "-") AS postedBy
        , IFNULL(DATE_FORMAT(a.date_posted, "%Y-%m-%d"), "-") AS datePosted
        , a.FK_verify_stat as verifStatId
        , a.*
        FROM item_code_req as a  
      ' . $filter . '
        ORDER BY a.date_requested DESC, a.PK_item_code_req DESC
      ';
      // die($transactQry);
      $recResult = $connection -> query($transactQry);
      
      if ($recResult -> num_rows > 0) {
        $content['totalRec'] = $recResult -> num_rows;
        while ($recRow = $recResult -> fetch_assoc()) {
          
          $mgmtButton = '';
          if ($recRow['reqStat'] == 'saved') {
            $mgmtButton = '
              <button class="btn btn-info transaction-btn" data-link="../asset/core/ajax/item-code-req-select.php" data-targe="modal-container" data-content="{
                  &quot;recordId&quot; : &quot;' . $recRow['pk_item_code_req'] . '&quot;
                }" trans-name="modal-rec" title="Edit Request"><i class="fas fa-pencil-alt"></i></button>

              <button class="btn btn-success transaction-btn" data-link="../asset/core/ajax/generic-warning-post.php" data-targe="modal-container" data-content="{
                  &quot;link&quot;        : &quot;../asset/core/ajax/item-code-req-post.php&quot;,
                  &quot;dataContent&quot; : {
                    &quot;recordId&quot;    : &quot;' . $recRow['pk_item_code_req'] . '&quot;
                  },
                  &quot;headerTitle&quot; : &quot;Item Code Request&quot;
                }" trans-name="modal-rec" title="Post Request"><i class="fas fa-thumbtack"></i></button>

              <button class="btn btn-danger transaction-btn" title="Cancel Request" data-link="../asset/core/ajax/generic-warning-cancel.php" data-target="modal-container" trans-name="modal-rec" data-content="{
                  &quot;link&quot;        : &quot;../asset/core/ajax/item-code-req-cancel.php&quot;,
                  &quot;dataContent&quot; : {
                    &quot;recordId&quot;    : &quot;' . $recRow['pk_item_code_req'] . '&quot;
                  },
                  &quot;headerTitle&quot; : &quot;Item Code Request&quot;
                }"><i class="fas fa-ban"></i></button>
            ';
          } else if ($recRow['reqStat'] == 'posted' && $recRow['verifStatId'] == '4') {
            $mgmtButton = '
              <button class="btn btn-info transaction-btn" data-link="../asset/core/ajax/item-code-req-view.php" 
                data-targe="modal-container" data-content="{
                  &quot;recordId&quot; : &quot;' . $recRow['pk_item_code_req'] . '&quot;
                }" trans-name="modal-rec" title="View Request"><i class="fas fa-file"></i></button>

              <button class="btn btn-danger transaction-btn" data-link="../asset/core/ajax/generic-warning-unpost.php" data-targe="modal-container" data-content="{
                  &quot;link&quot;        : &quot;../asset/core/ajax/item-code-req-unpost.php&quot;,
                  &quot;dataContent&quot; : {
                    &quot;recordId&quot;    : &quot;' . $recRow['pk_item_code_req'] . '&quot;
                  },
                  &quot;headerTitle&quot; : &quot;Item Code Request&quot;
                }" trans-name="modal-rec" title="Void Request"><i class="fas fa-times"></i></button>
            ';
          } else {
            $mgmtButton = '
              <button class="btn btn-info transaction-btn" data-link="../asset/core/ajax/item-code-req-view.php" 
                data-targe="modal-container" data-content="{
                  &quot;recordId&quot; : &quot;' . $recRow['pk_item_code_req'] . '&quot;
                }" trans-name="modal-rec" title="View Request"><i class="fas fa-file"></i></button>
            ';
          }

          $classType;

          if ($recRow['reqStat'] == 'saved') {
            $classType = 'rec-pending';
          } else if ($recRow['reqStat'] == 'posted') {
            $classType = 'rec-posted';
          } else {
            $classType = 'rec-cancelled';
          }

          $content['recContent'][] = '
            <tr class="' . $classType . '">
              <td>' . $recRow['pk_item_code_req'] . '</td>
              <td>' . date('Y-m-d', strtotime($recRow['date_requested'])) . '</td>
              <td>' . $recRow['ris_no'] . '</td>             
              <td>' . $recRow['noOfItems'] . ' Item(s)</td>
              <td>' . ucfirst($recRow['reqStat']) . '</td>
              <td class="uppercase">' . $recRow['requestedBy'] . '</td>
              <td class="uppercase">' . $recRow['datePosted'] . '</td>
              <td class="uppercase">' . $recRow['postedBy'] . '</td>
              <td>
                <form action="item-code-req-items.php" method="post" class="d-inline">
                  <input type="text" name="requestId" hidden value="' . $recRow['pk_item_code_req'] . '">
                  <button type="submit" class="btn btn-light" title="View Items"><i class="fas fa-eye"></i></button>
                </form>
                ' . $mgmtButton . '
              </td>
            </tr>
          ';
        }
      } else {
        $content['totalRec'] = 1;
        $content['recContent'][] = '
          <tr>
            <td colspan="9" class="text-center">No Record Found</td>
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