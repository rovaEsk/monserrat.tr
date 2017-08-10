<?php
/**
* Export personnalisé par sélection des promotions
*/
	
ini_set('memory_limit','512M');
ini_set('max_execution_time','0');
set_time_limit(0);

require "includes/admin_global.php";

//print_r($_POST);

//construction des titres des colonnes
$contenu="";

if ($exporterTitre == 1){
	
	// Identifiant article
	if ($promotion_artid != "") $contenu.="$multilangue[identifiant_article];";
	
	// Site concerné
	if ($promotion_site != "") $contenu.="$multilangue[site_concerne];";
	
	// Référence article	
	if ($promotion_artcode != "") $contenu.="$multilangue[reference];";

	// Prix TTC article
	if ($promotion_prix != "") $contenu.="$multilangue[prix] $multilangue[ttc];";

	// Prix Promo TTC article
	if ($promotion_prixpromo != "") $contenu.="$multilangue[prix_promotion] $multilangue[ttc];";
	
	// Date début promotion
	if ($promotion_datedebut != "") $contenu.="$multilangue[date_debut];";

	// Date fin promotion
	if ($promotion_datefin != "") $contenu.="$multilangue[date_fin];";
	
	// Date saisie promotion
	if ($promotion_datesaisie != "") $contenu.="$multilangue[date_creation_promotion];";
	
	// Pourcentage de promotion
	if ($promotion_pourcentage != "") $contenu.="$multilangue[pourcentage];";
	
	// Devise
	if ($promotion_devise != "") $contenu.="$multilangue[devise];";	
	
	$contenu.="\n";
}


$nom_fichier_export = 'promotions.csv';

$filename = './export/csv/'.$nom_fichier_export;
if (!$handle = fopen($filename, 'w+')){
	echo "$multilangue[erreur_ouverture_fichier] ($filename)";
	exit;
}else{
	// UTF8 pour csv
	fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));
	if (fwrite($handle, $contenu) === FALSE) {
		echo "$multilangue[erreur_ecriture_fichier] ($filename)";
		exit;
	}
}

$nb_sites_select=0;
foreach ($critereSite as $siteid => $value){
	$tab_siteid[$siteid]=$siteid;
	$nb_sites_select++;
}

if($nb_sites_select == 1){
	foreach ($tab_siteid as $siteid){
		$and_siteid =" AND siteid = '$siteid'";
	}
}elseif($nb_sites_select > 1){
	$and_siteid = " AND siteid IN (";
	foreach ($tab_siteid as $siteid){
		$and_siteid .= "'$siteid',";
	}
	$and_siteid = substr($and_siteid,0,-1).")";
}

$sql="SELECT * FROM article_promo_site AS aps	
		WHERE pctpromo > 0 
		AND datefin > '".time()."'	
		$and_siteid					
		ORDER BY aps.datedebut";

/*echo $sql;
exit;*/

$promotions=$DB_site->query($sql);

while($promo=$DB_site->fetch_array($promotions)) {

	$site_promo = $DB_site->query_first("SELECT * FROM site WHERE siteid='$promo[siteid]'");
	$article_promo = $DB_site->query_first("SELECT * FROM article WHERE artid='$promo[artid]'");
	$article_site_promo = $DB_site->query_first("SELECT * FROM article_site WHERE artid='$promo[artid]' AND siteid='$promo[siteid]'");
	$devise_promo = $DB_site->query_first("SELECT * FROM devise WHERE deviseid='$site_promo[deviseid]'");	
		
	$contenu="";
	
	// Identifiant article
	if ($promotion_artid != ""){
		$contenu.=$promo[artid].";";
	}
	
	// Site concerné
	if ($promotion_site != ""){ 
		$contenu.="$site_promo[libelle];";
	}	

	// Référence article
	if ($promotion_artcode != ""){
		$contenu.=secure_chaine_csv($article_promo[artcode]).";";
	}

	// Prix TTC article
	if ($promotion_prix != ""){
		$contenu.=formaterPrix($article_site_promo[prix]).";";
	}

	// Prix Promo TTC article
	if ($promotion_prixpromo != ""){
		$contenu.=formaterPrix($article_site_promo[prix]-calculerPrixPourcent($article_site_promo[prix], $promo[pctpromo])).";";
	}

	// Date début promotion
	if ($promotion_datedebut != ""){
		$contenu.=date("d/m/Y H:i:s",$promo[datedebut]).";";
	}

	// Date fin promotion
	if ($promotion_datefin != ""){
		$contenu.=date("d/m/Y H:i:s",$promo[datefin]).";";
	}
	
	// Date saisie promotion
	if ($promotion_datesaisie != ""){
		$contenu.=date("d/m/Y H:i:s",$promo[datesaisie]).";";
	}
	
	// Pourcentage de promotion
	if ($promotion_pourcentage != ""){
		$contenu.=formaterPrix($promo[pctpromo]).";";
	}
	
	// Devise
	if ($promotion_devise != ""){
		 $contenu.="$devise_promo[symbole];";
	}
		
	$contenu.="\n";
	
	//echo $contenu;
	
	fwrite($handle,$contenu);
}

fclose($handle);
$file = realpath(".")."/export/csv/".$nom_fichier_export;
header('Content-Description: File Transfer');
header('Content-Type: application/force-download');
header('Content-Length: ' . filesize($file));
header('Content-Disposition: attachment; filename=' . basename($file));
readfile($file);
exit;

?>
