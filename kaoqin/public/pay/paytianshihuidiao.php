<?php
include_once('config.php');
function send_get($url, $get_data = array())
{
    // 如果有查询参数，则将其附加到 URL
    if (!empty($get_data)) {
        $query = http_build_query($get_data);
        $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
    }
    $options = array(
        'http' => array(
            'method'  => 'GET',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'timeout' => 15 * 60 // 超时时间（单位: 秒）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
}
// 输出所有查询参数
if (!empty($_GET)) {
    $huidiao_result = send_get($wangzhan_huidiao_url, $_GET);
    echo $huidiao_result;
    exit();
}else{
    echo "error";
}