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

  if (isset($_POST['userName'])) {
    $userName = new form_validation($_POST['userName'], 'str-int', 'User Name', false);

    if ($userName -> valid == 1) {
      $filter = '';

      if (!empty($userName -> value)) {
        $filter .= 'WHERE CONCAT(a.lname, ", ", a.fname) LIKE "%' . $userName -> value . '%"';
      }

      $transactQry = '
        SELECT CONCAT(a.lname, ", ", a.fname) as name, b.user_id as user_id, c.user_type_desc as user_type,
        b.isActive as isActive, a.update_date as update_date, a.PK_sys_user as PK_sys_user
        FROM sys_user as a INNER JOIN user_acc as b ON a.PK_sys_user = b.FK_sys_user
        INNER JOIN user_type as c ON b.FK_user_type = c.PK_user_type
      ' . $filter . '
        ORDER BY a.lname ASC, a.fname ASC
      ';
      // die($transactQry);
      $recResult = $connection -> query($transactQry);
      
      if ($recResult -> num_rows > 0) {
        $content['totalRec'] = $recResult -> num_rows;
        while ($recRow = $recResult -> fetch_assoc()) {
          $accStat = $recRow['isActive'] == '1' ? 'Active' : 'Disabled';
          $content['recContent'][] = '
            <tr>
              <td>' . $recRow['user_id'] . '</td>
              <td class="capitalize">' . $recRow['name'] . '</td>
              <td>' . date('F d, Y', strtotime($recRow['update_date'])) . '</td>
              <td>' . ucfirst($recRow['user_type']) . '</td>
              <td>' . $accStat . '</td>
              <td>
                <button class="btn btn-success transaction-btn" title="Update Record" data-link="../asset/core/ajax/user-mgmt-select.php" data-target="modal-container" trans-name="modal-rec" data-content="{
                    &quot;userId&quot; : &quot;' . $recRow['PK_sys_user'] . '&quot;
                  }"><i class="fas fa-pencil-alt"></i></button>
              </td>
              <td>
                <button class="btn btn-info transaction-btn" title="User Access" data-link="../asset/core/ajax/user-access-mgmt-select.php" data-target="modal-container" trans-name="modal-rec" data-content="{
                    &quot;userId&quot; : &quot;' . $recRow['PK_sys_user'] . '&quot;
                  }"><i class="fas fa-user-plus"></i></button>
              </td>
            </tr>
          ';
        }
      } else {
        $content['totalRec'] = 1;
        $content['recContent'][] = '
          <tr>
            <td colspan="7" class="text-center">No Record Found</td>
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