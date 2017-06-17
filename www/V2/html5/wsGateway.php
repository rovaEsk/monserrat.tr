<?php
/** Passerelle webservice prestashop, création des produits (titre, description, categorie, photo...) et ajout au panier **/
header('Content-Type: text/html; charset=utf-8');
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once( 'includes/connexion.php' );
require_once( 'includes/functions.php' );
require_once( 'includes/PSWebServiceLibrary.php' );

define( 'DEBUG', false );
define( '_PS_DEBUG_SQL_', false );
define( 'PS_SHOP_PATH', 'http://rapidpub.synapdev.fr/' );
define( 'PS_WS_AUTH_KEY', '7NE3W75GZ5C2LEPXD3U18ZATQPINCZM5' );

$webService = new PrestaShopWebservice( PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG );


?>