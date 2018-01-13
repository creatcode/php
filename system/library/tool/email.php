<?php

/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/7
 * Time: 14:43
 */

namespace Tool;

use interfaces\IEmail;
use Enum\ErrorCode;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email implements IEmail {

    protected $config = [
        'smtp_server' => "smtp.163.com", //SMTP服务器
        'smtp_server_port' => 25, //SMTP服务器端口
        'smtp_user_mail' => "13686138279@163.com", //SMTP服务器的用户邮箱
        'smtp_user' => "13686138279", //SMTP服务器的用户帐号
        'smtp_pass' => "Wei123456", //SMTP服务器的用户密码
        'mail_type' => "HTML" //邮件格式（HTML/TXT）,TXT为文本邮件
    ];

    public function __construct($config) {
        //去掉空值免得覆盖
        /* $config = array_filter($config);
          $this->config = array_merge($this->config, $config);

          foreach ($this->config as $key => $val) {
          if (empty($val)) {
          throw new \Exception("missing parameter " . $key, ErrorCode::ERROR_MISSING_PARAMETER);
          }
          } */

        require __DIR__ . '/PHPMailer-master/src/Exception.php';
        require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
        require __DIR__ . '/PHPMailer-master/src/SMTP.php';
    }

    /**
     * 发送邮件 不支持from参数
     * @param $from string 置空
     * @param $to string
     * @param $title string
     * @param $content string
     */
    public function sendEmail($from, $to, $title, $content) {
        /* $smtp = new smtp($this->config['smtp_server'], $this->config['smtp_server_port'], true, $this->config['smtp_user'], $this->config['smtp_pass']); //这里面的一个true是表示使用身份验证,否则不使用身份验证.
          $smtp->debug = false; //是否显示发送的调试信息
          return $smtp->sendmail($to, $this->config['smtp_user_mail'], $title, $content, $this->config['mail_type']); */

        /*  $message = "
          <html>
          <head>
          <title>eazymov</title>
          </head>
          <body>
          " . $content . "
          </body>
          </html>
          ";
          $headers = "MIME-Version: 1.0" . "\r\n";
          $headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
          $headers .= 'From: eazymov' . "\r\n";
          return mail($to, $title, $content, $headers); */


        $mail = new PHPMailer;

// Tell PHPMailer to use SMTP
        $mail->isSMTP();

// Replace sender@example.com with your "From" address. 
// This address must be verified with Amazon SES.
        $mail->setFrom('574078142@qq.com', 'eazymov');

// Replace recipient@example.com with a "To" address. If your account 
// is still in the sandbox, this address must be verified.
// Also note that you can include several addAddress() lines to send
// email to multiple recipients.
        $mail->addAddress($to, 'eazymov user');

// Replace smtp_username with your Amazon SES SMTP user name.
        $mail->Username = 'AKIAIISDGEFOVM6HITPQ';

// Replace smtp_password with your Amazon SES SMTP password.
        $mail->Password = 'At5bVA7KAMr6gE8RdEgyZ4+tibokxoNFmh+04mFtxHhv';

// Specify a configuration set. If you do not want to use a configuration
// set, comment or remove the next line.
//$mail->addCustomHeader('X-SES-CONFIGURATION-SET', 'ConfigSet');//这个就不要开了
// If you're using Amazon SES in a region other than US West (Oregon), 
// replace email-smtp.us-west-2.amazonaws.com with the Amazon SES SMTP  
// endpoint in the appropriate region.
        $mail->Host = 'email-smtp.us-east-1.amazonaws.com';

// The subject line of the email
        $mail->Subject = $title;

// The HTML-formatted body of the email
        $mail->Body =$content;

// Tells PHPMailer to use SMTP authentication
        $mail->SMTPAuth = true;

// Enable TLS encryption over port 587
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

// Tells PHPMailer to send HTML-formatted email
        $mail->isHTML(true);

// The alternative email body; this is only displayed when a recipient
// opens the email in a non-HTML email client. The \r\n represents a 
// line break.
        $mail->AltBody = "Email Test\r\nThis email was sent through the 
    Amazon SES SMTP interface using the PHPMailer class.";

        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }

}
