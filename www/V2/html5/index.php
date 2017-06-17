<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

if ( isset( $_GET['action'] ) ) {

	switch ( $_GET['action'] ) {

		case 'lettrages_adhesifs':
			include_once 'lettrages_adhesifs.php';
			break;

		case 'lettres_en_relief':
			include_once 'lettres_en_relief.php';
			break;

		case 'vitrines':
			include_once 'vitrines.php';
			break;

		case 'banderoles':
			include_once 'banderoles.php';
			break;

		case 'panneaux':
			include_once 'panneaux.php';
			break;

		case 'magnet':
			include_once 'magnet.php';
			break;

		case 'lettres_boitier':
			include_once 'lettres_boitier.php';
			break;

		case 'plaques_professionnelles':
			include_once 'plaques-professionnelles.php';
			break;

		case 'plaques_professionnelles2':
			include_once 'plaques_professionnelles.php';
			break;

		case 'tampons':
			include_once 'tampons.php';
			break;

		case 'gravograph':
			include_once 'gravograph.php';
			break;

		case 'immatriculation':
			include_once 'immatriculation.php';
			break;

		default:
			header('HTTP/1.1 500 Internal Server Error');

	}

} else {

	header('HTTP/1.1 500 Internal Server Error');
	
}
?>
