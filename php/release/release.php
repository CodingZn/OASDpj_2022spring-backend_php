<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];

if ($req_method == "GET"){

    http_response_code(405);

}
elseif ($req_method == "POST"){
    //新增
}
elseif ($req_method == "PATCH"){
    //修改
}
else{
    http_response_code(405);
}
