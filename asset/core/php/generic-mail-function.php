<?php 
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  require '../../vendor/PHPMailer/src/Exception.php';
  require '../../vendor/PHPMailer/src/PHPMailer.php';
  require '../../vendor/PHPMailer/src/SMTP.php';


  function sendEmail($reciever, $emailContent) {
    // System Mail Configuration
    $sysMailRecQry = 'SELECT * FROM system_mail_serv';
    $sysMailRecRes = $GLOBALS['connection'] -> query($sysMailRecQry);
    $sysMailRecRow = $sysMailRecRes -> fetch_object();

    $mail = new PHPMailer();

    //Server settings
    $mail->SMTPDebug = 0;                                 // Disable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = $sysMailRecRow -> serv_host;            // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $sysMailRecRow -> email_addr;       // SMTP username
    $mail->Password = $sysMailRecRow -> email_pwd;        // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = $sysMailRecRow -> serv_port;            // TCP port to connect to

    // Sender Info
    $mail->setFrom($sysMailRecRow -> email_addr, 'Item Code Management');
    $mail->addReplyTo($sysMailRecRow -> email_addr, 'Item Code Management');

    // Reciever Info
    for ($index = 0 ; $index < count($reciever) ; $index++) {
      $mail->addAddress( $reciever[$index] -> email,  $reciever[$index] -> fullname);
    }

    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $emailContent -> title;
    $mail->Body    = $emailContent -> mainBody;
    $mail->AltBody = $emailContent -> alternateBody;

    return $mail->send();
  }

?>