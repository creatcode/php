<?php
/* *
 * 配置文件
 * 版本：3.4
 * 修改日期：2016-03-08
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
namespace payment\alipay;

/**
 * 	配置账号信息
 */

class aliPayConfig
{
    //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
    //合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
    const PARTNER = '2088021154800263';

    //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
    const SELLER_ID = 'hero@estronger.cn';

    //商户的私钥,此处填写原始私钥，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
    const PRIVATE_KEY_PATH = 'key/rsa_private_key.pem';

    // 支付宝的公钥，查看地址：https://b.alipay.com/order/pidAndKey.htm
    const ALI_PUBLIC_KEY_PATH = 'key/alipay_public_key.pem';

    // 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
    const NOTIFY_URL = '';

    // 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
    const RETURN_URL = '';

    //签名方式
    const SIGN_TYPE = 'RSA';

    //字符编码格式 目前支持utf-8
    const INPUT_CHARSET = 'utf-8';

    //ca证书路径地址，用于curl中ssl校验
    //请保证cacert.pem文件在当前文件夹目录中
    const CACERT = '';

    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    const TRANSPORT = 'http';

    // 支付类型 ，无需修改
    const PAYMENT_TYPE = '1';

    // 产品类型，无需修改
    const SERVICE = "mobile.securitypay.pay";

    //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
}
?>