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

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once( '../includes/connexion.php' );
require_once( '../includes/functions.php' );

if ( isset( $_SESSION['prodID'] ) ) $productID = $_SESSION['prodID']; else die();

if ( isset( $_POST['action'] ) AND $_POST['action'] == 'update' ) {

	$width = $_POST['width'];
	$height = $_POST['height'];
	$color = $_POST['couleur'];
	$type = $_POST['type'];
	$ratio = $_POST['ratio'];
	if ( isset( $_POST['id'] ) ) {

		$id = $_POST['id'];

		$_SESSION['rapidpub'][$productID]['forme'][$id]['color'] = $color;
		$_SESSION['rapidpub'][$productID]['forme'][$id]['height'] = $height;
		$_SESSION['rapidpub'][$productID]['forme'][$id]['width'] = $width;
		$_SESSION['rapidpub'][$productID]['forme'][$id]['ratio'] = floatval( $ratio );

		$data = $_SESSION['rapidpub'][$productID]['forme'][$id];

	} else {

		$id = md5( uniqid( rand(), true ) );
		$data = array(
			'id' 		=> $id,
			'color'		=> $color,
			'height'	=> $height,
			'width'		=> $width,
			'ratio'		=> floatval( $ratio ),
			'expDate'	=> date( 'Yd/m/Y', strtotime( "+2 days" ) ),
		);
		$_SESSION['rapidpub'][$productID]['forme'][$id] = $data;

	}

	echo json_encode( $data );

}

?>
