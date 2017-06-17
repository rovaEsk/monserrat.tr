<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
	if ( isset( $_SERVER['HTTP_ORIGIN'] ) ) {
		$address = 'http://' . $_SERVER['SERVER_NAME'];
		if ( strpos( $address, $_SERVER['HTTP_ORIGIN'] ) !== 0 ) {
			exit( 'CSRF protection in POST request: detected invalid Origin header: ' . $_SERVER['HTTP_ORIGIN'] );
		}
	}
}

if ( isset( $_SESSION['prodID'] ) ) $productID = $_SESSION['prodID']; else	die();
//-----------------------------------
require_once("../../global.php");
//------------------------------------   
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

if (isset($_POST['action'])) {
   $action = $_POST['action'];
} else if(isset($_GET['action'])) {
   $action = $_GET['action'];
}

if (isset($_POST['moduleId'])) {
   $moduleId = $_POST['moduleId'];
} else if(isset($_GET['moduleId'])) {
   $moduleId = $_GET['moduleId'];
}
$imageData = array('imageData' => "");
$arrayImg = array();
$dataImg = "";
if(isset($action) && $action != ""){
    if(isset($moduleId) && $moduleId != ""){
        switch($moduleId){
            case 1: //Banderole
			case 5: //Magnet
            case 6: //Panneau
            case 7: //PLaquepro
            case 8: //Vitrine
                if ( isset( $_SESSION['rapidpub'][$productID]['image'] ) && !empty( $_SESSION['rapidpub'][$productID]['image'] )) {
        			$elemT = $_SESSION['rapidpub'][$productID]['image'];
                    foreach($elemT as $key=>$value){
                        array_push($arrayImg, $value['imgPath']);
                    }
                    base64_encode(serialize($arrayImg));
                    $dataImg = base64_encode(serialize($arrayImg));
                    $imageData = array('imageData' => $dataImg);
                }
            break;
            default :
                $imageData = array('imageData' => "");
        }            
    }
    /** ---- launch JSON ----**/
    echo json_encode($imageData);
    /** ---- launch JSON ----**/
}
