<?php
// ─── email-templates.php ─────────────────────────────────────────────────────
require_once __DIR__ . '/mailer.php';

// ── 1. WELCOME OTP (register) ─────────────────────────────────────────────────
function sendWelcomeOTP($toEmail, $toName, $otp) {
    $body = emailTemplate('Welcome to Maison', '
        <p style="font-size:13px;letter-spacing:3px;color:#7a6a35;text-transform:uppercase;font-family:Arial,sans-serif;margin:0 0 16px;">Welcome</p>
        <h1 style="font-size:26px;color:#f2edd8;font-family:Georgia,serif;font-weight:normal;margin:0 0 20px;">Hello, ' . htmlspecialchars($toName) . '</h1>
        <p style="font-size:15px;color:#8a8070;line-height:1.8;font-family:Arial,sans-serif;margin:0 0 32px;">
            Thank you for joining Maison Fine Dining. Use the code below to verify your account.
        </p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 32px;">
          <tr><td align="center">
            <div style="background:#0a0b0d;border:1px solid #2a2416;display:inline-block;padding:24px 48px;text-align:center;">
              <div style="font-size:11px;letter-spacing:4px;color:#7a6a35;text-transform:uppercase;font-family:Arial,sans-serif;margin-bottom:12px;">Verification Code</div>
              <div style="font-size:38px;letter-spacing:12px;color:#c49a3c;font-family:Courier,monospace;font-weight:bold;">' . $otp . '</div>
              <div style="font-size:11px;color:#3a3b3f;font-family:Arial,sans-serif;margin-top:12px;">Valid for 10 minutes</div>
            </div>
          </td></tr>
        </table>
        <p style="font-size:13px;color:#4a4540;font-family:Arial,sans-serif;">If you did not create an account, please ignore this email.</p>
    ');
    return sendMail($toEmail, $toName, 'Welcome to Maison — Verify Your Account', $body);
}

// ── 2. FORGOT PASSWORD OTP ────────────────────────────────────────────────────
function sendPasswordResetOTP($toEmail, $toName, $otp) {
    $body = emailTemplate('Reset Your Password', '
        <p style="font-size:13px;letter-spacing:3px;color:#7a6a35;text-transform:uppercase;font-family:Arial,sans-serif;margin:0 0 16px;">Security</p>
        <h1 style="font-size:26px;color:#f2edd8;font-family:Georgia,serif;font-weight:normal;margin:0 0 20px;">Reset Your Password</h1>
        <p style="font-size:15px;color:#8a8070;line-height:1.8;font-family:Arial,sans-serif;margin:0 0 32px;">
            We received a request to reset your Maison account password. Use the code below to proceed.
        </p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 32px;">
          <tr><td align="center">
            <div style="background:#0a0b0d;border:1px solid #2a1a1a;display:inline-block;padding:24px 48px;text-align:center;">
              <div style="font-size:11px;letter-spacing:4px;color:#7a6a35;text-transform:uppercase;font-family:Arial,sans-serif;margin-bottom:12px;">Reset Code</div>
              <div style="font-size:38px;letter-spacing:12px;color:#c49a3c;font-family:Courier,monospace;font-weight:bold;">' . $otp . '</div>
              <div style="font-size:11px;color:#3a3b3f;font-family:Arial,sans-serif;margin-top:12px;">Valid for 10 minutes</div>
            </div>
          </td></tr>
        </table>
        <p style="font-size:13px;color:#4a4540;font-family:Arial,sans-serif;">If you did not request this, your account is safe — ignore this email.</p>
    ');
    return sendMail($toEmail, $toName, 'Maison — Password Reset Code', $body);
}

// ── 3. BOOKING CONFIRMATION ───────────────────────────────────────────────────
function sendBookingConfirmation($toEmail, $toName, $bookingRef, $date, $time, $partySize, $occasion = '', $tableNumber = null, $tableLocation = null) {
    $formattedDate = date('l, d F Y', strtotime($date));
    $occasionRow   = ($occasion && $occasion !== 'none')
        ? '<tr>
            <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#6a6560;font-family:Arial,sans-serif;">Occasion</td>
            <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#c4b898;font-family:Arial,sans-serif;text-align:right;">' . ucfirst($occasion) . '</td>
           </tr>' : '';

    $body = emailTemplate('Booking Confirmed', '
        <p style="font-size:13px;letter-spacing:3px;color:#7a6a35;text-transform:uppercase;font-family:Arial,sans-serif;margin:0 0 16px;">Reservation Confirmed</p>
        <h1 style="font-size:26px;color:#f2edd8;font-family:Georgia,serif;font-weight:normal;margin:0 0 12px;">Your table awaits, ' . htmlspecialchars($toName) . '</h1>
        <p style="font-size:15px;color:#8a8070;line-height:1.8;font-family:Arial,sans-serif;margin:0 0 32px;">
            Your reservation at Maison Fine Dining is confirmed. We look forward to welcoming you. Please arrive a few minutes early.
        </p>
        <div style="background:#0a0b0d;border:1px solid #1e1f24;padding:24px 28px;margin-bottom:32px;">
          <div style="font-size:10px;letter-spacing:4px;color:#7a6a35;text-transform:uppercase;font-family:Arial,sans-serif;margin-bottom:20px;">Booking Details</div>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#6a6560;font-family:Arial,sans-serif;">Reference</td>
              <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#c49a3c;font-family:Courier,monospace;text-align:right;letter-spacing:2px;">' . $bookingRef . '</td>
            </tr>
            <tr>
              <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#6a6560;font-family:Arial,sans-serif;">Date</td>
              <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#c4b898;font-family:Arial,sans-serif;text-align:right;">' . $formattedDate . '</td>
            </tr>
            <tr>
              <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#6a6560;font-family:Arial,sans-serif;">Time</td>
              <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#c4b898;font-family:Arial,sans-serif;text-align:right;">' . $time . '</td>
            </tr>
            <tr>
              <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#6a6560;font-family:Arial,sans-serif;">Guests</td>
              <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#c4b898;font-family:Arial,sans-serif;text-align:right;">' . $partySize . ' ' . ($partySize == 1 ? 'Guest' : 'Guests') . '</td>
            </tr>
            ' . $occasionRow . '
            ' . ($tableNumber ? '
            <tr>
              <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#6a6560;font-family:Arial,sans-serif;">Table</td>
              <td style="padding:10px 0;border-bottom:1px solid #1e1f24;font-size:13px;color:#c4b898;font-family:Arial,sans-serif;text-align:right;">' . $tableNumber . ' &nbsp;·&nbsp; ' . ucfirst($tableLocation) . '</td>
            </tr>' : '') . '
          </table>
        </div>
        <p style="font-size:13px;color:#4a4540;line-height:1.8;font-family:Arial,sans-serif;margin:0 0 8px;">
            📍 Savedi Road, Ahmednagar 414003 &nbsp;·&nbsp; 📞 +91 7972666151
        </p>
        <p style="font-size:12px;color:#3a3b3f;font-family:Arial,sans-serif;margin:16px 0 0;">
            To cancel or modify, please contact us at least 2 hours in advance.
        </p>
    ');
    return sendMail($toEmail, $toName, 'Booking Confirmed — ' . $bookingRef . ' · Maison', $body);
}
