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

if ($req_method == "GET"){//获取购物车里的所有商品

    $mysql = new Mysql();

    $paintingIDList=$mysql->selectAllPaintingIDinCart($userID);
    $paintingList=$mysql->selectPartShortPaintingsByIDList($paintingIDList);

    $data = array('paintings'=>$paintingList, 'message'=>'操作成功！');

    http_response_code(200);

    exit(json_encode($data));
}
elseif ($req_method == "POST"){//将一个商品添加到购物车
    $mysql = new Mysql();
//获取表单
    $data = json_decode(file_get_contents('php://input'), true);
    $PaintingID = $data['PaintingID'];

    $columnNames=array('CustomerID', 'PaintingID');
    $columnValues=array($userID, $PaintingID);

    //查找状态
    $painting = $mysql->selectAPaintingById($PaintingID);
    if ($painting->Status !== 'released'){
        http_response_code(400);
        exit(json_encode(array('message'=>"艺术品 $painting->Title 已售出！")));
    }

//检查是否已经添加到购物车
    $result = $mysql->select($columnNames, 'customer_cart',
        "WHERE CustomerID='$userID' AND PaintingID='$PaintingID'");
    if ($result){
        http_response_code(400);
        $data=array('message'=>"您已添加过该艺术品，不能重复添加！");
        exit(json_encode($data));
    }
//添加到购物车
    $result = $mysql->insert('customer_cart', $columnNames, $columnValues);
    if ($result){
        http_response_code(200);
        $data=array('message'=>"添加成功！");
        exit(json_encode($data));
    }
    else{
        http_response_code(500);
        exit(json_encode(array('message'=>"添加失败！请刷新页面后重试！")));
    }

}
elseif ($req_method == "DELETE"){//删除购物车中的一个艺术品
    if (array_key_exists('PaintingID',$_GET)){
        $PaintingID = $_GET['PaintingID'];
    }
    else {
        $data = array("message" => "缺少必要参数！");
        http_response_code(400);
        exit(json_encode($data));
    }
    $mysql=new Mysql();

    //检查是否已经添加到购物车
    $result = $mysql->select("*", 'customer_cart',
        "WHERE CustomerID='$userID' AND PaintingID='$PaintingID'");
    if (!$result){
        http_response_code(400);
        $data=array('message'=>"您的购物车里没有该商品！");
        exit(json_encode($data));
    }
    $result=$mysql->delete('customer_cart', "WHERE CustomerID='$userID' AND PaintingID='$PaintingID'");
    if ($result){
        http_response_code(200);
        $data=array('message'=>"删除成功！");
        exit(json_encode($data));
    }
    else{
        http_response_code(500);
        exit(json_encode(array('message'=>"未知错误！")));
    }
}
else{
    http_response_code(405);
}
