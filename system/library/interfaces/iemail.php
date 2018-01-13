<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 2017/12/7
 * Time: 13:40
 */
namespace interfaces;

interface IEmail {

    /**
     * @param $from
     * @param $to
     * @param $title
     * @param $content
     * @return mixed
     */
    public function sendEmail($from,$to,$title,$content);

}