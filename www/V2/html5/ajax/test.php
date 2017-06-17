<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if ( isset( $_SESSION['rapidpub']) )
	session_unset( $_SESSION['rapidpub'] );
require_once( 'html5/includes/connexion.php' );
require_once( 'html5/includes/functions.php' );