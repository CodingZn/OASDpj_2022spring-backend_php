<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];

if ($req_method == "GET"){
    if (array_key_exists('PaintingID',$_GET)){
        $PaintingID = $_GET['PaintingID'];
    }
    else {
        $data = array("message" => "缺少必要参数！");
        exit(json_encode($data));
    }

    $mysql = new Mysql();
    $reviews = $mysql->selectAllReviewsOfPainting($PaintingID);

    //按点赞数排序
    usort($reviews, "sort_Reviews_by_likes_reverse");

    $data = array('reviews'=>$reviews);
    exit(json_encode($data));

}
elseif($req_method == "POST"){//创建评论
    $userID = checkCustomerToken();
    if (!$userID) {
        $data = array("message"=> "无操作权限！");
        http_response_code(401);
        exit(json_encode($data));
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $PaintingID=$data['PaintingID'];
    $comment=$data['Comment'];
    $mysql=new Mysql();

    $columnNames=array('PaintingID', 'CustomerID', 'Comment');
    $columnValues=array($PaintingID, $userID, $comment);
    $result=$mysql->insert('reviews', $columnNames, $columnValues);

    if ($result){
        $data = array("message"=> "评论成功！");
        exit(json_encode($data));
    }
    else {
        $data = array("message"=> "未知错误！");
        http_response_code(500);
        exit(json_encode($data));
    }
}
elseif($req_method == "PATCH"){//点赞或取消
    $userID = checkCustomerToken();
    if (!$userID) {
        $data = array("message"=> "无操作权限！");
        http_response_code(401);
        exit(json_encode($data));
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $RatingID = $data['RatingID'];
    $RatingOP = $data['RatingOP'];

    $mysql=new Mysql();
    $review = $mysql->selectAReview($RatingID);
    $rating=$review->Rating;
    if ($RatingOP > 0)
        $rating = $rating + 1;
    else{
        if ($rating > 0)
            $rating->Rating = $rating - 1;
    }

    //save
    $maps = array('Rating'=>$rating);
    $mysql->update('reviews', $maps, "WHERE RatingID='$RatingID'");

    exit();

}
elseif($req_method == "DELETE"){//删除评论
    //必要参数
    if (array_key_exists('RatingID',$_GET)){
        $RatingID = $_GET['RatingID'];
    }
    else {
        $data = array("message" => "缺少必要参数！");
        http_response_code(400);
        exit(json_encode($data));
    }
    $userID = checkCustomerToken();
    if (!$userID) {
        $data = array("message"=> "无操作权限！");
        http_response_code(401);
        exit(json_encode($data));
    }
    $mysql=new Mysql();

    //检查权限
    $result = $mysql->select(array('RatingID'), 'reviews',
        "WHERE RatingID='$RatingID'");
    if (!$result){
        http_response_code(400);
        $data=array('message'=>"该评论不存在！");
        exit(json_encode($data));
    }
    $result = $mysql->select(array('RatingID'), 'reviews',
        "WHERE RatingID='$RatingID' AND CustomerID='$userID'");
    if (!$result){
        http_response_code(403);
        $data=array('message'=>"不能删除别人的评论！");
        exit(json_encode($data));
    }

    $mysql->delete('reviews', "WHERE RatingID='$RatingID'");

    $data=array('message'=>"删除成功！");
    exit(json_encode($data));
}
else{
    http_response_code(405);
}

function sort_Reviews_by_likes_reverse($Review1, $Review2){
    if ($Review1->Rating > $Review2->Rating)
        return -1;
    if ($Review1->Rating == $Review2->Rating)
        return 0;
    if ($Review1->Rating < $Review2->Rating)
        return 1;
}
