<?php
$conncet = mysqli_connect("121.37.100.255", "root", "123456", "art", "3306", "/tmp/mysql.sock");
if (!$conncet) exit("connect fail");
$result = mysqli_query($conncet, "SELECT * FROM customers");
if (!$result) exit("result fail");
$row = mysqli_fetch_row($result);

exit(json_encode(array('1'=>$row)));