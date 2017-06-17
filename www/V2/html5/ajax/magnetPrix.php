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

$productID = $_SESSION['prodID'];
if ( !isset( $productID ) )
	die();

require_once ('../../global.php');

$articlemoduleid = 5;

$longueur = $_POST['bLong'];
$hauteur = $_POST['bHaut'];
//$optionImage = $_POST['optionImage'];

$surface = $longueur * $hauteur;

//Calcul du prix du panneau par rapport à sa surface et les données sur le prix par m2 stoquées en bdd

$tarif = 0;
$tarifImage = 0;
$tarifFond = 0;

//Champ largeur pris comme surface min et longueur comme surface max
$request = "SELECT pm.prixmodule AS prixmodule, pm.largeur AS surfaceMax
			FROM prixarticlemodule AS pm
			INNER JOIN articlemodule_prix AS amp
    			ON pm.prixmoduleid = amp.prixmoduleid
			WHERE amp.articlemoduleid = ".$articlemoduleid." 
				AND pm.largeur <> 0
			ORDER BY pm.largeur ASC" ;


$moduleFeatures=$DB_site->query($request);

while ($moduleFeature=$DB_site->fetch_array($moduleFeatures)) {
	$pm = $moduleFeature[prixmodule];
	$sm = $moduleFeature[surfaceMax];
	if( $surface >= $sm)
		$tarifFond = $surface / 10000 * $pm;
}

$request2 = "	SELECT pm.prixmodule AS prixmodule
				FROM prixarticlemodule AS pm
				INNER JOIN articlemodule_prix AS amp
    				ON pm.prixmoduleid = amp.prixmoduleid
				WHERE amp.articlemoduleid = ".$articlemoduleid." 
					AND pm.largeur = 0
				LIMIT 1" ;

$moduleFeature = $DB_site->query_first($request2);
$prixOptionImage = $moduleFeature[prixmodule];

if ( isset( $_SESSION['rapidpub'][$productID]['image'] ) AND !empty( $_SESSION['rapidpub'][$productID]['image'] ) ) {
	$elemT = $_SESSION['rapidpub'][$productID]['image'];
	foreach ( $elemT AS $element ) {
		$tarifImage = $prixOptionImage;
	}
}

//calcul tarif
$tarif = $tarifFond + $tarifImage;

$data = array(
	'productID'	=> $productID,
	'longueur' 	=> $longueur,
	'hauteur'	=> $hauteur,
	'surface'	=> $surface,
	'prixHT'	=> $tarif,
	'tarifFond'	=> $tarifFond,
	'tarifImage'	=> $tarifImage,
	'text'		=> $text
);

$_SESSION['rapidpub'][$productID]['info'] = $data;

echo json_encode( $data ); 
