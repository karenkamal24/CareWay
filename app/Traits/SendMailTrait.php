<?php
namespace App\Traits;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

trait SendMailTrait
{
    public function sendEmail($receiver_mail, $msg_title, $msg_content)
    {
        $mail = new PHPMailer(true);
        try {
           
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = env('MAIL_PORT');

          
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($receiver_mail);
            $mail->CharSet = 'UTF-8';

            $mail->isHTML(true);
            $mail->Subject = $msg_title;
            $mail->Body = $msg_content;

    
            $mail->send();
            return ['status' => 200, 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            Log::error("Email failed to send: " . $mail->ErrorInfo);
            return ['status' => 500, 'message' => 'Email failed to send', 'error' => $mail->ErrorInfo];
        }
    }
}
