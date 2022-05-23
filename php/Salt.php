<?php

class Salt
{
    function s(){
        $salt = md5(rand());var_dump($salt);
        $password = '123qwe';
        $hashed_password = crypt($password, $salt);

        var_dump($hashed_password);
        $hashed_password = crypt($password, $salt);

        var_dump($hashed_password);
        $hashed_password = crypt($password, $salt);

        var_dump($hashed_password);
    }

}