<?
header('Content-type: text/html; charset=UTF-8'); 
require_once("includes/admin_global.php");


if (isset($action) && $action == "produits_positionsphotos"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE article_caractval_photo SET position = '$position' WHERE articlecaractvalphotoid = '$value'");
		}
	}
}

if (isset($action) && $action == "produits_positionsconseils"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE article_conseil SET position = '$position' WHERE id = '$value'");
		}
	}
}

if (isset($action) && $action == "produits_positionscomplements"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE article_complement SET position = '$position' WHERE id = '$value'");
		}
	}
}

if (isset($action) && $action == "produits_positionschamps"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE article_champ SET position = '$position' WHERE articlechampid = '$value'");
		}
	}
}

if (isset($action) && $action == "produits_positionschamps"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE article_champ SET position = '$position' WHERE articlechampid = '$value'");
		}
	}
}

if (isset($action) && $action == "categorie_positions"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE categorie SET position = '$position' WHERE catid = '$value'");
		}
	}
}

if (isset($action) && $action == "categorie_positionsarticles"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE position SET position = '$position' WHERE artid = '$value' AND catid = '$catid'");
		}
	}
}

if (isset($action) && $action == "marques_positions"){
	if($ordre!=""){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE marque SET position = '$position' WHERE marqueid = '$value'");
		}
	}
}

if (isset($action) && $action == "marques_positionsarticle"){
	if($ordre!=""){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE article_marque SET position = '$position' WHERE artid = '$value' AND marqueid = '$marqueid'");
		}
	}
}


if (isset($action) && $action == "caracteristiques_positions"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE caracteristique SET position = '$position' WHERE caractid = '$value'");
		}
	}
}

if (isset($action) && $action == "caracteristiques_positionsvaleurs"){
	if($ordre){
		$position = 0 ;
		$liste = explode(";", $ordre) ;
		foreach ($liste as $key => $value) {
			$position++ ;
			$DB_site->query("UPDATE caracteristiquevaleur SET position = '$position' WHERE caractvalid = '$value'");
		}
	}
}

if (isset($action) && $action == "carrousel_administrable_positions"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE diapositive SET position = '$position' WHERE diapositiveid = '$value'");
		}
	}
}

if (isset($action) && $action == "carrousel_administrable_positionsarticles"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE diapositive_article SET position = '$position' WHERE artid = '$value'");
		}
	}
}

if (isset($action) && $action == "telechargement_positions"){
	if($ordre){
		$position = 0 ;
		$liste = explode(";", $ordre) ;
		foreach ($liste as $key => $value) {
			$position++ ;
			$DB_site->query("UPDATE document SET position = '$position' WHERE documentid = '$value'");
		}
	}
}

if (isset($action) && $action == "formulaires_positions"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE formulaire_champ SET position = '$position' WHERE formulairechampid = '$value'");
		}
	}
}

if (isset($action) && $action == "instit_positions"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE institutionnel SET position = '$position' WHERE institutionnelid = '$value' AND menu = '$menu'");
		}
	}
}

if (isset($action) && $action == "menus_faq_positions"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE faq_menu SET position = '$position' WHERE menuid = '$value'");
		}
	}
}

if (isset($action) && $action == "faq_positions"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE faq SET position = '$position' WHERE faqid='$value' AND menuid = '$menuid'");
		}
	}
}

if (isset($action) && $action == "moyens_reglement_positions"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE moyenpaiement SET position = '$position' WHERE moyenid = '$value'");
		}
	}
}


if (isset($action) && $action == "modes_livraison_positions"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE mode_livraison SET position = '$position' WHERE modelivraisonid = '$value'");
		}
	}
}

if (isset($action) && $action == "echange_liens_positions"){
	if($ordre){
		$position = 0;
		$liste = explode(";", $ordre);
		foreach ($liste as $key => $value) {
			$position++;
			$DB_site->query("UPDATE liens SET position = '$position' WHERE lienid = '$value'");
		}
	}
}

if (isset($action) && $action == "produits_activer"){
	$active = ($color == "green" ? 0 : 1);
	$DB_site->query("UPDATE article_site SET active$version = '$active' WHERE artid = '$artid'");
	$color = ($color == "green" ? "red" : "green");
	$tooltipvisible = ($color == "green" ? $multilangue[desactiver] : $multilangue[activer]);
	echo $artid . "_" . $color . "_" . $tooltipvisible . "_" . $version;
}

