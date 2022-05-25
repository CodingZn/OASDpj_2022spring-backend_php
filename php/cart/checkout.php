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


if ($req_method == "POST"){//下单并付款
    $mysql = new Mysql();
    //获取要购买的艺术品ID
    if (array_key_exists('range',$_GET) && $_GET['all'] == '0'){//partly
        $data = json_decode(file_get_contents('php://input'), true);
        $paintingIDList=["PaintingIDs"];
    }
    else {
        $paintingIDList=$mysql->selectAllPaintingIDinCart($userID);
    }
    if (count($paintingIDList) <= 0){
        http_response_code(400);
        exit(json_encode(array('message'=>'没有要下单的商品！')));
    }

    //对于每件艺术品，查找状态，检查余额，未售出则在购物车中删除，创建订单，扣款
    foreach ($paintingIDList as $PaintingID) {
        $PaintingID = $PaintingID[0];
        //查找状态
        $painting = $mysql->selectAPaintingById($PaintingID);var_dump($painting);
        if ($painting->Status !== 'released'){
            http_response_code(400);
            exit(json_encode(array('message'=>"艺术品 $painting->Title 已售出！")));
        }

        //检查余额
        $cost = $painting->MSRP;
        $account = $mysql->selectACustomer($userID)->UserAccount;
        if ($cost > $account){
            http_response_code(400);
            exit(json_encode(array('message'=>'余额不足！请充值！')));
        }

        //设为售出
        $map = array('Status'=>'sold');
        $mysql->update('paintings', $map, "WHERE PaintingID='$PaintingID'");

        //在购物车中删除
        $mysql->delete('customer_cart', "WHERE CustomerID='$userID' AND PaintingID='$PaintingID'");

        //创建订单
        $columnNames = array('CustomerID', 'PaintingID');
        $columnValues = array($userID, $PaintingID);
        $mysql->insert('orders', $columnNames, $columnValues);

        //扣款
        $account = $account - $cost;
        $map = array('UserAccount'=>$account);
        $mysql->update('customers', $map, "WHERE CustomerID='$userID'");

    }

    http_response_code(200);
    exit(json_encode(array('message'=>'下单成功！')));

}
else{
    http_response_code(405);
}

