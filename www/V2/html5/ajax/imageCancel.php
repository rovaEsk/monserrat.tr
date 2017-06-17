<?php
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

if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
  if ( isset( $_POST['id'] ) AND $_POST['id'] != '' ) {
    $id = $_POST['id'];
    unset( $_SESSION['rapidpub'][$productID]['image'][$id] );
  }
}
?>
