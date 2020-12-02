<?php 
  // function that will return a value to the AJAX request
  function encode_json_file ($array) {
    echo json_encode($array);
  }

  // Function that will return the data in a modal form
  function modalize($bodyContent, $footerButton, $modalSize = '') {
    $footerContent = '';

    if ($footerButton['trasnType'] == 'error') {
      $footerContent = '
        <div class="col-sm-12 text-center btn-container">
          <button type="button" class="btn btn-default modal-close cancel" data-dismiss="modal">' . $footerButton['btnLbl'] . '</button>
        </div>
      ';
    } else if ($footerButton['trasnType'] == 'success') {
      $footerContent = '
        <div class="col-sm-12 text-center btn-container">
          <button type="button" class="btn btn-default update-rec-content modal-close transaction-btn" data-target="' . $footerButton['container'] . '" data-link="' . $footerButton['link'] . '" trans-name="' . $footerButton['transName'] . '" data-content="' . $footerButton['content'] . '" data-dismiss="modal">' . $footerButton['btnLbl'] . '</button>
        </div>
      ';
    } else if ($footerButton['trasnType'] == 'dialog') {
      $footerContent = '
         <div class="col-sm-12 col-md-6 text-right btn-container">
          <button type="button" class="btn btn-default modal-close transaction-btn" data-target="' . $footerButton['container'] . '" data-link="' . $footerButton['link'] . '" trans-name="' . $footerButton['transName'] . '" data-content="' . $footerButton['content'] . '" data-dismiss="modal">' . $footerButton['btnLbl'] . '</button>
        </div>
        <div class="col-sm-12 col-md-6 text-left btn-container">
          <button class="btn btn-default modal-close cancel" type="button" data-dismiss="modal">' . $footerButton['btnLblClose'] . '</button>
        </div>
      ';
    } else if ($footerButton['trasnType'] == 'ajax-next-footer') {
      $footerContent = '
         <div class="col-sm-12 col-md-6 text-right btn-container">
          <button type="button" class="btn btn-default modal-close transaction-btn" data-target="' . $footerButton['container'] . '" data-link="' . $footerButton['link'] . '" trans-name="' . $footerButton['transName'] . '" data-content="' . $footerButton['content'] . '" data-dismiss="modal">' . $footerButton['btnLbl1'] . '</button>
        </div>
        <div class="col-sm-12 col-md-6 text-left btn-container">
          <button class="btn btn-default submit-btn" type="button">' . $footerButton['btnLbl2'] . '</button>
        </div>
      ';
    } else if ($footerButton['trasnType'] == 'btn-trigger') {
      if (!isset($footerButton['btnLbl2']) && !empty($footerButton['btnLbl2'])) {
        $footerContent = '
          <div class="col-sm-12 col-md-6 text-right btn-container">
            <button type="button" class="btn btn-default modal-close btn-trigger">' . $footerButton['btnLbl1'] . '</button>
          </div>
          <div class="col-sm-12 col-md-6 text-left btn-container">
            <button class="btn btn-default modal-close cancel" type="button" data-dismiss="modal">' . $footerButton['btnLbl2'] . '</button>
          </div>
        ';
      } else {
        $footerContent = '
         <div class="col-sm-12 text-center btn-container">
          <button type="button" class="btn btn-default modal-close btn-trigger" data-dismiss="modal">' . $footerButton['btnLbl'] . '</button>
        </div>
      ';
      }
    } else {
      $footerContent = '
        <div class="col-sm-12 col-md-6 text-right btn-container">
          <button type="button" class="btn btn-default submit-btn">' . $footerButton['btnLbl'] . '</button>
        </div>
        <div class="col-sm-12 col-md-6 text-left btn-container">
          <button class="btn btn-default modal-close cancel" type="button" data-dismiss="modal">Cancel</button>
        </div>
      ';
    }

    return '
      <div class="modal fade" id="transaction-modal">
        <div class="modal-dialog ' . $modalSize . '">
          <div class="modal-content">
            <button class="close" data-dismiss="modal" type="button"><span class="fas fa-times"></span></button>
            <div class="modal-body">
              ' . $bodyContent . '

              <div class="row margin-top-xs">
                ' . $footerContent . '
              </div>
            </div>
          </div>
        </div>
      </div>
    ';
  }

  // Function that will select the current active page at the Navigation Bars
  function checkActivePage($pageName) {
    $currentPageName = basename($_SERVER['PHP_SELF'], '.php');

    if (strtolower($pageName) == strtolower($currentPageName)) {
      return 'active';
    }

    return '';
  }

  function getLastRec($tableName, $idColName = '') {
    $idColName = !empty($idColName)
      ? $idColName
      : $tableName . '_id';
    $selectRec = 'SELECT * FROM ' . $tableName . ' ORDER BY ' . $idColName . ' DESC LIMIT 1';
    $recResult = $GLOBALS['connection'] -> query($selectRec);

    if ($recResult -> num_rows > 0) {
      return $recResult -> fetch_object();
    }

    return '';
  }
?>