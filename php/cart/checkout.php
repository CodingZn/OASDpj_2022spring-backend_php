<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];

if ($req_method == "GET"){//获取购物车里的所有商品

    http_response_code(405);

}elseif ($req_method == "POST"){//将一个商品添加到购物车
    if (!checkCustomerToken()) {
        $data = array("message"=> "无操作权限！");
        http_response_code(401);
        exit(json_encode($data));
    }

}
elseif ($req_method == "DELETE"){//删除购物车中的一个艺术品

    http_response_code(405);
}
else{
    http_response_code(405);
}

