<?php

class Mysql
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "123456";
    private $dbname = "art";

    private $connect;//数据库连接
    private $select;//架构art连接

    //artistID->artistName, Genre, Subject
    private $columnNames_ShortPainting_paintings = array("PaintingID", "ArtistID", "Title",
        "ImageFileName", "MSRP", "Popularity", "ReleaseDate", "Description", "Status");

    //artistID->artistName, to add: GenreNames, SubjectNames, RatingID
    private $columnNames_Painting_paintings = array("PaintingID", "ArtistID", "Title",
        "ImageFileName", "MSRP", "Popularity", "ReleaseDate", "Description", "Status",
        "CustomerID_create", "YearOfWork", "Width", "Height", "Medium");

    private $columnNames_Customer_customers = array('CustomerID', 'UserName', 'Email', 'Address', 'Phone', 'UserAccount');
    private $columnNames_Review_reviews = array('RatingID', 'PaintingID','CustomerID', 'CreateDateTime', 'Rating', 'Comment');
    private $columnNames_Order_orders = array('OrderID', 'CustomerID', 'DateCreated', 'PaintingID');


    public function __construct(){
        $this->connect=mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
        if(!$this->connect){
            die('数据库连接失败！' . mysqli_error($this->connect));
        }
        $this->select = mysqli_select_db($this->connect, $this->dbname );
        if (!$this->select){
            die('无此数据库！');
        }
    }

    public function func1($tablename, $username, $password){

        $sql = "SELECT * FROM $tablename WHERE UserName='$username' AND Pass='$password'";

        $result = mysqli_query($this->connect, $sql);
        if ($result === false){
            die('无法读取数据！' . mysqli_error($this->connect));
        }
        else{
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            if ($row == null){
                return false;
            }
            else{
                return $row['CustomerID'];
            }

        }
    }

    public function query($sql){
        $result = mysqli_query($this->connect, $sql);
        if ($result === false){
//            die('无法读取数据！' . mysqli_error($this->connect));
            return false;
        }
        else{
            return $result;
        }
    }

    public function select($columnNames, $tableName, $condition = ""){
        if ($columnNames === "*"){
            $columnSql = "*";
        }
        else {
            $columnSql = $this->columnArrayToSql($columnNames);
        }
        $sql = "SELECT $columnSql FROM $tableName $condition";
        $result = $this->query($sql);
        if (!$result || $result->num_rows == 0){
            return false;
        }
        else return $result;
    }

    public function insert($tableName, $columnNames, $columnValues){
        $columnNamesSql = $this->columnArrayToSql($columnNames);
        $columnValuesSql = $this->columnArrayToSqlWithQuo($columnValues);
        $sql = "INSERT INTO $tableName ($columnNamesSql) VALUES ($columnValuesSql)";
        $result = $this->query($sql);
        if (!$result) return false;
        $sql = "SELECT LAST_INSERT_ID()";
        $result = $this->query($sql);
        return $result;
    }

    public function delete($tableName, $condition){
        $sql = "DELETE FROM $tableName $condition";
        return $this->query($sql);
    }

    public function update($tableName, $maps, $condition){
        $mapSql = $this->mapsToSql($maps);
        $sql = "UPDATE $tableName SET $mapSql $condition";
        return $this->query($sql);
    }

    private function columnArrayToSql($columnNames){
        $n = count($columnNames);
        $columnSql = "";
        for ($i=0; $i < $n; $i++){
            $columnSql = $columnSql . $columnNames[$i] . ",";
        }
        return substr($columnSql, 0, strlen($columnSql)-1);
    }
    private function columnArrayToSqlWithQuo($columnNames){
        $n = count($columnNames);
        $columnSql = "'";
        for ($i=0; $i < $n; $i++){
            $columnSql = $columnSql . $columnNames[$i] . "','";
        }
        return substr($columnSql, 0, strlen($columnSql)-2);
    }
    private function mapsToSql($maps){
        $mapSql = "";
        foreach ($maps as $key=>$value){
            $mapSql = $mapSql . $key . '='. "'$value',";
        }
        return substr($mapSql, 0, strlen($mapSql) - 1);
    }

    public function selectById($columnNames, $tableName, $idName, $idValue){
        $condition = "WHERE $idName=$idValue";
        return $this->select($columnNames, $tableName, $condition);
    }

    public function selectOneObjById($columnNames, $tableName, $idName, $idValue){
        $result = $this->selectById($columnNames, $tableName, $idName, $idValue);
        if ($result === false){
            return $result;
        }else{
            $row = mysqli_fetch_assoc($result);
            return $row;
        }

    }

    public function selectAPaintingById($PaintingID){
        $result = $this->selectOneObjById($this->columnNames_Painting_paintings, "paintings", "PaintingID", $PaintingID);
        if (!$result){
            return false;
        }else{
            $this->addArtistName($result);
            $this->addGenre($result, $PaintingID);
            $this->addSubject($result, $PaintingID);
            $this->addCreatorUserName($result, $PaintingID);
            return (object) $result;
        }
    }

    private function addArtistName(&$result){
        $ArtistID = $result['ArtistID'];
        $result2 = mysqli_query($this->connect,"SELECT FirstName,LastName FROM artists WHERE ArtistID=$ArtistID");
        $Artist = mysqli_fetch_assoc($result2);
        $ArtistName = $Artist['FirstName'].' '.$Artist['LastName'];
        $result['ArtistName']=$ArtistName;

    }

    private function addGenre(&$result, $PaintingID){
        $result2 = mysqli_query($this->connect,"SELECT GenreID FROM paintinggenres WHERE PaintingID=$PaintingID");
        $GenreIDList = array();
        $GenreNameList = array();
        while ($row = mysqli_fetch_assoc($result2)){
            array_push($GenreIDList, $row['GenreID']);
        }
        for ($i=0; $i<count($GenreIDList);$i++){
            $GenreID=$GenreIDList[$i];
            $result3 = mysqli_query($this->connect, "SELECT GenreName FROM genres WHERE GenreID=$GenreID");
            $row = mysqli_fetch_assoc($result3);
            array_push($GenreNameList, $row['GenreName']);
        }
        $result['Genre'] = $GenreNameList;
    }

    private function addSubject(&$result, $PaintingID){
        $result2 = mysqli_query($this->connect,"SELECT SubjectID FROM paintingsubjects WHERE PaintingID=$PaintingID");
        $GenreIDList = array();
        $GenreNameList = array();
        while ($row = mysqli_fetch_assoc($result2)){
            array_push($GenreIDList, $row['SubjectID']);
        }
        for ($i=0; $i<count($GenreIDList);$i++){
            $GenreID=$GenreIDList[$i];
            $result3 = mysqli_query($this->connect, "SELECT SubjectName FROM subjects WHERE SubjectID=$GenreID");
            $row = mysqli_fetch_assoc($result3);
            array_push($GenreNameList, $row['SubjectName']);
        }
        $result['Subject'] = $GenreNameList;
    }

    private function addCreatorUserName(&$result, $PaintingID){
        $CreatorID = $result['CustomerID_create'];
        if (!$CreatorID) {
            $result['UserName_create']=null;return ;
        }
        $Customer = $this->selectACustomer($CreatorID);
        if (!$Customer) {
            $result['UserName_create']=null;return ;
        }
        $result['UserName_create']=$CreatorID['UserName'];
    }

    private function addCommenterUserName(&$result){
        $CreatorID = $result['CustomerID'];
        if (!$CreatorID) {
            $result['UserName_create']=null;return ;
        }
        $Customer = $this->selectACustomer($CreatorID);
        if (!$Customer) {
            $result['UserName_create']=null;return ;
        }
        $result['UserName_create']=$Customer->UserName;
    }

    public function selectAShortPaintingById($PaintingID){
        $result = $this->selectOneObjById($this->columnNames_ShortPainting_paintings, "paintings", "PaintingID", $PaintingID);
        if ($result == null){
            return false;
        }else{
            $result['Description'] = substr($result['Description'],0,150);
            $this->addArtistName($result);
            $this->addGenre($result, $PaintingID);
            $this->addSubject($result, $PaintingID);
            return (object) $result;
        }
    }

    public function selectAllPaintingIDs(){
        $result = $this->select(array('PaintingID'), 'paintings');
        if (!$result) return false;
        else{
            $PaintingIDList = mysqli_fetch_all($result);
            return $PaintingIDList;
        }
    }

    public function selectAllShortPaintings(){
        $PaintingIDList = $this->selectAllPaintingIDs();
        if(!$PaintingIDList) return false;
        $paintingList = array();
        for ($i=0;$i<count($PaintingIDList);$i++){
            $painting = $this->selectAShortPaintingById($PaintingIDList[$i][0]);
            array_push($paintingList, $painting);
        }
        return $paintingList;
    }

    public function selectPartShortPaintingsByIDList($PaintingIDList){
        $paintingList = array();
        for ($i=0;$i<count($PaintingIDList);$i++){
            $painting = $this->selectAShortPaintingById($PaintingIDList[$i][0]);
            array_push($paintingList, $painting);
        }
        return $paintingList;
    }

    //select ids

    public function selectAllPaintingIDinCart($CustomerID){
        $columnNames=array('PaintingID');
        $result=$this->select($columnNames, 'customer_cart', "WHERE CustomerID='$CustomerID'");
        if (!$result) return array();
        return mysqli_fetch_all($result);
    }

    public function selectAllPaintingIDofCustomer($CustomerID){
        $columnNames=array('PaintingID');
        $result=$this->select($columnNames, 'paintings', "WHERE CustomerID_create='$CustomerID'");
        if (!$result) return array();
        return mysqli_fetch_all($result);
    }

    public function selectAllOrderIDofCustomer($CustomerID){
        $columnNames=array('OrderID');
        $result=$this->select($columnNames, 'orders',
            "WHERE CustomerID='$CustomerID'");
        if (!$result) return array();
        return mysqli_fetch_all($result);
    }

    //other entity

    public function selectACustomer($CustomerID){
        $result = $this->selectOneObjById($this->columnNames_Customer_customers, "customers", "CustomerID", $CustomerID);
        if ($result == null){
            return false;
        }else{
            return (object) $result;
        }
    }

    public function selectAReview($RatingID){
        $result = $this->selectOneObjById($this->columnNames_Review_reviews, "reviews", "RatingID", $RatingID);
        if ($result == null){
            return false;
        }else{
            $this->addCommenterUserName($result, $RatingID);
            return (object) $result;
        }
    }

    public function selectAllReviewsOfPainting($PaintingID){
        $columnNames=array('RatingID');
        $result=$this->select($columnNames, 'reviews', "WHERE PaintingID='$PaintingID'");
        $reviewIDList = mysqli_fetch_all($result);
        $reviewList = array();
        for ($i=0; $i<count($reviewIDList); $i++){
            $review = $this->selectAReview($reviewIDList[$i][0]);
            array_push($reviewList, $review);
        }
        return $reviewList;
    }

    public function selectAOrder_raw($OrderID){
        $result = $this->selectOneObjById($this->columnNames_Order_orders, "orders", "OrderID", $OrderID);
        if ($result == null){
            return false;
        }else{
            return (object) $result;
        }
    }

    public function selectAOrder_full($OrderID){
        $order = $this->selectAOrder_raw($OrderID);
        $order->Painting = $this->selectAShortPaintingById($order->PaintingID);
        $order->Customer = $this->selectACustomer($order->CustomerID);
        return $order;
    }


    //get values
    public function getGenres(){
        $sql = "SELECT GenreID,GenreName FROM genres";
        $result = $this->query($sql);
        return mysqli_fetch_all($result);
    }

    public function getSubjects(){
        $sql = "SELECT SubjectID,SubjectName FROM subjects";
        $result = $this->query($sql);
        return mysqli_fetch_all($result);
    }

    public function getArtists(){
        $sql = "SELECT ArtistID,LastName FROM artists";
        $result = $this->query($sql);
        return mysqli_fetch_all($result);
    }

    //insert


}
