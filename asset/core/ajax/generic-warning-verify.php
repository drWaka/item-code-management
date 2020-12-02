<?php
  include '../php/connection.php';
  include '../php/server-side-validation.php';
  include '../php/generic-functions.php';

  // JSON Variables
  $success = 'success';
  $content = array(
    "modal" => ''
  );

  if (isset($_POST['link']) && isset($_POST['dataContent']) && isset($_POST['headerTitle'])) {
    $link = new form_validation($_POST['link'], 'str-int', 'File Link', true);
    $dataContent = new form_validation(json_encode($_POST['dataContent']), 'str-int', 'Data Content', true);
    $headerTitle = new form_validation($_POST['headerTitle'], 'str-int', 'Data Content', true);

    if ($link -> valid == 1 && $dataContent -> valid == 1 && $headerTitle -> valid == 1) {
      $content['modal'] = modalize( 
        '<div class="row text-center">
            <h2 class="header capitalize col-12">' . $headerTitle -> value . ' Record Verify</h2>
            <p class="para-text col-12">Do you really want to verify this record?</p>
        </div>', 
        array(
          "trasnType" => 'dialog',
          "btnLbl" => 'Yes',
          "btnLblClose" => 'No',
          "container" => 'modal-container',
          "link" => $link -> value,
          "transName" => 'modal-rec',
          "content" => $dataContent -> value
        )
      );
    } else {
      if ($link -> valid == 0) {
        $content['modal'] = modalize( 
          '<div class="row text-center">
              <h2 class="header capitalize col-12">Error Encountered</h2>
              <p class="para-text col-12">Error Details: ' . $link -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      } else if ($dataContent -> valid == 0) {
        $content['modal'] = modalize( 
          '<div class="row text-center">
              <h2 class="header capitalize col-12">Error Encountered</h2>
              <p class="para-text col-12">Error Details: ' . $dataContent -> err_msg . '</p>
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
              <p class="para-text col-12">Error Details: ' . $headerTitle -> err_msg . '</p>
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

  // Return JSON encode
  encode_json_file(array($success, $content));
?>