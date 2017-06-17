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

$session = md5( session_id()+time() );
if ( isset( $_SESSION['prodID'] ) ) $productID = $_SESSION['prodID']; else	die();
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

if( !isset($DB_site) ){
    require_once("../../global.php");
}
if ( isset( $_SESSION['prodID'] ) ) $productID = $_SESSION['prodID']; else	die();

if (isset($_POST['action'])) {
   $action = $_POST['action'];
} else if(isset($_GET['action'])) {
   $action = $_GET['action'];
}
if (isset($_POST['type'])) {
   $type = $_POST['type'];
} else if(isset($_GET['type'])) {
   $type = $_GET['type'];
}
if(isset($_POST['dimension_id'])) {
   $dimension_id = $_POST['dimension_id'];
} else if(isset($_GET['dimension_id'])) {
   $dimension_id = $_GET['dimension_id'];
}
if(isset($_POST['maitere_id'])) {
   $maitere_id = $_POST['maitere_id'];
} else if(isset($_GET['maitere_id'])) {
   $maitere_id = $_GET['maitere_id'];
}


$data = array();
//$prixValue = 0.00;
if ( isset( $action ) AND $action == 'prix_image' ) {
    if(isset($type)){
        switch($type){
            case 'image': 
                $prix_image_regle = $DB_site->query_first("select * from  prixarticlemodule WHERE prixmoduleid =176");
                $prixImageRegle = $prix_image_regle['prixmodule']; 
                $prixarticlemodule = $DB_site->query_first("select * from  prixarticlemodule WHERE categorieregleprixid =$maitere_id AND categoriedimensionid =$dimension_id");
                $prixValueRegle = $prixarticlemodule['prixmodule'];
                $prixValue = $prixValueRegle;
                if ( isset( $_SESSION['rapidpub'][$productID]['image'] ) AND !empty( $_SESSION['rapidpub'][$productID]['image'] ) ) {
        			$elemT = $_SESSION['rapidpub'][$productID]['image'];
                    $iCountElemt = count($elemT);
                     //print_r($iCountElemt);
                    if($iCountElemt == 1){
                        $prixValue =  (float) number_format($prixValueRegle, 2) + (float) number_format($prixImageRegle, 2) ;
                    }
        		}
            break;
            default :
                die(); 
        }
        $data = array(
    		'prix_image' => $prixValue
    	); 
    }
}

if ( isset( $action ) AND $action == 'del_prix_image' ) {
    if(isset($type)){
        switch($type){
            case 'image': 
                $prix_image_regle = $DB_site->query_first("select * from  prixarticlemodule WHERE prixmoduleid =176");
                $prixImageRegle = $prix_image_regle['prixmodule']; 
                $prixarticlemodule = $DB_site->query_first("select * from  prixarticlemodule WHERE categorieregleprixid =$maitere_id AND categoriedimensionid =$dimension_id");
                $prixValueRegle = $prixarticlemodule['prixmodule'];
                $prixValue = $prixValueRegle;
                if ( isset( $_SESSION['rapidpub'][$productID]['image'] ) AND !empty( $_SESSION['rapidpub'][$productID]['image'] ) ) {
        			$elemT = $_SESSION['rapidpub'][$productID]['image'];
                    $iCountElemt = count($elemT);
                    //print_r($iCountElemt);
                    if($iCountElemt == 0){
                        $prixValue = (float) number_format($prixValueRegle, 2) - (float) number_format($prixImageRegle, 2) ;
                    }
        		}
            break;
            default :
                die(); 
        }
        $data = array(
    		'prix_image' => $prixValue
    	); 
    	
    }
}

if ( isset( $action ) AND $action == 'prix_fixation' ) {
    if(isset($type)){
        switch($type){
            case 'no_fixing': 
                $prix_image_regle = $DB_site->query_first("select * from  prixarticlemodule WHERE prixmoduleid =177");
                $prixImageRegle = $prix_image_regle['prixmodule']; 
                $prixarticlemodule = $DB_site->query_first("select * from  prixarticlemodule WHERE categorieregleprixid =$maitere_id AND categoriedimensionid =$dimension_id");
                $prixValueRegle = $prixarticlemodule['prixmodule'];
                $prixValue = $prixValueRegle;
                $countActionSession = $_SESSION['no_fixing']; 
                 if ( isset( $countActionSession ) AND !empty( $countActionSession ) AND $countActionSession != 0 ) {
                    $prixValue =  (float) number_format($prixValueRegle, 2) - (float) number_format($prixImageRegle, 2) ; 
                    $_SESSION['no_fixing'] = 0;     
                }
            break;
            case 'adhesif_fixing':
                $prix_image_regle = $DB_site->query_first("select * from  prixarticlemodule WHERE prixmoduleid =178");
                $prixImageRegle = $prix_image_regle['prixmodule']; 
                $prixarticlemodule = $DB_site->query_first("select * from  prixarticlemodule WHERE categorieregleprixid =$maitere_id AND categoriedimensionid =$dimension_id");
                $prixValueRegle = $prixarticlemodule['prixmodule'];
                $prixValue = $prixValueRegle;
                $countActionSession = $_SESSION['adhesif_fixing']; 
                if ( isset( $countActionSession ) AND !empty( $countActionSession ) AND $countActionSession != 0 ) {
                    $prixValue =  (float) number_format($prixValueRegle, 2) - (float) number_format($prixImageRegle, 2) ;
                    $_SESSION['adhesif_fixing'] = 0;     
                }
            break;
            default :
                die(); 
        }
        $data = array(
    		'prix_fixation' => $prixValue
    	); 
    }
}
//----------- launch data JSON --------------
echo json_encode( $data );
//----------- launch data JSON --------------
?>
