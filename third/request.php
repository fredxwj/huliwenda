<?php
/**
 * Created by PhpStorm.
 * User: xingwanjia
 * Date: 2019/5/6
 * Time: 11:51 PM
 */


Class Request
{
    public static function post($url, $headers, $post_data, $flag){
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        //执行命令
        $data = curl_exec($curl);
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        //关闭URL请求
        curl_close($curl);
        #var_dump($code, $header, $body);
        return array($code, $header, $body);
    }
};
