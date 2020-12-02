<?php 
  class error_handler {
    var $error_class = 'has-error has-feedback';
    var $error_icon = '<span class="fa fa-times form-control-feedback error-icon"></span>';
    var $error_text;
    function __construct($error_msg) {
      if (empty($error_msg)) {
        $this -> error_class = '';
        $this -> error_icon = '';
        $this -> error_text = '';
      } else {
        if (!($error_msg == 'flag')) {
          $this -> error_text = '<div class="error-text">' . $error_msg . '</div>';
        }
      }
    }
  }
?>