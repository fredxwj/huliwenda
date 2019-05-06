<?php
/**
 * Created by PhpStorm.
 * User: xingwanjia
 * Date: 2019/5/6
 * Time: 11:47 PM
 */
require_once 'request.php';


function postBaixingQA($title, $content, $answers, $userid){
    $userid = 'cjnswv5ft000k0a91m79oecnp';
    $url = 'http://pgc.baixing.space/api/post';
    #$url = 'http://139.198.15.30:8017/v1/qa/paid/create';
    $app_id = 'admin';
    $secret = '25b02ac9c14998';
    $timestamp = strtotime('now');
    $token = md5('/v1/qa/paid/create'.'post'.$secret.$timestamp);

    $arr = array(
        'title' => $title,
        'content_html' => $content,
        'answers' => $answers,
        'vendor' => 'baixing',
        'cate' => 'shenghuofuwu',
        'subcate' => 'shenghuochangshi',
        'cate_name' => '生活服务',
        'subcate_name' => '生活常识',
        "userId" => $userid
    );

    $headers= array(
        'Content-type:application/json;charset=utf-8',
        'X-APP-ID:admin',
        'Expect:',
        'X-TOKEN:'.$token,
        'X-TIMESTAMP:'.$timestamp,
    );

    $post_data = json_encode($arr);
    $request = new Request();
    list($code, $header, $body) = Request::post($url, $headers, $post_data, 1);
    if ($code === 200){
        #var_dump($code, $header, $body);
        #var_dump("===succ====:", $body);
        $responseJson = json_decode($body);
        if ($responseJson->code == 0){
            $id = $responseJson->data->question_id;
            return $id;
        }
    }
    return 0;
}