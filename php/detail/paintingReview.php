<?php
require_once "../header.php";
require_once "../Mysql.php";

$req_method = $_SERVER['REQUEST_METHOD'];

$userID = checkCustomerToken();
if (!$userID) {
    $data = array("message"=> "无操作权限！");
    http_response_code(401);
    exit(json_encode($data));
}

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

    $data = array('reviews'=>$reviews);
    exit(json_encode($data));

}
elseif($req_method == "PATCH"){//点赞或取消
    $data = json_decode(file_get_contents('php://input'), true);

    $RatingID = $data['RatingID'];
    $RatingOP = $data['RatingOP'];

    $mysql=new Mysql();
    $review = $mysql->selectAReview($RatingID);
    if ($RatingOP > 0)
        $review->Rating = $review->Rating + 1;
    else
        $review->Rating = $review->Rating - 1;

    //save review

}
elseif($req_method == "DELETE"){//删除评论

}
else{
    http_response_code(405);
}
