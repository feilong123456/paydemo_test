<?php
header('Content-Type: application/json');
include_once('config.php');
function send_post($url, $post_data)
{

    $postdata = http_build_query($post_data);
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $postdata,
            'timeout' => 15 * 60 // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result;
}

$money = $_REQUEST['money'];
$type =  $_REQUEST['paytype'];
$out_trade_no = $_REQUEST['out_trade_no'];
$arr = array(
    'pid' => $uid,
    'out_trade_no' => $out_trade_no,
    'type' => $type,
    'name' => "VIP",
    'notify_url'=>$huidiao_url,
    'return_url'=>$return_url,
    'money' => $money,
    'request_method'=>"JSON",
);
ksort($arr);
reset($arr);
$arg  = "";
foreach ($arr as $key=>$val) {
    $arg.=$key."=".$val."&";
}
//去掉最后一个&字符
$arg = substr($arg,0,-1);

//$sign = trim($sign,'&');
$sign = md5($arg.$uidkey);

$arr['sign']=$sign;
$arr['request_method']="JSON";
$arr['sign_type']="MD5";
$get_data =json_decode(trim(send_post($pay_url, $arr)),true);
//重定向即可
function redirect_to($url) {
    // 检查是否已经发送过任何输出
    if (!headers_sent()) {
        header("Location: $url", true, 302); // 302 是默认的临时重定向状态码
        exit(); // 终止脚本执行，确保重定向发生
    } else {
        echo "Error: Headers already sent!";
    }
}

// 使用重定向函数
redirect_to($get_data['pay_url']);