<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');

$maxRetries = 10;
$retry = 0;

while ($retry < $maxRetries) {
    $conn = @new mysqli($host, $user, $pass, $db);

    if (!$conn->connect_error) {
        echo "数据库连接成功！";
        break;
    }

    $retry++;
    sleep(2);
}

if ($retry == $maxRetries) {
    die("数据库连接失败");
}