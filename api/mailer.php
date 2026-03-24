<?php
// ─── mailer.php ───────────────────────────────────────────────────────────────

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

define('MAIL_FROM',     'maisonadmin007@gmail.com');
define('MAIL_PASSWORD', 'avpltaevgwmgodrz');
define('MAIL_FROM_NAME','Maison Fine Dining');

function sendMail($toEmail, $toName, $subject, $htmlBody) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_FROM;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

function emailTemplate($title, $bodyContent) {
    return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"/><title>' . $title . '</title></head>
<body style="margin:0;padding:0;background:#0a0b0d;font-family:Georgia,serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0a0b0d;padding:40px 0;">
  <tr><td align="center">
    <table width="580" cellpadding="0" cellspacing="0" style="max-width:580px;width:100%;background:#131518;border:1px solid #1e1f24;">
      <tr>
        <td style="padding:36px 40px 28px;text-align:center;border-bottom:1px solid #1e1f24;">
          <div style="font-size:11px;letter-spacing:6px;color:#7a6a35;text-transform:uppercase;font-family:Arial,sans-serif;margin-bottom:8px;">Est. 2018 · Ahmednagar</div>
          <div style="font-size:28px;letter-spacing:8px;color:#c49a3c;font-family:Georgia,serif;">MAISON</div>
          <div style="width:40px;height:1px;background:#c49a3c;margin:16px auto 0;opacity:0.4;"></div>
        </td>
      </tr>
      <tr><td style="padding:36px 40px;">' . $bodyContent . '</td></tr>
      <tr>
        <td style="padding:24px 40px;border-top:1px solid #1e1f24;text-align:center;">
          <div style="font-size:11px;color:#3a3b3f;font-family:Arial,sans-serif;">
            Savedi Road, Ahmednagar 414003 · maisonadmin007@gmail.com<br/>© 2026 Maison Fine Dining
          </div>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body></html>';
}
