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

if ($req_method == "GET"){//获取自己的艺术品信息，包括已发布和已卖出
    if(array_key_exists('type', $_GET))
        $type = $_GET['type'];

    //查询操作
    if($type=='released'){
        $mysql = new Mysql();
        $paintingIDList=$mysql->selectAllPaintingIDofCustomer($userID);

        $paintingList=$mysql->selectPartShortPaintingsByIDList($paintingIDList);
        $data = array('paintings'=>$paintingList, 'message'=>'操作成功！');
    }
    elseif ($type=='sold'){//查询卖出的所有艺术品，返回订单

        $mysql = new Mysql();
        $paintingIDList=$mysql->selectAllPaintingIDofCustomer($userID);
        $paintingList=$mysql->selectPartShortPaintingsByIDList($paintingIDList);
        $paintingList_sold = array();
        for($i=0; $i<count($paintingList); $i++){
            if ($paintingList[$i]->Status == 'sold'){
                array_push($paintingList_sold, $paintingList[$i]);
            }
        }
        $orderIDList = array();
        for($i=0; $i<count($paintingList_sold); $i++){
            $paintingID_sold = $paintingList_sold[$i][0];
            $result = $mysql->selectById(array('OrderID'), 'orders', 'PaintingID', $paintingID_sold);
            if (!$result) {
                $orderID= mysqli_fetch_assoc($result);
                array_push($orderIDList, $orderID);
            }
        }

        $orderList = array();
        for ($i=0; $i<count($orderIDList);$i++){
            $order = $mysql->selectAOrder_full($orderIDList[$i][0]);
            array_push($orderList, $order);
        }

        $data = array('orders'=>$orderList, 'message'=>'操作成功！');
    }
    else{
        http_response_code(400);
        exit(json_encode(array('message'=>'请求参数错误！')));
    }

    http_response_code(200);

    exit(json_encode($data));
}
elseif ($req_method == "DELETE"){//删除已发布的艺术品
    if(array_key_exists('PaintingID', $_GET))
        $PaintingID = $_GET['PaintingID'];
    else{
        http_response_code(400);
        exit(json_encode(array("message"=>"缺少必要的请求参数！")));
    }
    $mysql=new Mysql();

    //合法性与权限检查
    $painting = $mysql->selectAPaintingById($PaintingID);
    if (!$painting){
        http_response_code(404);
        exit(json_encode(array('message'=>'此艺术品不存在！')));
    }
    if ($painting->CustomerID_create != $userID){
        http_response_code(403);
        exit(json_encode(array('message'=>'您没有权限删除此艺术品！')));
    }
    if ($painting->Status == 'sold'){
        http_response_code(400);
        exit(json_encode(array('message'=>'已经卖出的艺术品不能删除！')));
    }

    //删除操作
    $result = $mysql->delete('paintings', "WHERE PaintingID='$PaintingID'");
    if (!$result){
        http_response_code(500);
        exit(json_encode(array('message'=>"未知错误！")));
    }

    http_response_code(200);
    exit(json_encode(array('message'=>"删除成功！")));
}
else{
    http_response_code(405);
}
