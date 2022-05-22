<?php
require_once "header.php";

echo "hello php ";

$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "art";


$connect=mysqli_connect($servername, $username, $password, $dbname);

if(!$connect){
    die('链接失败！' . mysqli_error($connect));
}
$select = mysqli_select_db($connect, $dbname );
if (!$select){
    die('链接失败！' . mysqli_error($connect));
}
$userName = 'test@qq.com';
$pass = '123456';

$sql = "SELECT * FROM customerlogon WHERE UserName='test@qq.com' AND Pass=$pass ";

$user = mysqli_query($connect, $sql);
if ($user === false){
    die('无法读取数据！' . mysqli_error($connect));
}
else{
    $row = mysqli_fetch_array($user, MYSQLI_ASSOC);
    if ($row == null){
        echo "无此用户";
    }
    else{
        echo $row['Pass'];
    }

}

mysqli_free_result($user);
$close = mysqli_close($connect);

?>