if (isset($action) && $action == "produits_activer_site"){
	$active = ($color == "green" ? 0 : 1);
	$DB_site->query("UPDATE article_site SET active$version = '$active' WHERE artid = '$artid' AND siteid = '$siteid'");
	$color = ($color == "green" ? "red" : "green");
	$tooltipvisible = ($color == "green" ? $multilangue[desactiver] : $multilangue[activer]);
	echo $artid . "_" . $color . "_" . $tooltipvisible . "_" . $version . "_" . $siteid;
}

if (isset($action) && $action == "encarts_perso_activer"){
	$active = ($color == "green" ? 0 : 1);
	$DB_site->query("UPDATE autopromo SET active = '$active' WHERE autopromoid = '$autopromoid'");
	$color = ($color == "green" ? "red" : "green");
	$tooltipvisible = ($color == "green" ? $multilangue[desactiver] : $multilangue[activer]);
	echo $autopromoid . "_" . $color . "_" . $tooltipvisible;
}

if (isset($action) && $action == "fond_administrable_activer"){
	$active = ($color == "green" ? 0 : 1);
	$DB_site->query("UPDATE background_site SET active$version = '$active' WHERE backgroundid = '$backgroundid'");
	$color = ($color == "green" ? "red" : "green");
	$tooltipvisible = ($color == "green" ? $multilangue[desactiver] : $multilangue[activer]);
	echo $backgroundid . "_" . $color . "_" . $tooltipvisible . "_" . $version;
}

if (isset($action) && $action == "formulaires_activer"){
	$scriptcourant = "formulaires.php";
	if($admin_droit[$scriptcourant][ecriture]){
		$actif = ($color == "green" ? 0 : 1);
		$DB_site->query("UPDATE formulaire SET actif = '$actif' WHERE formulaireid = '$formulaireid'");
		$color = ($color == "green" ? "red" : "green");
		$tooltipvisible = ($color == "green" ? $multilangue[desactiver] : $multilangue[activer]);
		echo $formulaireid . "_" . $color . "_" . $tooltipvisible;
	}else{
		header("HTTP/1.1 503 $multilangue[action_page_refuse]");
		exit;
	}
}

if (isset($action) && $action == "codes_reduction_activer"){
	$active = ($color == "green" ? 0 : 1);
	$DB_site->query("UPDATE cadeau SET active = '$active' WHERE cadeauid = '$cadeauid'");
	$color = ($color == "green" ? "red" : "green");
	$tooltipvisible = ($color == "green" ? $multilangue[desactiver] : $multilangue[activer]);
	echo $cadeauid . "_" . $color . "_" . $tooltipvisible;
}

if (isset($action) && $action == "produits_commandable"){
	$commandable = ($color == "green" ? 0 : 1);
	$DB_site->query("UPDATE article_site SET commandable = '$commandable' WHERE artid = '$artid'");
	$color = ($color == "green" ? "red" : "green");
	$tooltipvisible = ($color == "green" ? $multilangue[desactiver] : $multilangue[activer]);
	echo $artid . "_" . $color . "_" . $tooltipvisible;
}

if (isset($action) && $action == "categorie_visible"){
	$visible = ($color == "green" ? 0 : 1);
	$DB_site->query("UPDATE categorie_site SET visible_treeview$version = '$visible' WHERE catid = '$catid'");
	$color = ($color == "green" ? "red" : "green");
	$tooltipvisible = ($color == "green" ? $multilangue[passer_invisible] : $multilangue[passer_visible]);
	echo $catid . "_" . $color . "_" . $tooltipvisible . "_" . $version;
}

if (isset($action) && $action == "echange_liens_visible"){
	$visible = ($color == "green" ? 0 : 1);
	$DB_site->query("UPDATE liens SET encart = '$visible' WHERE lienid = '$lienid'");
	$color = ($color == "green" ? "red" : "green");
	$tooltipvisible = ($color == "green" ? $multilangue[passer_invisible] : $multilangue[passer_visible]);
	echo $lienid . "_" . $color . "_" . $tooltipvisible;
}

if (isset($action) && $action == "promotion_avancee_visible"){
	$visible = ($color == "green" ? 0 : 1);
	$DB_site->query("UPDATE operation SET active$version = '$visible' WHERE operationid = '$operationid'");
	$color = ($color == "green" ? "red" : "green");
	$tooltipvisible = ($color == "green" ? $multilangue[passer_invisible] : $multilangue[passer_visible]);
	echo $operationid . "_" . $color . "_" . $tooltipvisible . "_" . $version;
}


?>