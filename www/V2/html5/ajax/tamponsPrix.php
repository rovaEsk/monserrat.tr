<?phpsession_start();if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {	if ( isset( $_SERVER['HTTP_ORIGIN'] ) ) {		$address = 'http://' . $_SERVER['SERVER_NAME'];		if ( strpos( $address, $_SERVER['HTTP_ORIGIN'] ) !== 0 ) {			exit( 'CSRF protection in POST request: detected invalid Origin header: ' . $_SERVER['HTTP_ORIGIN'] );		}	}}$productID = $_SESSION['prodID'];if ( !isset( $productID ) )	die();require_once ('../../global.php');$articlemoduleid = 9;$longueur = $_POST['bLong'];$hauteur = $_POST['bHaut'];$typeAdhesif = $_POST['typeAdhesif'];$surface = $longueur * $hauteur;$tarif = 0;$tarifFond = 10;$tarifMotif = 0;$tarifText = 0;$tarifImage = 0;$optionImage = false;$prixparm2 = 0;$prixModuleFondId = 0;//if($surface >= 10000){	/*switch ($typeAdhesif) {		case "simple":			//$prixparm2 = 15;			$prixModuleFondId = 122;			//calcul tarif motif			if ( isset( $_SESSION['rapidpub'][$productID]['motif'] ) AND !empty( $_SESSION['rapidpub'][$productID]['motif'] ) ) {				$elemM = $_SESSION['rapidpub'][$productID]['motif'];				foreach ( $elemM AS $element ) {					if ( isset( $element['tarif'] ) )						$tarifMotif += $element['tarif'];				}			}			//calcul tarif texte			if ( isset( $_SESSION['rapidpub'][$productID]['text'] ) AND !empty( $_SESSION['rapidpub'][$productID]['text'] ) ) {				$elemT = $_SESSION['rapidpub'][$productID]['text'];				foreach ( $elemT AS $element ) {					if ( isset( $element['tarif'] ) )						$tarifText += $element['tarif'];				}			}			break;	    case "imprime":			//$prixparm2 = 40;			$prixModuleFondId = 124;			//calcul tarif image			if ( isset( $_SESSION['rapidpub'][$productID]['image'] ) AND !empty( $_SESSION['rapidpub'][$productID]['image'] ) ) {				$elemT = $_SESSION['rapidpub'][$productID]['image'];				if(count($elemT)>=1){					$optionImage = true;				}			}			break;	    case "microperfore":			//$prixparm2 = 50;			$prixModuleFondId = 125;			//calcul tarif image			if ( isset( $_SESSION['rapidpub'][$productID]['image'] ) AND !empty( $_SESSION['rapidpub'][$productID]['image'] ) ) {				$elemT = $_SESSION['rapidpub'][$productID]['image'];				if(count($elemT)>=1){					$optionImage = true;				}			}			break;	}		$request = "SELECT * FROM prixarticlemodule WHERE prixmoduleid = ".$prixModuleFondId;	$prixArticleModuleFond = $DB_site->query_first($request);	$prixparm2 = $prixArticleModuleFond[prixmodule];	$tarifFond =  $surface / 10000 * $prixparm2;		if($optionImage){		$prixModuleImageId = 126;		$request2 = "SELECT * FROM prixarticlemodule WHERE prixmoduleid = ".$prixModuleImageId;		$prixArticleModuleImage = $DB_site->query_first($request2);		$tarifImage = $prixArticleModuleImage[prixmodule]; //15	}	*/	$tarif = $tarifFond + $tarifImage + $tarifMotif + $tarifText;//}$data = array(	/*'productID'	=> $productID,	'longueur' 	=> $longueur,	'hauteur'	=> $hauteur,	'surface'	=> $surface,*/	'prixHT'	=> $tarif,	'tarifMotif' => $tarifMotif,	'tarifText' => $tarifText,	'tarifImage' => $tarifImage,	'tarifFond' => $tarifFond,	/*'text'		=> $text*/);$_SESSION['rapidpub'][$productID]['info'] = $data;echo json_encode( $data ); 