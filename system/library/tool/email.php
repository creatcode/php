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

class Email implements IEmail{

    protected $config = [
        'smtp_server' => "smtp.163.com",//SMTP服务器
        'smtp_server_port' => 25,//SMTP服务器端口
        'smtp_user_mail' => "",//SMTP服务器的用户邮箱
        'smtp_user' => "",//SMTP服务器的用户帐号
        'smtp_pass' => "",//SMTP服务器的用户密码
        'mail_type' => "HTML" //邮件格式（HTML/TXT）,TXT为文本邮件
    ];

    public function __construct($config)
    {
        //去掉空值免得覆盖
        $config = array_filter($config);
        $this->config = array_merge($this->config,$config);

        foreach($this->config as $key => $val ){
            if(empty($val)){
                throw new \Exception("missing parameter ".$key,ErrorCode::ERROR_MISSING_PARAMETER);
            }
        }
    }

    /**
     * 发送邮件 不支持from参数
     * @param $from string 置空
     * @param $to string
     * @param $title string
     * @param $content string
     */
    public function sendEmail($from, $to, $title, $content)
    {
        $smtp = new smtp($this->config['smtp_server'], $this->config['smtp_server_port'], true, $this->config['smtp_user'], $this->config['smtp_pass']);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
        $smtp->debug = false;//是否显示发送的调试信息
        return $smtp->sendmail($to, $this->config['smtp_user_mail'], $title, $content, $this->config['mail_type']);
    }
}

