<?phpsession_start();if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {	if ( isset( $_SERVER['HTTP_ORIGIN'] ) ) {		$address = 'http://' . $_SERVER['SERVER_NAME'];		if ( strpos( $address, $_SERVER['HTTP_ORIGIN'] ) !== 0 ) {			exit( 'CSRF protection in POST request: detected invalid Origin header: ' . $_SERVER['HTTP_ORIGIN'] );		}	}}$productID = $_SESSION['prodID'];if ( !isset( $productID ) )	die();require_once ('../../global.php');$articlemoduleid = 7;$categorieDimensionId = $_POST['categorieDimensionId'];$matiereId = $_POST['matiereId'];$fixation = $_POST['fixation'];//$surface = $longueur * $hauteur;$tarif = 0;$tarifFond = 20;$tarifMotif = 0;$tarifText = 0;$tarifImage = 0;$tarifFixation = 0;$optionImage = false;/** START PRIX par DIMENSION & MATIERE **/ $prixarticlemodule = $DB_site->query_first("select * from tarifs_plaques WHERE dimensions =$categorieDimensionId AND id_matiere =$matiereId");$tarifFond = $prixarticlemodule['prix_constate'];/** END PRIX par DIMENSION & MATIERE **//** START PRIX FIXATION **/ switch ($fixation) {    case 'aucune':        $tarifFixation = -2;        break;    case 'fix1':        $tarifFixation = 0;        break;    case 'fix2':        $tarifFixation = 0;        break;    case 'adhesifs':        $tarifFixation = -1;        break;    default:        $tarifFixation = 0;}/** END PRIX FIXATION **/ /*if($surface >= 10000){	switch ($typeAdhesif) {		case "simple":			//$prixparm2 = 15;			$prixModuleFondId = 122;			//calcul tarif motif			if ( isset( $_SESSION['rapidpub'][$productID]['motif'] ) AND !empty( $_SESSION['rapidpub'][$productID]['motif'] ) ) {				$elemM = $_SESSION['rapidpub'][$productID]['motif'];				foreach ( $elemM AS $element ) {					if ( isset( $element['tarif'] ) )						$tarifMotif += $element['tarif'];				}			}			//calcul tarif texte			if ( isset( $_SESSION['rapidpub'][$productID]['text'] ) AND !empty( $_SESSION['rapidpub'][$productID]['text'] ) ) {				$elemT = $_SESSION['rapidpub'][$productID]['text'];				foreach ( $elemT AS $element ) {					if ( isset( $element['tarif'] ) )						$tarifText += $element['tarif'];				}			}			break;	    case "imprime":			//$prixparm2 = 40;			$prixModuleFondId = 124;			//calcul tarif image			if ( isset( $_SESSION['rapidpub'][$productID]['image'] ) AND !empty( $_SESSION['rapidpub'][$productID]['image'] ) ) {				$elemT = $_SESSION['rapidpub'][$productID]['image'];				if(count($elemT)>=1){					$optionImage = true;				}			}			break;	    case "microperfore":			//$prixparm2 = 50;			$prixModuleFondId = 125;			//calcul tarif image			if ( isset( $_SESSION['rapidpub'][$productID]['image'] ) AND !empty( $_SESSION['rapidpub'][$productID]['image'] ) ) {				$elemT = $_SESSION['rapidpub'][$productID]['image'];				if(count($elemT)>=1){					$optionImage = true;				}			}			break;	}		$request = "SELECT * FROM prixarticlemodule WHERE prixmoduleid = ".$prixModuleFondId;	$prixArticleModuleFond = $DB_site->query_first($request);	$prixparm2 = $prixArticleModuleFond[prixmodule];	$tarifFond =  $surface / 10000 * $prixparm2;		if($optionImage){		$prixModuleImageId = 126;		$request2 = "SELECT * FROM prixarticlemodule WHERE prixmoduleid = ".$prixModuleImageId;		$prixArticleModuleImage = $DB_site->query_first($request2);		$tarifImage = $prixArticleModuleImage[prixmodule]; //15	}		$tarif = $tarifFond + $tarifImage + $tarifMotif + $tarifText;}*/$tarif = $tarifFond + $tarifImage + $tarifMotif + $tarifText + $tarifFixation;$data = array(	/*'productID'	=> $productID,	'longueur' 	=> $longueur,	'hauteur'	=> $hauteur,	'surface'	=> $surface,*/	'prixHT'	=> $tarif,	'tarifMotif' => $tarifMotif,	'tarifText' => $tarifText,	'tarifImage' => $tarifImage,	'tarifFond' => $tarifFond,	'tarifFixation' => $tarifFixation,	'categorieDimensionId' => $categorieDimensionId,	'matiereId' => $matiereId,	/*'text'		=> $text*/);$_SESSION['rapidpub'][$productID]['info'] = $data;echo json_encode( $data ); 