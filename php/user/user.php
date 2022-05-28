<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];

$userID = checkCustomerToken();
if (!$userID) {
    $data = array("message"=> "无操作权限！");
    http_response_code(401);
    exit(json_encode($data));
}

if ($req_method == "GET"){//获取用户信息

    $mysql = new Mysql();
    $user = $mysql->selectACustomer($userID);

    $data=array('user'=>$user);
    http_response_code(200);
    exit(json_encode($data));
}
elseif ($req_method == "POST"){//充值
    $data = json_decode(file_get_contents('php://input'), true);

    $mysql = new Mysql();
    $user = $mysql->selectACustomer($userID);
    $account = $user->UserAccount;

    $amount = $data['amount'];
    if (preg_match("/[^0-9]+/", $amount)){
        http_response_code(400);
        exit(json_encode(array('message'=>"输入非法！")));
    }
    if ($amount > 1000 || $amount <= 0){
        http_response_code(400);
        exit(json_encode(array('message'=>"充值金额只能为1000以内的正整数！")));
    }

    $account = $account + $amount;

    $map = array('UserAccount'=>$account);
    $result = $mysql->update('customers', $map, "WHERE CustomerID='$userID'");

    if (!$result){
        http_response_code(500);
        exit(json_encode(array('message'=>"未知错误！")));
    }

    http_response_code(200);
    exit(json_encode(array('message'=>"充值成功！")));

}
else{
    http_response_code(405);
}
