<?php

include "../autoload.php";

/*
include "../dist/SuperSQL.php";
include "../dist/SuperSQL_helper.php";
*/

if (isset($_GET["username"]) && isset($_GET["pass"])) {
    
    $SuperSQL = SuperSQL\SQLHelper::connect("localhost","mysite","root","1234");
    
     $SuperSQL->dev(); // dev mode
    
    $data = $SuperSQL->SELECT("users",[],[
        "username[string]" => $_GET["username"],
        "pass" => $_GET["pass"]
    ],1)->getData();
    
    if (isset($data[0])) {
        echo "Logged in! Data: " . json_encode($data[0]);
    }   
     echo "Query Data: " . json_encode($SuperSQL->getLog());
}

?>
