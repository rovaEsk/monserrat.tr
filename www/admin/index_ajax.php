<?php
include "includes/header.php";

if(isset($action) && $action == "supprWidget"){
	echo "la";
}

if(isset($action) && $action == "widget"){
	$json = json_decode($data, true);
	foreach($json as $j){
		$DB_site->query("UPDATE widget SET cols = '".$j['cols']."', rows = '".$j['rows']."', posX = '".$j['posX']."', posY = '".$j['posY']."' WHERE widgetid = '".$j['id']."'");
	}
}

if( isset($action) && $action == "statcontroller" && isset($_POST['cmd']) ){
	if($user_info[userid] == 1){
	exit;	
}
    switch( $_POST['cmd'] ){
        case "ctgchild":
            if( isset($_POST['catid'] ) ){
                $categories = MCategory::getChildren($_POST['catid']);
                echo json_encode($categories);
            }
        break;
    }
}

?>