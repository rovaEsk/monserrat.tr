<?php
include "./includes/header.php";

ini_set('memory_limit','512M');
ini_set('max_execution_time','0');

$referencepage="import_export_csv";
$pagetitle = "Import / export CSV - $host - Admin Arobases";

$_SESSION[debug_trace]="";
$_SESSION[debug] = 0;
//$mode = "test_modules";

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


if ($action == 'restaurer'){
	echo "
	<div class=\"blocraccourci\">
	<div class=\"raccourci\">
	<div class=\"titrefleche\">$multilangue[restaurer_donnees] - $multilangue[etape] 2</div>
	<form action=\"import_csv.php\" name=\"restauration\" id=\"formimport\" method=\"post\">
	<input type=\"hidden\" name=\"action\" value=\"dorestaurer\">
	<input type=\"hidden\" name=\"fichier\" value=\"$fichier\">
	<center>$multilangue[restauration_efface_modif_ajout]";
	switch ($fichier) {
		case 'article' :
			echo (date ( 'd/m/Y H:i:s', $params ['last_backup_import_article'] )) . " $multilangue[restauration_articles].";
			break;
		case 'categorie' :
			echo (date ( 'd/m/Y H:i:s', $params ['last_backup_import_categorie'] )) . " $multilangue[restauration_categories].";
			break;
		case 'promotion' :
			echo (date ( 'd/m/Y H:i:s', $params ['last_backup_import_promotion'] )) . " $multilangue[restauration_promotions].";
			break;
	}
	echo "
	<br><br>
	<input type=\"submit\" value=\"$multilangue[valider]\" class=\"btn_standard\">
	<a href=\"import_csv.php\" class=\"btn_retour\">$multilangue[retour]</a></center>
	</form>
	</div>
	</div>";
}

if ($action == 'dorestaurer') {
	if($admin_droit[$scriptcourant][ecriture]){
		echo "
		<div class=\"blocraccourci\">
		<div class=\"raccourci\">
		<div class=\"titrefleche\">$multilangue[restaurer_donnees] - $multilangue[etape] 3</div>
				<center>";
		if (loadDataAfterImportFail ( $DB_site, $fichier ) === true) {
			echo "<span class=\"vert\">$multilangue[restauration_ok]</span>";
		} else {
			echo "<span class=\"erreur\">$multilangue[restauration_ko]</span>";
		}
		echo "
		<br><br>
		<a href=\"import_csv.php\" class=\"btn_retour\">$multilangue[retour]</a></center>
		</div>
		</div>";
	}else{
		header('location: import_export_csv.php?erreurdroits=1');	
	}
}



/** ************************************ ETAPE 4 ***********************************
 ********************************* Import des données ******************************
 ******************************************************************************** **/
if ($action == "importbddpromotion"){
	if($admin_droit[$scriptcourant][ecriture]){
		$tabChamps = $_SESSION[tabChampsPromotions];
		//print_r($tabChamps);
		$nblignes = count($tabChamps);
	
		$compteurmodifs=0;
		if ($exclureEntete == 1){
			$lignedeb=2;
			$nblignes++;
		}else{
			$lignedeb=1;
		}
	
		$nbInsertions = 0;
		$nbModifications = 0;
	
		$nb_articles_non_trouves=0;
		$nb_sites_non_trouves=0;
		$nb_pas_importes_existe_deja=0;
		
		for ($ligne=$lignedeb;$ligne<=$nblignes;$ligne++){
			// Récupération de toutes les informations de la ligne de promotion
			$artid = $tabChamps[$ligne][artid];
			$libsite = utf8_encode($tabChamps[$ligne][libsite]);
			$artcode = $tabChamps[$ligne][artcode];
			$prix = str_replace(",",".",$tabChamps[$ligne][prix]);
			$prixpromo = str_replace(",",".",$tabChamps[$ligne][prixpromo]);		
			
			$datedebut = $tabChamps[$ligne][datedebut];
			$datefin = $tabChamps[$ligne][datefin];
			
			$split_datedebut = explode(" ",$datedebut);
			if ($split_datedebut[1] == "") {
				$split_datedebut[1] = "00:00:00";	
			}			
			$tab_datedebut = explode("/",$split_datedebut[0]);		
			$jour_debut=$tab_datedebut[0];
			$mois_debut=$tab_datedebut[1];
			$annee_debut=$tab_datedebut[2];
			
			
			$tab_heuredebut = explode(":",$split_datedebut[1]);		
			$heure_debut=$tab_heuredebut[0];
			$minute_debut=$tab_heuredebut[1];
			$seconde_debut=$tab_heuredebut[2];
			
			$tm_datedebut=mktime ($heure_debut,$minute_debut,$seconde_debut,$mois_debut,$jour_debut,$annee_debut);
	
			
			$split_datefin = explode(" ",$datefin);
			if ($split_datefin[1] == "") {
				$split_datefin[1] = "00:00:00";	
			}			

			
			$tab_datefin = explode("/",$split_datefin[0]);
			$jour_fin=$tab_datefin[0];
			$mois_fin=$tab_datefin[1];
			$annee_fin=$tab_datefin[2];
			
			$tab_heurefin = explode(":",$split_datefin[1]);
			$heure_fin=$tab_heurefin[0];
			$minute_fin=$tab_heurefin[1];
			$seconde_fin=$tab_heurefin[2];
			
			$tm_datefin=mktime ($heure_fin,$minute_fin,$seconde_fin,$mois_fin,$jour_fin,$annee_fin);
									
			// On calcule le pourcentage de promotion à rentrer
			$pctpromo = (1 - ($prixpromo/$prix))*100;
			
			// On vérifie que l'on trouve bien le site correspondant au libelle
			$site_promo = $DB_site->query_first("SELECT * FROM site WHERE libelle = '".securiserSql($libsite)."'");
			if($site_promo[siteid] != ""){
				// On vérifie que l'on trouve bien l'article correspondant
				$article_promo = $DB_site->query_first("SELECT * FROM article 
														WHERE artcode = '".securiserSql($artcode)."'
														AND  artid = '".securiserSql($artid)."'");
				
				if($article_promo[artid] != ""){				
					// On vérifie qu'une même ligne n'existe pas déjà
					$existe_promo = $DB_site->query_first("SELECT * FROM article_promo_site 
															WHERE artid = '".securiserSql($artid)."'
															AND siteid = '$site_promo[siteid]'
															AND pctpromo = '$pctpromo'
															AND datedebut = '$tm_datedebut'
															AND datefin = '$tm_datefin'");				
					
					if($existe_promo[promoid] == ""){
						// Elle existe pas encore, on importe 
						
						$sql = "INSERT INTO article_promo_site (artid,siteid,pctpromo,datedebut,datefin,datesaisie) 
								VALUES ('".securiserSql($artid)."','$site_promo[siteid]','$pctpromo','$tm_datedebut',
								'$tm_datefin','".time()."')";
						
						clearDir($GLOBALS[rootpath]."configurations/".$GLOBALS[host]."/cache/articles/".$artid);
						
						if($_SESSION[debug]){
							$_SESSION[debug_trace] .= "$sql<br>";
						}else{
							$DB_site->query($sql);
						}
						
						$nbInsertions++;
					}else{
						$nb_pas_importes_existe_deja++;
					}
				}else{
					$nb_articles_non_trouves++;
				}	
			}else{
				$nb_sites_non_trouves++;
			}
		}
		
		
		$message = "$nbInsertions promotions importées.<br>";
		if($nb_pas_importes_existe_deja > 0){
			$message .= "$nb_pas_importes_existe_deja promotions non importées car déjà existantes.<br>";
		}
		if($nb_articles_non_trouves > 0){
			$message .= "$nb_articles_non_trouves promotions non importées car l'article correspondant n'existe pas.<br>";
		}
		if($nb_sites_non_trouves > 0){
			$message .= "$nb_sites_non_trouves promotions non importées car le site concerné n'existe pas.<br>";
		}
				
		eval(charge_template($langue,$referencepage,"ImportEtape4Promotions"));
	}else{
		header('location: import_export_csv.php?erreurdroits=1');	
	}
}



if ($action == "importbddcategorie"){
	if($admin_droit[$scriptcourant][ecriture]){
		$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
		while ($site = $DB_site->fetch_array($sites)){
			$tab_siteid[$site[siteid]]=$site[siteid];
		}
		
		$champIdentifiant = "catid";
		
		$tabChamps = $_SESSION[tabChampsCategories];
		//print_r($tabChamps);
		$nblignes = count($tabChamps);
		
		$compteurmodifs=0;
		if ($exclureEntete == 1){
			$lignedeb=2;
			$nblignes++;
		}else{
			$lignedeb=1;
		}
		
		$nbInsertions = 0;
		$nbModifications = 0;	
		
		for ($ligne=$lignedeb;$ligne<=$nblignes;$ligne++){
							
			//Si colonne identifiant, on regarde si la catid existe
			$requeteInsert = 1;
			if ($tabChamps[$ligne][$champIdentifiant] != '') {
				$existe = $DB_site->query_first("SELECT $champIdentifiant FROM categorie WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."'");
				if ($existe[$champIdentifiant]) {
					$requeteInsert = 0;
				}
			}
			
			if ($requeteInsert == 1) {
				$sql="INSERT INTO categorie (catid) VALUES ('')";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
				$tabChamps[$ligne][$champIdentifiant] = $DB_site->insert_id();
					
				// On sait jamais...
				$sql="DELETE FROM categorie_site WHERE catid='".$tabChamps[$ligne][$champIdentifiant]."'";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
					
				foreach ($tab_siteid as $siteid){
					$sql="INSERT INTO categorie_site (catid,siteid) VALUES ('".$tabChamps[$ligne][$champIdentifiant]."','$siteid')";
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= "$sql<br>";
					}else{
						$DB_site->query($sql);
					}
				}
				$nbInsertions++;
			}else{
				$nbModifications++;
			}
			
			$sqlCategorie = "UPDATE categorie SET";
			foreach ($tab_siteid as $siteid){
				${"sqlCategorieSite".$siteid} = "UPDATE categorie_site SET";
			}
			
			// Libellés
			foreach ($tab_siteid as $siteid){			
				if (isset($tabChamps[$ligne]['libelle'.$siteid])) {
					${"sqlCategorieSite".$siteid} .= " libelle = '".securiserSql($tabChamps[$ligne]['libelle'.$siteid])."',";
					$compteurmodifs++;
				}			
			}
			
			// Descriptions
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['description'.$siteid])) {
					${"sqlCategorieSite".$siteid} .= " description = '".securiserSql($tabChamps[$ligne]['description'.$siteid],"html")."',";
					$compteurmodifs++;
				}
			}
			
			// Meta title
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['ref_title'.$siteid])) {
					${"sqlCategorieSite".$siteid} .= " ref_title = '".securiserSql($tabChamps[$ligne]['ref_title'.$siteid])."',";
					$compteurmodifs++;
				}
			}
			
			// Meta description
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['ref_description'.$siteid])) {
					${"sqlCategorieSite".$siteid} .= " ref_description = '".securiserSql($tabChamps[$ligne]['ref_description'.$siteid])."',";
					$compteurmodifs++;
				}
			}
			
			// Meta keywords
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['ref_keywords'.$siteid])) {
					${"sqlCategorieSite".$siteid} .= " ref_keywords = '".securiserSql($tabChamps[$ligne]['ref_keywords'.$siteid])."',";
					$compteurmodifs++;
				}
			}
					
			//module couleur de rayon		
			if (isset($tabChamps[$ligne]['color'])) {
				$sqlCategorie .= " color = '".securiserSql($tabChamps[$ligne]['color'])."',";
				$compteur++;
			}
				
			//module couleur de fond du rayon		
			if (isset($tabChamps[$ligne]['color_back'])) {
				$sqlCategorie .= " color_back = '".securiserSql($tabChamps[$ligne]['color_back'])."',";
				$compteur++;
			}
				
			//module couleur de survol du rayon	
			if (isset($tabChamps[$ligne]['color_survol'])) {
				$sqlCategorie .= " color_survol = '".securiserSql($tabChamps[$ligne]['color_survol'])."',";
				$compteur++;
			}
	
	
			
			####################################################
			### MAJ des tables article & article_description ###
			####################################################
			if ($sqlCategorie != "UPDATE categorie SET"){
				$sqlCategorie = substr($sqlCategorie, 0, -1);
				$sqlCategorie .= " WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."'";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= $sqlCategorie.'<br>';
				}else{
					$DB_site->query($sqlCategorie);
				}
			}
				
			foreach ($tab_siteid as $siteid){
				if (${"sqlCategorieSite".$siteid} != "UPDATE categorie_site SET"){				
					${"sqlCategorieSite".$siteid} = substr(${"sqlCategorieSite".$siteid}, 0, -1);
					${"sqlCategorieSite".$siteid} .= " WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."' AND siteid='$siteid'";
						
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= ${"sqlCategorieSite".$siteid}.'<br>';
					}else{
						$DB_site->query(${"sqlCategorieSite".$siteid});
					}
				}
			}
	
		
		}
		$message = "$nbInsertions $multilangue[categorie_s_creee_s]<br>
					$nbModifications $multilangue[categorie_s_modifiee_s]<br><br>";
				
		/*echo "
			<center>
				<font class=\"erreur\">$message</font>
				<br><br>
				<a href=\"import_csv.php\" class=\"btn_retour\">$multilangue[retour]</a>
				</center>";*/
		/*$folder = $rootpath."configurations/$host/importcsv";
		$dossier = opendir($folder);*/
		/*while ($fichier = readdir($dossier)){
			if ($fichier != "." && $fichier != ".."){
				if (file_exists($folder."/import".$type.".csv")){
					unlink($folder."/import".$type.".csv");
				}
			}
		}*/
		eval(charge_template($langue,$referencepage,"ImportEtape4Categories"));
	}else{
		header('location: import_export_csv.php?erreurdroits=1');	
	}
}


if ($action == 'importbddarticle'){
	if($admin_droit[$scriptcourant][ecriture]){
		$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
		while ($site = $DB_site->fetch_array($sites)){
			$tab_siteid[$site[siteid]]=$site[siteid];
		}
		
		$champIdentifiant = "artid";
		
		//saveDataBeforeImport($DB_site,'article');
		
		/*$nom_fic = $rootpath."configurations/$host/importcsv/import".$type.".csv" ;
		$rh = fopen($nom_fic, 'rb');
		if ($rh) {
			$row = 1 ;
			while ($data = fgetcsv ($rh, 10000, ";")) 	{
				$num = count ($data);
				for ($c = 0 ; $c < $num ; $c++) {
					$tableau[$row][$c] = $data[$c] ;
				}
				$row++;
			}
		}
		fclose($rh);*/
		
		//$tabChamps = actionsImportArticles($tableau);
		
		$tabChamps = $_SESSION[tabChampsArticles];
		//print_r($tabChamps);
		$nblignes = count($tabChamps);
		$valeurdefaut = 0;
		$compteurmodifs=0;
		if ($exclureEntete == 1){
			$lignedeb=2;
			$nblignes++;
		}else{
			$lignedeb=1;
		}	
		
		$nbInsertions = 0;	
		$nbModifications = 0;	
		
		for ($ligne=$lignedeb;$ligne<=$nblignes;$ligne++){		
			//Si colonne identifiant, on regarde si l'artid existe
			$requeteInsert = 1;
			if ($tabChamps[$ligne][$champIdentifiant] != '') {
				$existe = $DB_site->query_first("SELECT $champIdentifiant FROM article WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."'");
				if ($existe[$champIdentifiant]) {
					$requeteInsert = 0;				
				}
			}
			if ($requeteInsert == 1) {
				$sql="INSERT INTO article (artid) VALUES ('')";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
				$tabChamps[$ligne][$champIdentifiant] = $DB_site->insert_id();
				
				// On sait jamais...			
				$sql="DELETE FROM article_site WHERE artid='".$tabChamps[$ligne][$champIdentifiant]."'";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
				
				foreach ($tab_siteid as $siteid){			
					$sql="INSERT INTO article_site (artid,siteid) VALUES ('".$tabChamps[$ligne][$champIdentifiant]."','$siteid')";
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= "$sql<br>";
					}else{
						$DB_site->query($sql);
					}
				}
				$nbInsertions++;
			}else{
				$nbModifications++;
			}
			
			$sqlArticle = "UPDATE article SET";
			foreach ($tab_siteid as $siteid){
				${"sqlArticleSite".$siteid} = "UPDATE article_site SET";
			}
			
			//Référence
			if (isset($tabChamps[$ligne]['artcode'])) {
				$sqlArticle .= " artcode = '".securiserSql($tabChamps[$ligne]['artcode'])."',";
				$compteurmodifs++;
			}elseif(isset($defaut_article_artcode) && $defaut_article_artcode != ''){
				$sqlArticle .= " artcode = '".securiserSql($defaut_article_artcode)."',";
				$valeurdefaut++;
			}
			
			//Identifiant catégorie
			if (isset($tabChamps[$ligne]['catid0'])) {
				$existeCategorie = $DB_site->query_first("SELECT catid FROM categorie WHERE catid = '".$tabChamps[$ligne]['catid0']."'");
				if ($existeCategorie[catid] == "") {
					$tabChamps[$ligne]['catid0'] = 0;
				}			
				$sql="DELETE FROM position WHERE artid = '".$tabChamps[$ligne][$champIdentifiant]."'";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
				
				$sqlArticle .= " catid = '".$tabChamps[$ligne]['catid0']."',";
				
				$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM position WHERE catid = '".$tabChamps[$ligne]['catid0']."'");
				$position = $lastPosition[0] + 1;
				$sql="INSERT INTO position (catid, artid, position) VALUES ('".$tabChamps[$ligne]['catid0']."', '".$tabChamps[$ligne][$champIdentifiant]."', '$position')";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
				
				/*$icat = 1;
				while (isset($tabChamps[$ligne]['catid'.$icat])){
					$existeCategorie = $DB_site->query_first("SELECT catid FROM categorie WHERE catid = '".$tabChamps[$ligne]['catid'.$icat]."'");
					if ($existeCategorie[catid] != "") {
						//$verifLiaison = $DB_site->query_first("SELECT COUNT($champIdentifiant) FROM position WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."' AND catid = '".$tabChamps[$ligne]['catid'.$icat]."'");
						//if($verifLiaison[0] == 0) {
						$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM position WHERE catid = '".$tabChamps[$ligne]['catid'.$icat]."'");
						$position = $lastPosition[0] + 1;
						$DB_site->query("INSERT INTO position (catid, artid, position) VALUES ('".$tabChamps[$ligne]['catid'.$icat]."', '".$tabChamps[$ligne][$champIdentifiant]."', '$position')");
						//}
					}
					$icat++;
				}*/
				$compteurmodifs++;
			}elseif($defaut_article_catid != ''){
				$existeCategorie = $DB_site->query_first("SELECT catid FROM categorie WHERE catid = '".securiserSql($defaut_article_catid,"int")."'");
				if ($existeCategorie[catid] == "") {
					$tabChamps[$ligne]['catid0'] = 0;
				}
				$sql="DELETE FROM position WHERE artid = '".$tabChamps[$ligne][$champIdentifiant]."'";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
				
				$sqlArticle .= " catid = '".$defaut_article_catid."',";
				
				$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM position WHERE catid = '".securiserSql($defaut_article_catid,"int")."'");
				$position = $lastPosition[0] + 1;
				$sql="INSERT INTO position (catid, artid, position) VALUES ('".securiserSql($defaut_article_catid,"int")."', '".$tabChamps[$ligne][$champIdentifiant]."', '$position')";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
				
				$valeurdefaut++;
			}elseif($requeteInsert){
				
				$sqlArticle .= " catid = '0',";
				
				$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM position WHERE catid = '0'");
				$position = $lastPosition[0] + 1;
				$sql="INSERT INTO position (catid, artid, position) VALUES ('0', '".$tabChamps[$ligne][$champIdentifiant]."', '$position')";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
			}
			
			//Code EAN général
			if (isset($tabChamps[$ligne]['code_EAN'])) {
				$sqlArticle .= " code_EAN = '".securiserSql($tabChamps[$ligne]['code_EAN'])."',";
				$compteurmodifs++;
			}	
			
			//Code ASIN général
			if (isset($tabChamps[$ligne]['ASIN'])) {
				$sqlArticle .= " ASIN = '".securiserSql($tabChamps[$ligne]['ASIN'])."',";
				$compteurmodifs++;
			}		
		
			//Référence fabricant générale
			if (isset($tabChamps[$ligne]['reference_fabricant'])) {
				$sqlArticle .= " reference_fabricant = '".securiserSql($tabChamps[$ligne]['reference_fabricant'])."',";
				$compteurmodifs++;
			}elseif($defaut_article_reference_fabricant != ''){
				$sqlArticle .= " reference_fabricant = '".securiserSql($defaut_article_reference_fabricant)."',";
				$valeurdefaut++;
			}
		
			//Numéro tarifaire La Poste		
			if (isset($tabChamps[$ligne]['numero_tarifaire_laposte'])) {
				$sqlArticle .= " numero_tarifaire_laposte = '".securiserSql($tabChamps[$ligne]['numero_tarifaire_laposte'])."',";
				$compteurmodifs++;
			} elseif($defaut_article_numero_tarifaire_laposte != ''){
				$sqlArticle .= " numero_tarifaire_laposte = '".securiserSql($defaut_article_numero_tarifaire_laposte)."',";
				$valeurdefaut++;
			}
				
			//Libellés
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['libelle'.$siteid])) {
					${"sqlArticleSite".$siteid} .= " libelle = '".securiserSql($tabChamps[$ligne]['libelle'.$siteid])."',";
					$compteurmodifs++;
				}elseif(${'defaut_article_libelle'.$siteid} != ""){
					${"sqlArticleSite".$siteid} .= " libelle = '".securiserSql(${'defaut_article_libelle'.$siteid})."',";
					$valeurdefaut++;
				}elseif(isset($tabChamps[$ligne]['artcode']) && $requeteInsert == 1) {
					${"sqlArticleSite".$siteid} .= " libelle = '".securiserSql($tabChamps[$ligne]['artcode'])."',";
					$compteurmodifs++;
				}
			}
		
			//Sous titres
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['titre2'.$siteid])) {
					${"sqlArticleSite".$siteid} .= " titre2 = '".securiserSql($tabChamps[$ligne]['titre2'.$siteid])."',";
					$compteurmodifs++;
				}elseif(${'defaut_article_titre2'.$siteid} != ''){
					${"sqlArticleSite".$siteid} .= " titre2 = '".securiserSql(${'defaut_article_titre2'.$siteid})."',";
					$valeurdefaut++;
				}
			}
		
			//Google shop
			if (isset($tabChamps[$ligne]['googleshop']) && $tabChamps[$ligne]['googleshop'] != "") {
				if ($tabChamps[$ligne]['googleshop'] >= 0){
					$sql="DELETE FROM googleshopping WHERE artid = '".$tabChamps[$ligne][$champIdentifiant]."'";
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= "$sql<br>";
					}else{
						$DB_site->query($sql);
					}
					
					$sql="INSERT INTO googleshopping VALUES ('".$tabChamps[$ligne]['googleshop']."','".$tabChamps[$ligne][$champIdentifiant]."')";
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= "$sql<br>";
					}else{
						$DB_site->query($sql);
					}
				}elseif($defaut_article_googleshop != ''){
					$sql="DELETE FROM googleshopping WHERE artid = '".$tabChamps[$ligne][$champIdentifiant]."'";
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= "$sql<br>";
					}else{
						$DB_site->query($sql);
					}
					
					$sql="INSERT INTO googleshopping VALUES ('".securiserSql($defaut_article_googleshop,"int")."','".$tabChamps[$ligne][$champIdentifiant]."')";
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= "$sql<br>";
					}else{
						$DB_site->query($sql);
					}
				}
			}
		
			//Marques
			if (isset($tabChamps[$ligne]['marques'])) {
				$sql="DELETE FROM article_marque WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."'";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
				
				if ($tabChamps[$ligne]['marques']) {
					$tabMarques = explode("|", $tabChamps[$ligne]['marques']);
					foreach ($tabMarques as $libMarque) {
						$existeMarque = $DB_site->query_first("SELECT marqueid FROM marque_site WHERE siteid='1' AND libelle = '".securiserSql($libMarque)."'");
						if ($existeMarque[marqueid] == "") {
							$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM marque");
							$position = $lastPosition[0] + 1;
							$sql="INSERT INTO marque (position) VALUES ('$position')";
							if($_SESSION[debug]){
								$_SESSION[debug_trace] .= "$sql<br>";
								$tabChamps[$ligne]['marqueid'] = "MARQUEID_DEBUG";
							}else{
								$DB_site->query($sql);
								$tabChamps[$ligne]['marqueid'] = $DB_site->insert_id();
							}						
							
							foreach ($tab_siteid as $siteid){
								$sql="INSERT INTO marque_site (marqueid, siteid, libelle) 
										VALUES ('".$tabChamps[$ligne]['marqueid']."','$siteid','".securiserSql($libMarque)."')";
								if($_SESSION[debug]){
									$_SESSION[debug_trace] .= "$sql<br>";
								}else{
									$DB_site->query($sql);
								}
							}				
						}else{
							$tabChamps[$ligne]['marqueid'] = $existeMarque[marqueid];
						}
						$sql="INSERT INTO article_marque (artid, marqueid) VALUES ('".$tabChamps[$ligne][$champIdentifiant]."', '".$tabChamps[$ligne]['marqueid']."')";
						if($_SESSION[debug]){
							$_SESSION[debug_trace] .= "$sql<br>";
						}else{
							$DB_site->query($sql);
						}
					}
				}
			}elseif($defaut_article_marques != ''){
				$DB_site->query("DELETE FROM article_marque WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."'");
				$sql="DELETE FROM article_marque WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."'";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
				
				if ($defaut_article_marques) {
					$tabMarques = explode("|", $defaut_article_marques);
					foreach ($tabMarques as $libMarque) {
						$existeMarque = $DB_site->query_first("SELECT marqueid FROM marque WHERE libelle = '".securiserSql($libMarque)."'");
						if ($existeMarque[marqueid] == "") {
							$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM marque");
							$position = $lastPosition[0] + 1;
							$sql="INSERT INTO marque (libelle, position) VALUES ('".securiserSql($libMarque)."', '$position')";
							if($_SESSION[debug]){
								$_SESSION[debug_trace] .= "$sql<br>";
								$tabChamps[$ligne]['marqueid'] = "MARQUEID_DEBUG";
							}else{
								$DB_site->query($sql);
								$tabChamps[$ligne]['marqueid'] = $DB_site->insert_id();
							}						
						} else {
							$tabChamps[$ligne]['marqueid'] = $existeMarque[marqueid];
						}
						$sql="INSERT INTO article_marque (artid, marqueid) VALUES ('".$tabChamps[$ligne][$champIdentifiant]."', '".$tabChamps[$ligne]['marqueid']."')";
						if($_SESSION[debug]){
							$_SESSION[debug_trace] .= "$sql<br>";
						}else{
							$DB_site->query($sql);
						}
					}
				}
			}
		
			//Fournisseur
			if (isset($tabChamps[$ligne]['fournisseur'])) {
				$tabChamps[$ligne]['fournisseurid'] = 0;
				if ($tabChamps[$ligne]['fournisseur']) {
					$existeFournisseur = $DB_site->query_first("SELECT fournisseurid FROM fournisseur WHERE libelle = '".securiserSql($tabChamps[$ligne]['fournisseur'])."'");
					if ($existeFournisseur[fournisseurid] == "") {
						$sql="INSERT INTO fournisseur (libelle) VALUES ('".securiserSql($tabChamps[$ligne]['fournisseur'])."')";
						if($_SESSION[debug]){
							$_SESSION[debug_trace] .= "$sql<br>";
							$tabChamps[$ligne]['fournisseurid'] = "FOURNISSEURID_DEBUG";
						}else{
							$DB_site->query($sql);
							$tabChamps[$ligne]['fournisseurid'] = $DB_site->insert_id();
						}					
					}else{
						$tabChamps[$ligne]['fournisseurid'] = $existeFournisseur[fournisseurid];
					}
				}
				$sqlArticle .= " fournisseurid = '".$tabChamps[$ligne]['fournisseurid']."',";
				$compteurmodifs++;	
			}elseif($defaut_article_fournisseur != ''){
				$tabChamps[$ligne]['fournisseurid'] = 0;
				if ($defaut_article_fournisseur){
					$existeFournisseur = $DB_site->query_first("SELECT fournisseurid FROM fournisseur WHERE libelle = '".securiserSql($defaut_article_fournisseur)."'");
					if ($existeFournisseur[fournisseurid] == "") {
						$sql="INSERT INTO fournisseur (libelle) VALUES ('".securiserSql($defaut_article_fournisseur)."')";
						if($_SESSION[debug]){
							$_SESSION[debug_trace] .= "$sql<br>";
							$tabChamps[$ligne]['fournisseurid'] = "FOURNISSEURID_DEBUG";
						}else{
							$DB_site->query($sql);
							$tabChamps[$ligne]['fournisseurid'] = $DB_site->insert_id();
						}					
					}else{
						$tabChamps[$ligne]['fournisseurid'] = $existeFournisseur[fournisseurid];
					}
				}
				$sqlArticle .= " fournisseurid = '".$tabChamps[$ligne]['fournisseurid']."',";
				$valeurdefaut++;
			}
		
			//Tags
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['tags'.$siteid])){
					$sql="DELETE FROM article_tag WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."'";
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= "$sql<br>";
					}else{
						$DB_site->query($sql);
					}
					
					if ($tabChamps[$ligne]['tags']) {
						$tabTags = explode("|", $tabChamps[$ligne]['tags']);
						foreach ($tabTags as $libTag) {
							$existeTag = $DB_site->query_first("SELECT tagid FROM tags 
																WHERE tag = '".securiserSql($libTag)."'
																AND siteid='$siteid'");
							if ($existeTag[tagid] == "") {
								$sql="INSERT INTO tag (tag,siteid) VALUES ('".securiserSql($libTag)."','$siteid')";
								if($_SESSION[debug]){
									$_SESSION[debug_trace] .= "$sql<br>";
									$tabChamps[$ligne]['tagid'] = "TAGID_DEBUG";
								}else{
									$DB_site->query($sql);
									$tabChamps[$ligne]['tagid'] = $DB_site->insert_id();
								}							
							} else {
								$tabChamps[$ligne]['tagid'] = $existeMarque[tagid];
							}
							$sql="INSERT INTO article_tag (artid, tagid) VALUES ('".$tabChamps[$ligne][$champIdentifiant]."', '".$tabChamps[$ligne]['tagid']."')";
							if($_SESSION[debug]){
								$_SESSION[debug_trace] .= "$sql<br>";
							}else{
								$DB_site->query($sql);
							}
						}
					}
				}elseif($defaut_article_tags != ''){
					$sql="DELETE FROM article_tag WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."'";
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= "$sql<br>";
					}else{
						$DB_site->query($sql);
					}
					if ($defaut_article_tags) {
						$tabTags = explode("|", $defaut_article_tags);
						foreach ($tabTags as $libTag) {
							$existeTag = $DB_site->query_first("SELECT tagid FROM tags 
																WHERE tag = '".securiserSql($libTag)."'
																AND siteid='$siteid'");
							if ($existeMarque[marqueid] == "") {
								$sql="INSERT INTO tag (tag,siteid) VALUES ('".securiserSql($libTag)."','$siteid')";
								if($_SESSION[debug]){
									$_SESSION[debug_trace] .= "$sql<br>";
									$tabChamps[$ligne]['tagid'] = "TAGID_DEBUG";
								}else{
									$DB_site->query($sql);
									$tabChamps[$ligne]['tagid'] = $DB_site->insert_id();
								}							
							} else {
								$tabChamps[$ligne]['tagid'] = $existeMarque[tagid];
							}
							$sql="INSERT INTO article_tag (artid, tagid) VALUES ('".$tabChamps[$ligne][$champIdentifiant]."', '".$tabChamps[$ligne]['tagid']."')";
							if($_SESSION[debug]){
								$_SESSION[debug_trace] .= "$sql<br>";
							}else{
								$DB_site->query($sql);
							}
						}
					}
				}
			}
				
			//Poids
			if (isset($tabChamps[$ligne]['poids'])){
				$sqlArticle .= " poids = '".securiserSql($tabChamps[$ligne]['poids'],"int")."',";
				$compteurmodifs++;
			}elseif($defaut_article_poids != ''){
				$sqlArticle .= " poids = '".securiserSql($defaut_article_poids,"int")."',";
				$valeurdefaut++;
			}
		
			//Dimensions	
			if (isset($tabChamps[$ligne]['longueur'])) {
				$sqlArticle .= " longueur = '".$tabChamps[$ligne]['longueur']."',";
				$compteurmodifs++;
			}elseif($defaut_article_dim_longueur != ''){
				$sqlArticle .= " longueur = '".securiserSql($defaut_article_dim_longueur,"int")."',";
				$valeurdefaut++;
			}
			if (isset($tabChamps[$ligne]['largeur'])){			
				$sqlArticle .= " largeur = '".$tabChamps[$ligne]['largeur']."',";
				$compteurmodifs++;
			}elseif($defaut_article_dim_largeur != ''){
				$sqlArticle .= " largeur = '".securiserSql($defaut_article_dim_largeur,"int")."',";
				$valeurdefaut++;
			}		
			if (isset($tabChamps[$ligne]['hauteur'])) {
				$sqlArticle .= " hauteur = '".$tabChamps[$ligne]['hauteur']."',";
				$compteurmodifs++;
			}elseif($defaut_article_dim_hauteur != ''){
				$sqlArticle .= " hauteur = '".securiserSql($defaut_article_dim_hauteur,"int")."',";
				$valeurdefaut++;
			}
				
			//Prix
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['prix'.$siteid])){
					${"sqlArticleSite".$siteid} .= " prix = '".nettoyerChampNumerique($tabChamps[$ligne]['prix'.$siteid])."',";
					$compteurmodifs++;
				}elseif(${"defaut_article_prix".$siteid} != ''){
					${"sqlArticleSite".$siteid} .= " prix = '".nettoyerChampNumerique(${"defaut_article_prix".$siteid})."',";
					$valeurdefaut++;
				}
			}
			
			//Prix public
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['prixpublic'.$siteid])){
					${"sqlArticleSite".$siteid} .= " prixpublic = '".nettoyerChampNumerique($tabChamps[$ligne]['prixpublic'.$siteid])."',";
					$compteurmodifs++;
				}elseif(${"defaut_article_prixpublic".$siteid} != ''){
					${"sqlArticleSite".$siteid} .= " prixpublic = '".nettoyerChampNumerique(${"defaut_article_prixpublic".$siteid})."',";
					$valeurdefaut++;
				}
			}
			
			//Prix d'achat
			if (isset($tabChamps[$ligne]['prixachat'])) {
				if (isset($tabChamps[$ligne]['stock_total'])) {
					if (!strstr($tabChamps[$ligne]['stock_total'], "|")) {
						$old = $DB_site->query_first("SELECT nombre, prixachatmoyen FROM stock s, article a WHERE s.artid = '".$tabChamps[$ligne]['artid']."' AND s.artid=a.artid");
						$oldmoyen = $old[nombre]*$old[prixachatmoyen];
						$newmoyen = (intval($tabChamps[$ligne]['stock_total'])-$old[nombre])*nettoyerChampNumerique($tabChamps[$ligne]['prixachat']);
						$qte = intval($tabChamps[$ligne]['stock_total']);
						if ($qte){
							$prixachatmoyen =($oldmoyen+$newmoyen)/$qte;
							$prixachatmoyen = number_format($prixachatmoyen,2);
						}
					}
				}
				$sqlArticle .= " prixachat = '".nettoyerChampNumerique($tabChamps[$ligne]['prixachat'])."',";
				$compteurmodifs++;
			}
			
			//Taux de tva
			if (isset($tabChamps[$ligne]['tauxchoisi'])) {
				switch ($tabChamps[$ligne]['tauxchoisi']) {
					case "Aucun":
						$tabChamps[$ligne]['tauxchoisi'] = 0;
						break;
					case "Taux reduit":
						$tabChamps[$ligne]['tauxchoisi'] = 2;
						break;
					case "Taux intermédiaire":
						$tabChamps[$ligne]['tauxchoisi'] = 3;
						break;
					default:
						$tabChamps[$ligne]['tauxchoisi'] = 1;
						break;
				}
				$sqlArticle .= " tauxchoisi = '".nettoyerChampNumerique($tabChamps[$ligne]['tauxchoisi'])."',";
				$compteurmodifs++;
			}elseif($defaut_article_tauxchoisi != ''){
				switch ($defaut_article_tauxchoisi) {
					case "Aucun":
						$defaut_article_tauxchoisival = 0;
						break;
					case "Taux reduit":
						$defaut_article_tauxchoisival = 2;
						break;
					case "Taux intermédiaire":
						$tabChamps[$ligne]['tauxchoisi'] = 3;
						break;
					default:
						$defaut_article_tauxchoisival = 1;
						break;
				}
				$sqlArticle .= " tauxchoisi = '".nettoyerChampNumerique($defaut_article_tauxchoisival)."',";
				$valeurdefaut++;
			}
		
			//Délai de livraison général
			foreach ($tab_siteid as $siteid){		
				if (isset($tabChamps[$ligne]['delai'.$siteid])){
					${"sqlArticleSite".$siteid} .= " delai = '".nettoyerChampNumerique($tabChamps[$ligne]['delai'.$siteid])."',";
					$compteurmodifs++;
				}elseif(${"defaut_article_delai".$siteid} != ''){
					${"sqlArticleSite".$siteid} .= " delai = '".nettoyerChampNumerique(${"defaut_article_delai".$siteid})."',";
					$valeurdefaut++;
				}
			}
		
			//Commentaire général
			if (isset($tabChamps[$ligne]['commentaire'])) {
				$sqlArticle .= " commentaire = '".securiserSql($tabChamps[$ligne]['commentaire'])."',";
				$compteurmodifs++;
			}elseif($defaut_article_commentaire != ''){
				$sqlArticle .= " commentaire = '".securiserSql($defaut_article_commentaire)."',";
				$valeurdefaut++;
			}
		
			//Vendu au metre
			if (isset($tabChamps[$ligne]['prixaumetre'])){
				if ($tabChamps[$ligne]['prixaumetre'] == "non"){
					$tabChamps[$ligne]['prixaumetre'] = 0;
				}else{
					$tabChamps[$ligne]['prixaumetre'] = 1;
				}
				$sqlArticle .= " prixaumetre = '".$tabChamps[$ligne]['prixaumetre']."',";
				$compteurmodifs++;
			}elseif($defaut_article_aumetre != ''){
				if ($defaut_article_aumetre == "non") {
					$defaut_article_aumetreval = 0;
				} else {
					$defaut_article_aumetreval = 1;
				}
				$sqlArticle .= " prixaumetre = '".$defaut_article_aumetreval."',";
				$valeurdefaut++;
			}
			
			//Commandable
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['commandable'.$siteid])) {
					if ($tabChamps[$ligne]['commandable'.$siteid] == "non" || $tabChamps[$ligne]['commandable'.$siteid] == "Non"){
						$tabChamps[$ligne]['commandable'.$siteid] = 0;
					} else {
						$tabChamps[$ligne]['commandable'.$siteid] = 1;
					}
					${"sqlArticleSite".$siteid} .= " commandable = '".$tabChamps[$ligne]['commandable'.$siteid]."',";
					$compteurmodifs++;
				}elseif(${"defaut_article_commandable".$siteid} != ''){
					if (${"defaut_article_commandable".$siteid} == "non" || ${"defaut_article_commandable".$siteid} == "Non"){
						$defaut_article_commandableval = 0;
					}else{
						$defaut_article_commandableval = 1;
					}
					${"sqlArticleSite".$siteid} .= " commandable = '".$defaut_article_commandableval."',";				
					$valeurdefaut++;
				}
			}
			
			// Active V1
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['activeV1'.$siteid])) {
					if ($tabChamps[$ligne]['activeV1'.$siteid] == "non" || $tabChamps[$ligne]['activeV1'.$siteid] == "Non") {
						$tabChamps[$ligne]['activeV1'.$siteid] = 0;
					} else {
						$tabChamps[$ligne]['activeV1'.$siteid] = 1;
					}
					${"sqlArticleSite".$siteid} .= " activeV1 = '".$tabChamps[$ligne]['activeV1'.$siteid]."',";
					$compteurmodifs++;
				}elseif(${"defaut_article_activeV1".$siteid} != ''){
					if (${"defaut_article_activeV1".$siteid} == "non" || ${"defaut_article_activeV1".$siteid} == "Non"){
						$defaut_article_activeV1val = 0;
					}else{
						$defaut_article_activeV1val = 1;
					}
					${"sqlArticleSite".$siteid} .= " activeV1 = '".$defaut_article_activeV1val."',";
					$valeurdefaut++;
				}
			}
			
			// Active V2
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['activeV2'.$siteid])) {
					if ($tabChamps[$ligne]['activeV2'.$siteid] == "non" || $tabChamps[$ligne]['activeV2'.$siteid] == "Non"){
						$tabChamps[$ligne]['activeV2'.$siteid] = 0;
					} else {
						$tabChamps[$ligne]['activeV2'.$siteid] = 1;
					}
					${"sqlArticleSite".$siteid} .= " activeV2 = '".$tabChamps[$ligne]['activeV2'.$siteid]."',";
					$compteurmodifs++;
				}elseif(${"defaut_article_activeV2".$siteid} != ''){
					if (${"defaut_article_activeV2".$siteid} == "non" || ${"defaut_article_activeV2".$siteid} == "Non"){
						$defaut_article_activeV2val = 0;
					}else{
						$defaut_article_activeV2val = 1;
					}
					${"sqlArticleSite".$siteid} .= " activeV2 = '".$defaut_article_activeV2val."',";
					$valeurdefaut++;
				}
			}
				
			//Image 1
			if ($tabChamps[$ligne]['url_img0'] != ""){
				$tabImage = explode(".", $tabChamps[$ligne]['url_img0']);
				$ext = count($tabImage)-1;
				$extension = strtolower($tabImage[$ext]);
				$nom_fichier = $rootpath."configurations/$host/images/produits/".$tabChamps[$ligne]['artid'].".".$extension;
				if (file_exists($nom_fichier)){
					@unlink($nom_fichier);
				}
				copier_image_url($nom_fichier, $tabChamps[$ligne]['url_img0'], $extension);
				if (file_exists($nom_fichier)){
					redimentionner_image_complet($nom_fichier, $tabChamps[$ligne]['artid'].".".$extension);
					$sqlArticle .= " image = '".$extension."',";
					$compteurmodifs++;
				}
			}
				
			//Légendes 1
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['legende_img0'.$siteid])){
					${"sqlArticleSite".$siteid} .= " legende = '".securiserSql($tabChamps[$ligne]['legende_img0'.$siteid])."',";
					$compteurmodifs++;
				}
			}
				
			//Image 2
			if ($tabChamps[$ligne]['url_img1'] != "") {
				$tabImage = explode(".", $tabChamps[$ligne]['url_img1']);
				$ext = count($tabImage)-1;
				$extension = strtolower($tabImage[$ext]);
				$articlephotoid = $DB_site->query_first("SELECT articlephotoid, image FROM articlephoto WHERE artid = '".$tabChamps[$ligne]['artid']."' AND position='1'");
				if ($articlephotoid[articlephotoid]){
					$sql="DELETE FROM articlephoto WHERE articlephotoid = '".$articlephotoid[articlephotoid]."'";
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= "$sql<br>";
					}else{
						$DB_site->query($sql);
						@unlink($rootpath."configurations/$host/images/produits/".$tabChamps[$ligne]['artid']."_".$articlephotoid[articlephotoid].".".$articlephotoid[image]);
					}				
				}
				$DB_site->query();
				$sql="INSERT INTO articlephoto (artid, image, position) VALUES ('".$tabChamps[$ligne]['artid']."', '$extension', '1')";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
					$articlephotoid = "ARTICLEPHOTOID_DEBUG";
				}else{
					$DB_site->query($sql);
					$articlephotoid = $DB_site->insert_id();
					$nom_fichier = $rootpath."configurations/$host/images/produits/".$tabChamps[$ligne]['artid']."_".$articlephotoid.".".$extension;
					if (file_exists($nom_fichier)) {
						@unlink($nom_fichier);
					}
					copier_image_url($nom_fichier, $tabChamps[$ligne]['url_img1'], $extension);
					if (file_exists($nom_fichier)){
						redimentionner_image_complet($nom_fichier, $tabChamps[$ligne]['artid']."_".$articlephotoid.".".$extension);
					}
				}			
				
			}
				
			//Légendes 2
			$articlephotoid = $DB_site->query_first("SELECT articlephotoid FROM articlephoto WHERE artid = '".$tabChamps[$ligne]['artid']."' AND position='1'");
			foreach ($tab_siteid as $siteid){			
				if (isset($tabChamps[$ligne]['legende_img1'.$siteid]) && isset($articlephotoid[articlephotoid])){
					$articlephoto_site = $DB_site->query_first("SELECT * FROM articlephoto_site WHERE articlephotoid = '".$articlephotoid[articlephotoid]."' AND siteid='$siteid'");
					
					if($articlephoto_site[articlephotoid] != ""){
						$DB_site->query();
						$sql="UPDATE articlephoto_site 
								SET legende = '".securiserSql($tabChamps[$ligne]['legende_img1'.$siteid])."' 
								WHERE articlephotoid = '".$articlephotoid[articlephotoid]."'
								AND siteid='$siteid'";
						if($_SESSION[debug]){
							$_SESSION[debug_trace] .= "$sql<br>";
						}else{
							$DB_site->query($sql);
						}
					}else{
						$DB_site->query();
						$sql="INSERT INTO articlephoto_site (articlephotoid,siteid,legende) 
								VALUES('$articlephotoid[articlephotoid]','$siteid',
								'".securiserSql($tabChamps[$ligne]['legende_img1'.$siteid])."')";
						if($_SESSION[debug]){
							$_SESSION[debug_trace] .= "$sql<br>";
						}else{
							$DB_site->query($sql);
						}
					}			
				}
			}		
		
			//Descriptions
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['description'.$siteid])) {
					${"sqlArticleSite".$siteid} .= " description = '".str_replace('@@','<br>',securiserSql($tabChamps[$ligne]['description'.$siteid],"html"))."',";
					$compteurmodifs++;
				}elseif(${'defaut_article_description'.$siteid} != ''){
					${"sqlArticleSite".$siteid} .= " description = '".str_replace('@@','<br>',securiserSql(${'defaut_article_description'.$siteid},"html"))."',";
					$valeurdefaut++;
				}
			}
		
			//Fiches techniques
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['fichetechnique'.$siteid])) {
					${"sqlArticleSite".$siteid} .= " fichetechnique = '".str_replace('@@','<br>',securiserSql($tabChamps[$ligne]['fichetechnique'.$siteid],"html"))."',";
					$compteurmodifs++;
				}elseif(${'defaut_article_fichetechnique'.$siteid} != ''){
					${"sqlArticleSite".$siteid} .= " fichetechnique = '".str_replace('@@','<br>',securiserSql(${'defaut_article_fichetechnique'.$siteid},"html"))."',";
					$valeurdefaut++;
				}
			}
		
			//Notre avis
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['notreavis'.$siteid])) {
					${"sqlArticleSite".$siteid} .= " notreavis = '".str_replace('@@','<br>',securiserSql($tabChamps[$ligne]['notreavis'.$siteid],"html"))."',";
					$compteurmodifs++;
				}elseif(${'defaut_article_notreavis'.$siteid} != ''){
					${"sqlArticleSite".$siteid} .= " notreavis = '".str_replace('@@','<br>',securiserSql(${'defaut_article_notreavis'.$siteid},"html"))."',";
					$valeurdefaut++;
				}
			}
		
			//Colisage
			if (isset($tabChamps[$ligne]['colisage'])) {
				$sqlArticle .= " colisagefournisseur = '".securiserSql($tabChamps[$ligne]['colisage'])."',";
				$compteurmodifs++;
			}elseif($defaut_article_commentaire != ''){
				$sqlArticle .= " colisagefournisseur = '".securiserSql($defaut_article_colisage)."',";
				$valeurdefaut++;
			}
		
			//Balises meta title
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['ref_title'.$siteid])) {
					${"sqlArticleSite".$siteid} .= " ref_title = '".securiserSql($tabChamps[$ligne]['ref_title'.$siteid])."',";
					$compteurmodifs++;
				}elseif(${'defaut_article_ref_title'.$siteid} != ''){
					${"sqlArticleSite".$siteid} .= " ref_title = '".securiserSql(${'defaut_article_ref_title'.$siteid})."',";
					$valeurdefaut++;
				}
			}
		
			//Balises meta desc
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['ref_description'.$siteid])) {
					${"sqlArticleSite".$siteid} .= " ref_description = '".securiserSql($tabChamps[$ligne]['ref_description'.$siteid])."',";
					$compteurmodifs++;
				}elseif(${'defaut_article_ref_description'.$siteid} != ''){
					${"sqlArticleSite".$siteid} .= " ref_description = '".securiserSql(${'defaut_article_ref_description'.$siteid})."',";
					$valeurdefaut++;
				}
			}
		
			//Balises meta keywords
			foreach ($tab_siteid as $siteid){
				if (isset($tabChamps[$ligne]['ref_keywords'.$siteid])) {
					${"sqlArticleSite".$siteid} .= " ref_keywords = '".securiserSql($tabChamps[$ligne]['ref_keywords'.$siteid])."',";
					$compteurmodifs++;
				}elseif(${'defaut_article_ref_keywords'.$siteid} != ''){
					${"sqlArticleSite".$siteid} .= " ref_keywords = '".securiserSql(${'defaut_article_ref_keywords'.$siteid})."',";
					$valeurdefaut++;
				}
			}
		
			//Articles conseillés
			$iart=0;
			while (isset($tabChamps[$ligne]['artconseil'.$iart])){
				$existeArticle = $DB_site->query_first("SELECT artid FROM article WHERE artcode = '".$tabChamps[$ligne]['artconseil'.$iart]."'");
				if ($existeArticle[artid] != "") {
					$existeArticle_conseil = $DB_site->query_first("SELECT id FROM article_conseil WHERE artid='".$tabChamps[$ligne]['artid']."' AND artid_conseille='".$existeArticle[artid]."'");
					if ($existeArticle_conseil[id] == "") {
						$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM article_conseil WHERE artid='".$tabChamps[$ligne]['artid']."'");
						$position = $lastPosition[0] + 1;
						$sql="INSERT INTO article_conseil (artid, artid_conseille, position) 
								VALUES ('".$tabChamps[$ligne]['artid']."', '".$existeArticle[artid]."', '$position')";
						if($_SESSION[debug]){
							$_SESSION[debug_trace] .= "$sql<br>";
						}else{
							$DB_site->query($sql);
						}
					}
				}
				$iart++;
			}
			
			// Prix achat moyen
			if ($prixachatmoyen){
				$DB_site->query();
				$sql="UPDATE article SET prixachatmoyen = '".securiserSql($prixachatmoyen)."' WHERE artid = '".$tabChamps[$ligne]['artid']."'";
				if($_SESSION[debug]){
					$_SESSION[debug_trace] .= "$sql<br>";
				}else{
					$DB_site->query($sql);
				}
			}
			
			if ($compteurmodifs > 1 || $valeurdefaut >= 1) {
				####################################################
				### MAJ des tables article & article_description ###
				####################################################
				if ($sqlArticle != "UPDATE article SET"){
					$sqlArticle = substr($sqlArticle, 0, -1);
					$sqlArticle .= " WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."'";				
					if($_SESSION[debug]){
						$_SESSION[debug_trace] .= $sqlArticle.'<br>';
					}else{
						$DB_site->query($sqlArticle);
					}				
				}
				
				foreach ($tab_siteid as $siteid){
					if (${"sqlArticleSite".$siteid} != "UPDATE article_site SET"){
						
						${"sqlArticleSite".$siteid} = substr(${"sqlArticleSite".$siteid}, 0, -1);
						${"sqlArticleSite".$siteid} .= " WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."' AND siteid='$siteid'";
						
						if($_SESSION[debug]){
							$_SESSION[debug_trace] .= ${"sqlArticleSite".$siteid}.'<br>';
						}else{
							$DB_site->query(${"sqlArticleSite".$siteid});
						}					
					}
				}
						
				#####################################################################
				### MAJ des stocks (les articles doivent d'abord être mis à jour) ###
				#####################################################################
				//Caractéristiques
				if (isset($tabChamps[$ligne]['caracteristiques']) || $defaut_article_caracteristiques != '') {
					if (isset($tabChamps[$ligne]['caracteristiques'])){
						$tabCaract = explode("|", $tabChamps[$ligne]['caracteristiques']);
					}else{
						$tabCaract = explode("|", $defaut_article_caracteristiques);
					}
					$DB_site->query("DELETE FROM article_caractval WHERE $champIdentifiant = '".$tabChamps[$ligne][$champIdentifiant]."'");
					for ($i = 0; $i < count($tabCaract); $i++) {
						$tabCaractVal = explode(":", $tabCaract[$i]);
						// Libellé caractéristique
						if ($tabCaractVal[0]){
							$caractExiste = $DB_site->query_first("SELECT caractid FROM caracteristique_site 
																	WHERE libelle = '".securiserSql($tabCaractVal[0])."'
																	AND siteid='1'");
							if ($caractExiste[caractid] == ""){
								$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM caracteristique");
								$position = $lastPosition[0] + 1;
								$DB_site->query("INSERT INTO caracteristique (position) VALUES ('".$position."')");
								$caractid = $DB_site->insert_id();
								$DB_site->query("INSERT INTO caracteristique_site (libelle, caractid, siteid) 
												VALUES ('".securiserSql($tabCaractVal[0])."','".$caractid."','1')");
							}else{
								$caractid = $caractExiste[caractid];
							}
							
							if ($tabCaractVal[1]){
								$vals = explode(",", $tabCaractVal[1]);
								for ($j = 0; $j < count($vals); $j++){
									$caractValExiste = $DB_site->query_first("SELECT caractvalid FROM caracteristiquevaleur_site AS cvs 
																				INNER JOIN caracteristiquevaleur AS cv USING(caractvalid)
																				WHERE cvs.libelle = '".securiserSql($vals[$j])."' 
																				AND cv.caractid = '".$caractid."'
																				AND cvs.siteid='1'");
									if ($caractValExiste[caractvalid] == ""){
										$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM caracteristiquevaleur WHERE caractid='".$caractid."'");
										$position = $lastPosition[0]+1;
										$DB_site->query("INSERT INTO caracteristiquevaleur (caractid, position) VALUES ('".$caractid."', '".$position."') ");
										$caractvalid = $DB_site->insert_id();
										$DB_site->query("INSERT INTO caracteristiquevaleur_site (caractvalid, libelle, siteid) 
															VALUES ('".$caractvalid."', '".securiserSql($vals[$j])."', '1') ");
									}else{
										$caractvalid = $caractValExiste[caractvalid];
									}
									$DB_site->query("INSERT INTO article_caractval (artid, caractvalid) VALUES ('".$tabChamps[$ligne][$champIdentifiant]."', '".$caractvalid."') ");
								}
							}
						}
					}
				}
				construireStockArticle($DB_site, $tabChamps[$ligne][$champIdentifiant]);	
	
				clearDir($GLOBALS[rootpath]."configurations/".$GLOBALS[host]."/cache/articles/".$tabChamps[$ligne][$champIdentifiant]);
				
				//Référence par combinaison
				if (isset($tabChamps[$ligne]['stock_artcode'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_artcode'], "reference");
				}elseif($defaut_article_stock_artcode != ''){
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $defaut_article_stock_artcode, "reference");
				}
				//EAN par combinaison
				if (isset($tabChamps[$ligne]['stock_code_EAN'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_code_EAN'], "code_EAN");
				}elseif($defaut_article_stock_code_EAN != ''){
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $defaut_article_stock_code_EAN, "code_EAN");
				}				
				
				//ASIN par combinaison
				if (isset($tabChamps[$ligne]['stock_ASIN'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_ASIN'], "ASIN");
				}
										
				//Référence fabricant par combinaison
				if (isset($tabChamps[$ligne]['stock_reference_fabricant'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_reference_fabricant'], "reference_fabricant");
				}elseif($defaut_article_stock_reference_fabricant != ''){
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $defaut_article_stock_reference_fabricant, "reference_fabricant");
				}
				
				//Stocks par combinaison
				if (isset($tabChamps[$ligne]['stock_total'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_total'], "total");
					recalculerStockArticle($DB_site, $tabChamps[$ligne][$champIdentifiant]);
				}elseif($defaut_article_stock_total != ''){
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $defaut_article_stock_total, "total");
					recalculerStockArticle($DB_site, $tabChamps[$ligne][$champIdentifiant]);
				}
				
				//Seuil d'alerte par combinaison
				if (isset($tabChamps[$ligne]['stock_seuil_alerte'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_seuil_alerte'], "seuil_alerte");
				}elseif($defaut_article_stock_seuil_alerte != ''){
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $defaut_article_stock_seuil_alerte, "seuil_alerte");
				}
				
				//Délais de réappro par combinaison
				if (isset($tabChamps[$ligne]['stock_delai_appro'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_delai_appro'], "delai_appro");
				}elseif($defaut_article_stock_delai_appro != ''){
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $defaut_article_stock_delai_appro, "delai_appro");
				}
						
						//Délais de livraison par combinaison
				if (isset($tabChamps[$ligne]['stock_delai_livraison'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_delai_livraison'], "delai_livraison");
				}elseif($defaut_article_stock_delai_livraison != ''){
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $defaut_article_stock_delai_livraison, "delai_livraison");
				}
				
				//Zone de stockage par combinaison
				if (isset($tabChamps[$ligne]['stock_zonestockage'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_zonestockage'], "zonestockage");
				}elseif($defaut_article_stock_zonestockage != ''){
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $defaut_article_stock_zonestockage, "zonestockage");
				}
				
				//prix d'achat par combinaison
				if (isset($tabChamps[$ligne]['stock_prixachat'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_prixachat'], "prixachat");
				}elseif($defaut_article_stock_prixachat != ''){
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $defaut_article_stock_prixachat, "prixachat");
				}
				
				//Prix de vente par combinaison
				if (isset($tabChamps[$ligne]['stock_prix'])) {
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $tabChamps[$ligne]['stock_prix'], "differenceprix");
				}elseif($defaut_article_stock_prix != ''){
					importerInformationsCombinaisons($DB_site, $tabChamps[$ligne][$champIdentifiant], $defaut_article_stock_prix, "differenceprix");
				}
			}
		} // Fin for ($ligne=$lignedeb;$ligne<=$nblignes;$ligne++){
		
		$message = "$nbInsertions $multilangue[article_s_cree_s]<br>
					$nbModifications $multilangue[article_s_modifie_s]<br><br>";
		
			
		/*echo "
			<center>
				<font class=\"erreur\">$message</font>
				<br><br>
				<a href=\"import_csv.php\" class=\"btn_retour\">$multilangue[retour]</a>
				</center>";*/
		/*$folder = $rootpath."configurations/$host/importcsv";
		$dossier = opendir($folder);*/
		/*while ($fichier = readdir($dossier)){
			if ($fichier != "." && $fichier != ".."){
				if (file_exists($folder."/import".$type.".csv")){
					unlink($folder."/import".$type.".csv");
				}
			}
		}*/
		eval(charge_template($langue,$referencepage,"ImportEtape4Articles"));
	}else{
		header('location: import_export_csv.php?erreurdroits=1');	
	}
} // Fin if ($action == 'importbddarticle'){
/** ********************************************************************************
 ********************************* Import des données ******************************
 ************************************* FIN ETAPE 4 ****************************** **/




/** ************************************ ETAPE 3 ***********************************
 *************************** Prévisualisation de l'import **************************
 ******************************************************************************** **/
// Prévisualisation import catégorie
if($action == "importpromotion"){

	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while ($site = $DB_site->fetch_array($sites)){
		$tab_siteid[$site[siteid]]="(<i>$site[libelle]</i>)";
	}

	if (!empty($_FILES['fichier_import']['name'])){
		$erreur = "";
		//indiquer dans ce tableau les type de fichier autorisé pour l'upload.
		$listeTypesAutorise = array("text/csv","text/tsv","text/comma-separated-values","application/csv","application/excel","application/vnd.ms-excel","application/vnd.msexcel","text/anytext");
		erreurUpload("fichier_import",$listeTypesAutorise,100048576);

		$tableau_apercu = "";

		if ($erreur == ""){
			$nom_fic = $rootpath."configurations/$host/importcsv/import".$type.".csv" ;
			copier_image($nom_fic,"fichier_import") ;
			$rh = fopen($nom_fic, 'rb');
			if ($rh) {
				$row = 1 ;
				while ($data = fgetcsv ($rh, 10000, ";")) 	{
					$num = count ($data);
					for ($c = 0 ; $c < $num ; $c++) {
						$tableau[$row][$c] = $data[$c] ;
					}
					$row++;
				}
			}
			fclose($rh);
			$folder = $rootpath."configurations/$host/importcsv";
			$dossier = opendir($folder);
			while ($fichier = readdir($dossier)){
				if ($fichier != "." && $fichier != ".."){
					if (file_exists($folder."/import".$type.".csv"))	{
						unlink($folder."/import".$type.".csv");
					}
				}
			}
				
			if ((isset($tableau)) and (count($tableau) > 0)){

				$contenu= "<br><center>$multilangue[verifier_donnees_tableau] : <br><br></center>";
				$contenu.= "<div class=\"import_ascenseur\">";

				$tableau_apercu="<table class=\"table table-striped table-bordered dataTable table-hover table-apercu\" cellspacing=\"0\" border=\"1\">";
				foreach ($tableau as $ligne => $valeur){
					if ($exclureEntete == 1 && $ligne == 1){
						$compteur = 0;
						$tableau_apercu .= "<thead><tr>";
							
						// Identifiant article
						if ($promotion_artid == 'on') {
							$tableau_apercu .= "<th>$multilangue[site_concerne]</th>";
							$compteur++;
						}
						
						// Site concerné
						if ($promotion_site == 'on') {
							$tableau_apercu .= "<th>$multilangue[site_concerne]</th>";
							$compteur++;
						}
						
						// Référence article
						if ($promotion_artcode == 'on') {
							$tableau_apercu .= "<th>$multilangue[reference_article]</th>";
							$compteur++;
						}
						
						// Prix TTC article
						if ($promotion_prix == 'on') {
							$tableau_apercu .= "<th>$multilangue[prix] $multilangue[ttc]</th>";
							$compteur++;
						}
						
						// Prix Promo TTC article
						if ($promotion_prixpromo == 'on') {
							$tableau_apercu .= "<th>$multilangue[prix_promotion] $multilangue[ttc]</th>";
							$compteur++;
						}
							
						// Date début promotion
						if ($promotion_datedebut == 'on') {
							$tableau_apercu .= "<th>$multilangue[date_debut] $multilangue[promotion]</th>";
							$compteur++;
						}
							
						// Date fin promotion
						if ($promotion_datefin == 'on') {
							$tableau_apercu .= "<th>$multilangue[date_fin] $multilangue[promotion]</th>";
							$compteur++;
						}			
						$tableau_apercu .= "</tr></thead><tbody>";
						
					}elseif (!$exclureEntete || ($exclureEntete == 1 && $ligne != 1)){
						$compteur = 0;
						$tabChamps[$ligne] = array();
						$tableau_apercu .= "<tr id=\"ligne_".$ligne."\">";
							
						// Identifiant article
						if ($promotion_artid == 'on'){
							$tabChamps[$ligne]['artid'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['artid']."</td>";
							$compteur++;
						}
							
						// Site concerné
						if ($promotion_site == 'on'){
							$tabChamps[$ligne]['libsite'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['libsite']."</td>";
							$compteur++;
						}
							
						// Référence article
						if ($promotion_artcode == 'on'){
							$tabChamps[$ligne]['artcode'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['artcode']."</td>";
							$compteur++;
						}
							
						// Prix TTC article
						if ($promotion_prix == 'on'){
							$tabChamps[$ligne]['prix'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['prix']."</td>";
							$compteur++;
						}
							
						// Prix Promo TTC article
						if ($promotion_prixpromo == 'on'){
							$tabChamps[$ligne]['prixpromo'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['prixpromo']."</td>";
							$compteur++;
						}
							
						// Date début promotion
						if ($promotion_datedebut == 'on'){
							$tabChamps[$ligne]['datedebut'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['datedebut']."</td>";
							$compteur++;
						}
							
						// Date fin promotion
						if ($promotion_datefin == 'on'){
							$tabChamps[$ligne]['datefin'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['datefin']."</td>";
							$compteur++;
						}												
			
						$tableau_apercu .= "</tr>";
					}
				}
				$tableau_apercu .= "</tbody></table>";

				$contenu.=$tableau_apercu."</div><br>";

				$apercu = $contenu;

				$_SESSION[tabChampsPromotions]=$tabChamps;

			}
		}else{
			$message = $erreur;
		}

		$libNavigSupp="<b>Importer des ".$type."s au format CSV - Etape 3</b>";
		eval(charge_template($langue,$referencepage,"NavigSupp"));

		eval(charge_template($langue,$referencepage,"ImportEtape3Promotions"));
	}
}







// Prévisualisation import catégorie
if($action == "importcategorie"){
	
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while ($site = $DB_site->fetch_array($sites)){
		$tab_siteid[$site[siteid]]="(<i>$site[libelle]</i>)";
	}
	
	if (!empty($_FILES['fichier_import']['name'])){
		$erreur = "";
		//indiquer dans ce tableau les type de fichier autorisé pour l'upload.
		$listeTypesAutorise = array("text/csv","text/tsv","text/comma-separated-values","application/csv","application/excel","application/vnd.ms-excel","application/vnd.msexcel","text/anytext");
		erreurUpload("fichier_import",$listeTypesAutorise,100048576);
	
		$tableau_apercu = "";
		
		if ($erreur == ""){
			$nom_fic = $rootpath."configurations/$host/importcsv/import".$type.".csv" ;
			copier_image($nom_fic,"fichier_import") ;
			$rh = fopen($nom_fic, 'rb');
			if ($rh) {
				$row = 1 ;
				while ($data = fgetcsv ($rh, 10000, ";")) 	{
					$num = count ($data);
					for ($c = 0 ; $c < $num ; $c++) {
						$tableau[$row][$c] = $data[$c] ;
					}
					$row++;
				}
			}
			fclose($rh);
			$folder = $rootpath."configurations/$host/importcsv";
			$dossier = opendir($folder);
			while ($fichier = readdir($dossier)){
				if ($fichier != "." && $fichier != ".."){
					if (file_exists($folder."/import".$type.".csv"))	{
						unlink($folder."/import".$type.".csv");
					}
				}
			}
			
			if ((isset($tableau)) and (count($tableau) > 0)){
				
				$contenu= "<br><center>$multilangue[verifier_donnees_tableau] : <br><br></center>";
				$contenu.= "<div class=\"import_ascenseur\">";
				
				$tableau_apercu="<table class=\"table table-striped table-bordered dataTable table-hover table-apercu\" cellspacing=\"0\" border=\"1\">";
				foreach ($tableau as $ligne => $valeur){
					if ($exclureEntete == 1 && $ligne == 1){
						$compteur = 0;
						$tableau_apercu .= "<thead><tr>";
							
						//Identifiant Catégorie
						if ($categorie_catid == 'on') {
							$tableau_apercu .= "<th>$multilangue[identifiant_categorie]</th>";
							$compteur++;
						}
							
						//Libellés
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'categorie_libelle'.$siteid} == 'on') {
								$tableau_apercu .= "<th>$multilangue[libelle]<br>($libsite)</th>";
								$compteur++;
							}
						}
							
						//Description
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'categorie_description'.$siteid} == 'on') {
								$tableau_apercu .= "<th>$multilangue[description]<br>($libsite)</th>";
								$compteur++;
							}
						}
							
						//Balises title
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'categorie_ref_title'.$siteid} == 'on') {
								$tableau_apercu .= "<th>$multilangue[balises_meta] title<br>($libsite)</th>";
								$compteur++;
							}
						}
							
						//Balises meta desc
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'categorie_ref_description'.$siteid} == 'on') {
								$tableau_apercu .= "<th>$multilangue[balises_meta] description<br>($libsite)</th>";
								$compteur++;
							}
						}
							
						//Balises meta key
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'categorie_ref_keywords'.$siteid} == 'on') {
								$tableau_apercu .= "<th>$multilangue[balises_meta] keywords<br>($libsite)</th>";
								$compteur++;
							}
						}
							
						//module couleur de rayon
						if ($categorie_color == 'on') {
							$tableau_apercu .= "<th>$multilangue[couleur]</th>";
							$compteur++;
						}
							
						//module couleur de fond du rayon
						if ($categorie_color_back == 'on') {
							$tableau_apercu .= "<th>$multilangue[couleur_fond]</th>";
							$compteur++;
						}
							
						//module couleur de survol du rayon
						if ($categorie_color_survol == 'on') {
							$tableau_apercu .= "<th>$multilangue[couleur_survol]</th>";
							$compteur++;
						}
						$tableau_apercu .= "</tr></thead><tbody>";
					}elseif (!$exclureEntete || ($exclureEntete == 1 && $ligne != 1)){
						$compteur = 0;
						$tabChamps[$ligne] = array();
						$tableau_apercu .= "<tr id=\"ligne_".$ligne."\">";
							
						//Identifiant Catégorie
						if ($categorie_catid == 'on') {
							$tabChamps[$ligne]['catid'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['catid']."</td>";
							$compteur++;
						}
							
						//Libellés
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'categorie_libelle'.$siteid} == 'on') {
								$tabChamps[$ligne]['libelle'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu .= "<td>".$tabChamps[$ligne]['libelle'.$siteid]."</td>";
								$compteur++;
							}
						}
							
						//Description
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'categorie_description'.$siteid} == 'on') {
								$tabChamps[$ligne]['description'.$siteid] = $tableau[$ligne][$compteur] ;						
								$tableau_apercu .= "<td>".$tableau[$ligne][$compteur]."</td>";
								$compteur++;
							}
						}
							
						//Balises title
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'categorie_ref_title'.$siteid} == 'on') {
								$tabChamps[$ligne]['ref_title'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu .= "<td>".$tabChamps[$ligne]['ref_title'.$siteid]."</td>";
								$compteur++;
							}
						}
							
						//Balises meta desc
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'categorie_ref_description'.$siteid} == 'on') {
								$tabChamps[$ligne]['ref_description'.$siteid] = $tableau[$ligne][$compteur];						
								$tableau_apercu .= "<td>".$tableau[$ligne][$compteur]."</td>";
								$compteur++;
							}
						}
							
						//Balises meta key
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'categorie_ref_keywords'.$siteid} == 'on') {
								$tabChamps[$ligne]['ref_keywords'.$siteid] = $tableau[$ligne][$compteur] ;							
								$tableau_apercu .= "<td>".$tableau[$ligne][$compteur]."</td>";
								$compteur++;
							}
						}
							
						//module couleur de rayon
						if ($categorie_color == 'on') {
							$tabChamps[$ligne]['color'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['color']."</td>";
							$compteur++;
						}
							
						//module couleur de fond du rayon
						if ($categorie_color_back == 'on') {
							$tabChamps[$ligne]['color_back'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['color_back']."</td>";
							$compteur++;
						}
							
						//module couleur de survol du rayon
						if ($categorie_color_survol == 'on') {
							$tabChamps[$ligne]['color_survol'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu .= "<td>".$tabChamps[$ligne]['color_survol']."</td>";
							$compteur++;
						}
						$tableau_apercu .= "</tr>";
					}
				}
				$tableau_apercu .= "</tbody></table>";
				
				$contenu.=$tableau_apercu."</div><br>";		

				$apercu = $contenu;
				
				$_SESSION[tabChampsCategories]=$tabChamps;
				
			}
		}else{
			$message = $erreur;			
		}
		
		$libNavigSupp="<b>Importer des ".$type."s au format CSV - Etape 3</b>";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		
		eval(charge_template($langue,$referencepage,"ImportEtape3Categories"));
	}
}






// Prévisualisation import article
if ($action == 'importarticle'){
	
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while ($site = $DB_site->fetch_array($sites)){
		$tab_siteid[$site[siteid]]="(<i>$site[libelle]</i>)";
	}

	
	if (!empty($_FILES['fichier_import']['name'])){
		$erreur = "";
		//indiquer dans ce tableau les type de fichier autorisés pour l'upload.
		$listeTypesAutorise = array("text/csv","text/tsv","text/comma-separated-values","application/csv","application/excel","application/vnd.ms-excel","application/vnd.msexcel","text/anytext","application/force-download");
		erreurUpload("fichier_import",$listeTypesAutorise,100048576);
	
		
		if ($erreur == ""){
			
			$folder = $rootpath."configurations/$host/importcsv";
			$dossier = opendir($folder);
			while ($fichier = readdir($dossier)){
				if ($fichier != "." && $fichier != ".."){
					if (file_exists($folder."/import".$type.".csv")){
						unlink($folder."/import".$type.".csv");
					}
				}
			}
			$nom_fic = $rootpath."configurations/$host/importcsv/import".$type.".csv" ;
			copier_image($nom_fic,"fichier_import") ;
			$rh = fopen($nom_fic, 'rb');
			if ($rh) {
				$row = 1 ;
				while ($data = fgetcsv ($rh, 10000, ";")) 	{
					$num = count ($data);
					for ($c = 0 ; $c < $num ; $c++) {
						$tableau[$row][$c] = $data[$c] ;
					}
					$row++;
				}
			}
			fclose($rh);
	
			//print_r($_POST);
			
			if ((isset($tableau)) and (count($tableau) > 0)){
				$lastCompteur = 0;
				
				$contenu= "<br><center><b>$multilangue[verifier_donnees_tableau] : </b><br><br></center>";
				$contenu.= "<div class=\"import_ascenseur\">";
				$tableau_apercu="<table class=\"table table-striped table-bordered dataTable table-hover table-apercu\" cellspacing=\"0\" border=\"1\">";
				$champsnnimporte = array();
				foreach ($tableau as $ligne => $valeur){
					if ($exclureEntete == 1 && $ligne == 1){
						$compteur = 0;
						$tableau_apercu.= "<thead><tr>";
							
						//Référence générale
						if ($article_artcode == 'on') {
							$tableau_apercu.= "<th>$multilangue[reference]</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[reference].'|defaut_article_artcode';
						}
							
						//Identifiant article
						if ($article_artid == 'on') {
							$tableau_apercu.= "<th>$multilangue[identifiant_article]</th>";
							$compteur++;
						}
	
						//Identifiant catégorie
						if ($article_catid == 'on') {
							$tableau_apercu.= "<th>$multilangue[identifiant_categorie]</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[identifiant_categorie].'|defaut_article_catid';
						}
							
						//Code EAN général
						if ($article_code_EAN == 'on') {
							$tableau_apercu.= "<th>$multilangue[code_ean]</th>";
							$compteur++;
						}/*else{
						$champsnnimporte[] = $multilangue[code_ean].'|defaut_article_code_EAN';
						}*/
											
						//Code ASIN général
						if ($article_ASIN == 'on') {
							$tableau_apercu.= "<th>$multilangue[code_asin]</th>";
							$compteur++;
						}						
							
						//Référence fabricant générale
						if ($article_reference_fabricant == 'on'){
							$tableau_apercu.= "<th>$multilangue[reference_fabricant]</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[reference_fabricant].'|defaut_article_reference_fabricant';
						}
							
						if(in_array("5864",$modules) || in_array("5957",$modules) || $mode == "test_modules"){
							//Numéro tarifaire la poste
							if ($article_numero_tarifaire_laposte == 'on') {
								$tableau_apercu.= "<th>$multilangue[numero_tarifaire_laposte]</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[numero_tarifaire_laposte].'|defaut_article_numero_tarifaire_laposte';
							}
						}
							
						//Libellés
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_libelle'.$siteid} == 'on') {
								$tableau_apercu.= "<th>$multilangue[libelle]<br>$libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[libelle]."<br>".$libsite."|defaut_article_libelle".$siteid;
							}
						}
							
						//Sous titres
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_titre2'.$siteid} == 'on') {
								$tableau_apercu.= "<th>$multilangue[titre2]<br>$libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[titre2]."<br>".$libsite.'|defaut_article_titre2'.$siteid;
							}
						}
							
						//Google shop
						if ($article_googleshop == 'on') {
							$tableau_apercu.= "<th>$multilangue[categorie] Google Shopping</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[categorie].' Google Shopping|defaut_article_googleshop';
						}
							
						//Marques
						if ($article_marques == 'on') {
							$tableau_apercu.= "<th>$multilangue[marques]</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[marques].'|defaut_article_marques';
						}
							
						//Fournisseur
						if ($article_fournisseur == 'on') {
							$tableau_apercu.= "<th>$multilangue[fournisseur]</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[fournisseur].'|defaut_article_fournisseur';
						}
							
						//Poids général
						if ($article_poids == 'on') {
							$tableau_apercu.= "<th>$multilangue[poids]<br>($multilangue[grammes])</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[poids]." (".$multilangue[grammes].')|defaut_article_poids';
						}

						if(in_array(5950,$modules) || $mode == "test_modules"){
							//Dimensions						
							if ($article_dim_longueur == 'on') {
								$tableau_apercu.= "<th>$multilangue[longueur]<br>($multilangue[millimetres])</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[longueur]." (".$multilangue[millimetres].')|defaut_article_dim_longueur';
							}
							if ($article_dim_largeur == 'on') {
								$tableau_apercu.= "<th>$multilangue[largeur]<br>($multilangue[millimetres])</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[largeur]." (".$multilangue[millimetres].')|defaut_article_dim_largeur';
							}
							if ($article_dim_hauteur == 'on') {
								$tableau_apercu.= "<th>$multilangue[hauteur]<br>($multilangue[millimetres])</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[hauteur]." (".$multilangue[millimetres].')|defaut_article_dim_hauteur';
							}	
						}					
							
						//Prix de vente général						
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_prix'.$siteid} == 'on') {
								$tableau_apercu.= "<th>$multilangue[prix_vente]<br>$libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[prix_vente]."<br>".$libsite.'|defaut_article_prix'.$siteid;
							}
						}
	
						//Prix public						
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_prixpublic'.$siteid} == 'on') {
								$tableau_apercu.= "<th>$multilangue[prix_public]<br>$libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[prix_public]."<br>".$libsite.'|defaut_article_prixpublic'.$siteid;
							}
						}
	
						//Prix d'achat
						if ($article_prixachat == 'on') {
							$tableau_apercu.= "<th>$multilangue[prix_achat]</th>";
							$compteur++;
						}
	
						//Taux de tva
						if ($article_tauxchoisi == 'on') {
							$tableau_apercu.= "<th>$multilangue[taux_tva]</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[taux_tva].'|defaut_article_tauxchoisi';
						}
							
						//Délai de livraison général					
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_delai'.$siteid} == 'on') {
								$tableau_apercu.= "<th>$multilangue[delai_livraison]<br>$libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[delai_livraison]."<br>".$libsite.'|defaut_article_delai'.$siteid;
							}
						}
							
						//Commentaire
						if ($article_commentaire == 'on') {
							$tableau_apercu.= "<th>$multilangue[commentaire]</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[commentaire].'|defaut_article_commentaire';
						}
							
						//Vendu au mètre
						if(in_array(5937,$modules)){
							if ($article_aumetre == 'on') {
								$tableau_apercu.= "<th>$multilangue[vendu_au_metre]</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[vendu_au_metre].'|defaut_article_aumetre|1|0';
							}
						}
	
						//Commandable						
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_commandable'.$siteid} == 'on') {
								$tableau_apercu.= "<th>$multilangue[commandable] V2<br>$libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[commandable]."<br>".$libsite.'|defaut_article_commandable'.$siteid.'|0|0|1';
							}
						}
						
						//Actif V1
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_activeV1'.$siteid} == 'on') {
								$tableau_apercu.= "<th>$multilangue[actif] V1<br>$libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[actif]." V1 ".$libsite.'|defaut_article_activeV1'.$siteid.'|0|0|1';
							}
						}
						
						//Actif V2
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_activeV2'.$siteid} == 'on') {
								$tableau_apercu.= "<th>$multilangue[actif] V2<br>$libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[actif]." V2 ".$libsite.'|defaut_article_activeV2'.$siteid.'|0|0|1';
							}
						}
							
					
						//Image 1
						if ($article_image == 'on'){
							$tableau_apercu.= "<th>".$multilangue[image]."<br>$libsite</th>";
							$compteur++;
						}

						//Légendes 1
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_legende'.$siteid} == 'on'){
								$tableau_apercu.= "<th>".$multilangue[legende]."<br>$libsite</th>";
								$compteur++;
							}
						}

						//Image Supp
						if ($article_image2 == 'on'){
							$tableau_apercu.= "<th>".$multilangue[image]." 2<br>$libsite</th>";
							$compteur++;
						}

						//Légendes Supp
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_legende2'.$siteid} == 'on'){
								$tableau_apercu.= "<th>".$multilangue[legende]." 2<br>$libsite</th>";
								$compteur++;
							}
						}
						
	
						//Descriptions
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_description'.$siteid} == 'on') {
								$tableau_apercu.= "<th>".$multilangue[description]."<br>$libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[description]."<br>".$libsite.'|defaut_article_description'.$siteid;
							}
						}
							
						if(in_array(5846, $modules) || $mode == "test_modules"){
							//module fiche technique						
							foreach ($tab_siteid as $siteid => $libsite){
								if (${'article_fichetechnique'.$siteid} == 'on') {
									$tableau_apercu.= "<th>".$multilangue[fiche_technique]."<br>$libsite</th>";
									$compteur++;
								}else{
									$champsnnimporte[] = $multilangue[fiche_technique]."<br>".$libsite.'|defaut_article_fichetechnique'.$siteid;
								}
							}
						}
													
						//module notre avis						
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_notreavis'.$siteid} == 'on') {
								$tableau_apercu.= "<th>".$multilangue[notre_avis]."<br>$libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[notre_avis]."<br>".$libsite.'|defaut_article_notreavis'.$siteid;
							}
						}
													
						//Caractéristiques
						if ($article_caracteristiques == 'on') {
							$tableau_apercu.= "<th>".$multilangue[caracteristiques]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[caracteristiques].'|defaut_article_caracteristiques';
						}
							
						//Référence par combinaison
						if ($article_stock_artcode == 'on') {
							$tableau_apercu.= "<th>".$multilangue[reference_stock]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[reference_stock].'|defaut_article_stock_artcode';
						}
						
						//Référence fabricant par combinaison
						if ($article_stock_reference_fabricant == 'on') {
							$tableau_apercu.= "<th>".$multilangue[reference_fabricant_par_stock]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[reference_fabricant_par_stock].'|defaut_article_stock_reference_fabricant';
						}
							
						//EAN par combinaison
						if ($article_stock_code_EAN == 'on') {
							$tableau_apercu.= "<th>".$multilangue[code_ean_par_stock]."</th>";
							$compteur++;
						}
												
						//ASIN par combinaison
						if ($article_stock_ASIN == 'on') {
							$tableau_apercu.= "<th>".$multilangue[code_asin_par_stock]."</th>";
							$compteur++;
						}
							
						//Stocks par combinaison
						if ($article_stock_total == 'on') {
							$tableau_apercu.= "<th>".$multilangue[stock]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[stock].'|defaut_article_stock_total';
						}
	
						//Seuil d'alerte par combinaison
						if ($article_stock_seuil_alerte == 'on') {
							$tableau_apercu.= "<th>".$multilangue[seuil_alerte]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[seuil_alerte].'|defaut_article_stock_seuil_alerte';
						}
	
						//Délais de réappro par combinaison
						if ($article_stock_delai_appro == 'on') {
							$tableau_apercu.= "<th>".$multilangue[delai_appro]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[delai_appro].' (jours)|defaut_article_stock_delai_appro';
						}
	
						//Délais de livraison par combinaison
						if ($article_stock_delai_livraison == 'on') {
							$tableau_apercu.= "<th>".$multilangue[delai_livraison_stock]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[delai_livraison_stock].'|defaut_article_stock_delai_livraison';
						}
							
						//Zone de stockage par combinaison
						if ($article_stock_zonestockage == 'on') {
							$tableau_apercu.= "<th>".$multilangue[zone_stockage_stock]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[zone_stockage_stock].'|defaut_article_stock_zonestockage';
						}
	
						//Prix d'achat par combinaison
						if ($article_stock_prixachat == 'on') {
							$tableau_apercu.= "<th>".$multilangue[prix_achat_stock]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[prix_achat_stock]." ".$multilangue[ht].'|defaut_article_stock_prixachat';
						}
	
						//Prix de vente par combinaison
						if ($article_stock_prix == 'on') {
							$tableau_apercu.= "<th>".$multilangue[prix_vente_stock]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[prix_vente_stock].' '.$multilangue[ttc].'|defaut_article_stock_prix';
						}
							
						//Colisage
						if ($article_colisage == 'on') {
							$tableau_apercu.= "<th>".$multilangue[colisagefournisseur]."</th>";
							$compteur++;
						}else{
							$champsnnimporte[] = $multilangue[colisagefournisseur].'|defaut_article_colisage';
						}
							
						//Balises title
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_ref_title'.$siteid} == 'on') {
								$tableau_apercu.= "<th>".$multilangue[balises_meta]." title <br> $libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[balises_meta].' title <br>'.$libsite.'|defaut_article_ref_title'.$siteid;
							}
						}
							
						//Balises meta desc
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_ref_description'.$siteid} == 'on') {
								$tableau_apercu.= "<th>".$multilangue[balises_meta]." description <br> $libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[balises_meta].' description <br>'.$libsite.'|defaut_article_ref_description'.$siteid;
							}
						}
							
						//Balises meta key
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_ref_keywords'.$siteid} == 'on') {
								$tableau_apercu.= "<th>".$multilangue[balises_meta]." keywords <br> $libsite</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[balises_meta].' keywords <br>'.$libsite.'|defaut_article_ref_keywords'.$siteid;
							}
						}
						//Articles conseillés
						if ($article_conseil == 'on') {
							$tableau_apercu.= "<th>".$multilangue[articles_conseilles]."</th>";
							$compteur++;
						}
							
						if (in_array(134,$modules) || $mode == "test_modules") {
							//Tags
							if ($article_tags == 'on') {
								$tableau_apercu.= "<th>".$multilangue[tags]."</th>";
								$compteur++;
							}else{
								$champsnnimporte[] = $multilangue[tags].'|defaut_article_tags';
							}
						}
							
						$tableau_apercu.= "</tr></thead><tbody>";
					}elseif (!$exclureEntete || ($exclureEntete == 1 && $ligne != 1)){
						$compteur = 0;
						$tabChamps[$ligne] = array();
						$tableau_apercu.= "<tr id=\"ligne_".$ligne."\">";
						//Référence générale
						if ($article_artcode == 'on') {
							$tabChamps[$ligne]['artcode'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['artcode']."</td>";
							$compteur++;
						}
							
						//Identifiant article
						if ($article_artid == 'on') {
							$tabChamps[$ligne]['artid'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['artid']."</td>";
							$compteur++;
						}
	
						//Identifiant catégorie
						if ($article_catid == 'on') {
							$categs = $tableau[$ligne][$compteur] ;
							$categs = explode("|",$categs);
							foreach ($categs as $idcateg => $categ){
								$tabChamps[$ligne]['catid'.$idcateg] = $categ;
							}
							$tableau_apercu.= "<td>".$tableau[$ligne][$compteur]."</td>";
							$compteur++;
						}
							
						//Code EAN général
						if ($article_code_EAN == 'on') {
							$tabChamps[$ligne]['code_EAN'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['code_EAN']."</td>";
							$compteur++;
						}
													
						//Code ASIN général
						if ($article_ASIN == 'on') {
							$tabChamps[$ligne]['ASIN'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['ASIN']."</td>";
							$compteur++;
						}
													
						//Référence fabricant générale
						if ($article_reference_fabricant == 'on') {
							$tabChamps[$ligne]['reference_fabricant'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['reference_fabricant']."</td>";
							$compteur++;
						}
							
						if(in_array("5864",$modules) || in_array("5957",$modules) || $mode == "test_modules"){
							//Numéro tarifaire la poste
							if ($article_numero_tarifaire_laposte == 'on') {
								$tabChamps[$ligne]['numero_tarifaire_laposte'] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['numero_tarifaire_laposte']."</td>";
								$compteur++;
							}
						}
	
						//Libellés
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_libelle'.$siteid} == 'on') {
								$tabChamps[$ligne]['libelle'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['libelle'.$siteid]."</td>";
								$compteur++;
							}
						}
							
						//Sous titres
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_titre2'.$siteid} == 'on') {
								$tabChamps[$ligne]['titre2'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['titre2'.$siteid]."</td>";
								$compteur++;
							}
						}
							
						//googleshop
						if ($article_googleshop == 'on') {
							$tabChamps[$ligne]['googleshop'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['googleshop']."</td>";
							$compteur++;
						}
							
						//Marques
						if ($article_marques == 'on') {
							$tabChamps[$ligne]['marques'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['marques']."</td>";
							$compteur++;
						}
							
						//Fournisseur
						if ($article_fournisseur == 'on') {
							$tabChamps[$ligne]['fournisseur'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['fournisseur']."</td>";
							$compteur++;
						}
							
						//Poids général
						if ($article_poids == 'on') {
							$tabChamps[$ligne]['poids'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['poids']."</td>";
							$compteur++;
						}
							
						if(in_array(5950,$modules) || $mode == "test_modules"){
							//Dimensions						
							if ($article_dim_longueur == 'on') {
								$tabChamps[$ligne]['longueur'] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['longueur']."</td>";
								$compteur++;
							}
							if ($article_dim_largeur == 'on') {
								$tabChamps[$ligne]['largeur'] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['largeur']."</td>";
								$compteur++;
							}
							if ($article_dim_hauteur == 'on') {
								$tabChamps[$ligne]['hauteur'] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['hauteur']."</td>";
								$compteur++;
							}
						}
													
						//Prix de vente général						
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_prix'.$siteid} == 'on'){
								$tabChamps[$ligne]['prix'.$siteid] = $tableau[$ligne][$compteur];
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['prix'.$siteid]."</td>";
								$compteur++;
							}
						}
	
						//Prix public						
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_prixpublic'.$siteid} == 'on'){
								$tabChamps[$ligne]['prixpublic'.$siteid] = $tableau[$ligne][$compteur];
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['prixpublic'.$siteid]."</td>";
								$compteur++;
							}
						}
	
						//Prix achat
						if ($article_prixachat == 'on') {
							$tabChamps[$ligne]['prixachat'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['prixachat']."</td>";
							$compteur++;
						}						
	
						//Taux de tva
						if ($article_tauxchoisi == 'on') {
							$tabChamps[$ligne]['tauxchoisi'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['tauxchoisi']."</td>";
							$compteur++;
						}
							
						//Délai de livraison général						
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_delai'.$siteid} == 'on'){
								$tabChamps[$ligne]['delai'.$siteid] = $tableau[$ligne][$compteur];
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['delai'.$siteid]."</td>";
								$compteur++;
							}
						}
							
						//Commentaire
						if ($article_commentaire == 'on') {
							$tabChamps[$ligne]['commentaire'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['commentaire']."</td>";
							$compteur++;
						}
							
						//Vendu au mètre
						if ($article_aumetre == 'on') {
							$tabChamps[$ligne]['prixaumetre'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['prixaumetre']."</td>";
							$compteur++;
						}
	
						//Commandable						
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_commandable'.$siteid} == 'on'){
								$tabChamps[$ligne]['commandable'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['commandable'.$siteid]."</td>";
								$compteur++;
							}
						}
						
						//Active V1
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_activeV1'.$siteid} == 'on'){
								$tabChamps[$ligne]['activeV1'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['activeV1'.$siteid]."</td>";
								$compteur++;
							}
						}
						
						//Active V2
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_activeV2'.$siteid} == 'on'){
								$tabChamps[$ligne]['activeV2'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['activeV2'.$siteid]."</td>";
								$compteur++;
							}
						}							
						
						//Image 1
						if ($article_image == 'on'){
							$tabChamps[$ligne]['url_img0'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['url_img0']."</td>";
							$compteur++;
						}

						//Légendes 1
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_legende'.$siteid} == 'on'){
								$tabChamps[$ligne]['legende_img0'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['legende_img0'.$siteid]."</td>";
								$compteur++;
							}
						}

						//Image Supp
						if ($article_image2 == 'on'){
							$tabChamps[$ligne]['url_img1'] = $tableau[$ligne][$compteur];
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['url_img1']."</td>";
							$compteur++;
						}

						//Légendes Supp
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_legende2'.$siteid} == 'on'){
								$tabChamps[$ligne]['legende_img1'.$siteid] = $tableau[$ligne][$compteur];
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['legende_img1'.$siteid]."</td>";
								$compteur++;
							}
						}
						
	
						//Descriptions
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_description'.$siteid} == 'on') {
								$tabChamps[$ligne]['description'.$siteid] = $tableau[$ligne][$compteur];
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['description'.$siteid]."</td>";
								$compteur++;
							}
						}
							
						if(in_array(5846, $modules) || $mode == "test_modules"){
							//module fiche technique						
							foreach ($tab_siteid as $siteid => $libsite){
								if (${'article_fichetechnique'.$siteid} == 'on') {
									$tabChamps[$ligne]['fichetechnique'.$siteid] = $tableau[$ligne][$compteur];
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['fichetechnique'.$siteid]."</td>";
									$compteur++;
								}
							}	
						}					
							
						//module notre avis						
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_notreavis'.$siteid} == 'on'){
								$tabChamps[$ligne]['notreavis'.$siteid] = $tableau[$ligne][$compteur];
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['notreavis'.$siteid]."</td>";
								$compteur++;
							}
						}						
							
						//Caractéristiques
						if ($article_caracteristiques == 'on') {
							$tabChamps[$ligne]['caracteristiques'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['caracteristiques']."</td>";
							$compteur++;
						}
							
						//Référence par combinaison
						if ($article_stock_artcode == 'on') {
							$tabChamps[$ligne]['stock_artcode'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_artcode']."</td>";
							$compteur++;
						}
							
						//Référence fabricant par combinaison
						if ($article_stock_reference_fabricant == 'on') {
							$tabChamps[$ligne]['stock_reference_fabricant'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_reference_fabricant']."</td>";
							$compteur++;
						}
						
						//EAN par combinaison
						if ($article_stock_code_EAN == 'on') {
							$tabChamps[$ligne]['stock_code_EAN'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_code_EAN']."</td>";
							$compteur++;
						}							
						
						//ASIN par combinaison
						if ($article_stock_ASIN == 'on') {
							$tabChamps[$ligne]['stock_ASIN'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_ASIN']."</td>";
							$compteur++;
						}
	
						//Stocks par combinaison
						if ($article_stock_total == 'on') {
							$tabChamps[$ligne]['stock_total'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_total']."</td>";
							$compteur++;
						}
	
						//Seuil d'alerte par combinaison
						if ($article_stock_seuil_alerte == 'on') {
							$tabChamps[$ligne]['stock_seuil_alerte'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_seuil_alerte']."</td>";
							$compteur++;
						}
	
						//Délais de réappro par combinaison
						if ($article_stock_delai_appro == 'on') {
							$tabChamps[$ligne]['stock_delai_appro'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_delai_appro']."</td>";
							$compteur++;
						}
	
						//Délais de livraison par combinaison
						if ($article_stock_delai_livraison == 'on') {
							$tabChamps[$ligne]['stock_delai_livraison'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_delai_livraison']."</td>";
							$compteur++;
						}
							
						//Zone de stockage par combinaison
						if ($article_stock_zonestockage == 'on') {
							$tabChamps[$ligne]['stock_zonestockage'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_zonestockage']."</td>";
							$compteur++;
						}
	
						//Prix d'achat par combinaison
						if ($article_stock_prixachat == 'on') {
							$tabChamps[$ligne]['stock_prixachat'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_prixachat']."</td>";
							$compteur++;
						}
	
						//Prix de vente par combinaison
						if ($article_stock_prix == 'on') {
							$tabChamps[$ligne]['stock_prix'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['stock_prix']."</td>";
							$compteur++;
						}
							
						//Colisage
						if ($article_colisage == 'on') {
							$tabChamps[$ligne]['colisage'] = $tableau[$ligne][$compteur] ;
							$tableau_apercu.= "<td>".$tabChamps[$ligne]['colisage']."</td>";
							$compteur++;
						}
							
						//Balises title
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_ref_title'.$siteid} == 'on') {
								$tabChamps[$ligne]['ref_title'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['ref_title'.$siteid]."</td>";
								$compteur++;
							}
						}
							
						//Balises meta desc
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_ref_description'.$siteid} == 'on') {
								$tabChamps[$ligne]['ref_description'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['ref_description'.$siteid]."</td>";
								$compteur++;
							}
						}
							
						//Balises meta key
						foreach ($tab_siteid as $siteid => $libsite){
							if (${'article_ref_keywords'.$siteid} == 'on') {
								$tabChamps[$ligne]['ref_keywords'.$siteid] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['ref_keywords'.$siteid]."</td>";
								$compteur++;
							}
						}
						//Articles conseillés
						if ($article_conseil == 'on') {
							$artconseil = $tableau[$ligne][$compteur] ;
							$artconseil = explode("|",$artconseil);
							foreach ($artconseil as $idcons => $refart){
								$tabChamps[$ligne]['artconseil'.$idcons] = $refart;
							}
							$tableau_apercu.= "<td>".$tableau[$ligne][$compteur]."</td>";
							$compteur++;
						}
						
						if (in_array(134,$modules) || $mode == "test_modules") {	
							//Tags
							if ($article_tags == 'on') {
								$tabChamps[$ligne]['tags'] = $tableau[$ligne][$compteur] ;
								$tableau_apercu.= "<td>".$tabChamps[$ligne]['tags']."</td>";
								$compteur++;
							}
						}
							
						$tableau_apercu.= "</tr>";
					}
				}
				$tableau_apercu.= "</tbody></table>";

				$contenu.=$tableau_apercu;
												
				$contenu.="</div><br>";
				if (count($champsnnimporte) > 0){
					$contenu.="
							<div class=\"col-md-12 ta-center\">
								<a href=\"$_SERVER[PHP_SELF]\" class=\"btn grey-silver\"><i class=\"fa fa-arrow-left\"></i> $multilangue[retour]</a>
								<button type=\"submit\" class=\"btn green\"><i class=\"fa fa-download\"></i> Importer</button>
							</div>
							<div class=\"clear\"></div>
							<div style=\"margin:10px;\">
								<center><b>Ajouter une valeur par défaut aux articles importés :</b></center>
							</div>										
							<div class=\"col-md-3\"></div>
							<div class=\"col-md-6\">
							<div class=\"form-body\" style=\"border-left:1px solid #EFEFEF;\">";
					foreach($champsnnimporte as $champs){
						$valeur = explode('|',$champs);
						$contenu.="
						<div class=\"form-group\">							
							<label class=\"control-label col-md-5\">								
								$valeur[0]							
							</label>
							<div class=\"col-md-7\">";
						
						if($valeur[4] == 1){
							$contenu.= "<div class=\"radio-list\">
											<label class=\"radio-inline\">
											<input type=\"radio\" name=\"$valeur[1]\" checked=\"checked\" value=\"0\"> $multilangue[non] </label>
											<label class=\"radio-inline\">
											<input type=\"radio\" name=\"$valeur[1]\" value=\"1\"> $multilangue[oui] </label>
											<label class=\"radio-inline\">
											<input type=\"radio\" name=\"$valeur[1]\" checked=\"checked\" value=\"\"> $multilangue[ne_rien_faire] </label>
										</div>";
						}elseif($valeur[3] == 1){
							$contenu.= "<div class=\"radio-list\">
											<label class=\"radio-inline\">
											<input type=\"radio\" name=\"$valeur[1]\" value=\"0\"> $multilangue[non] </label>
											<label class=\"radio-inline\">
											<input type=\"radio\" name=\"$valeur[1]\" checked=\"checked\" value=\"1\"> $multilangue[oui] </label>
											<label class=\"radio-inline\">
											<input type=\"radio\" name=\"$valeur[1]\" value=\"\"> $multilangue[ne_rien_faire] </label>
										</div>";
						}elseif($valeur[2] == 1){
							$contenu.= "<div class=\"radio-list\">
											<label class=\"radio-inline\">
											<input type=\"radio\" name=\"$valeur[1]\" checked=\"checked\" value=\"0\"> $multilangue[non] </label>
											<label class=\"radio-inline\">
											<input type=\"radio\" name=\"$valeur[1]\" value=\"1\"> $multilangue[oui] </label>
											<label class=\"radio-inline\">
											<input type=\"radio\" name=\"$valeur[1]\" value=\"\"> $multilangue[ne_rien_faire] </label>
										</div>";
						}else{
							$contenu.=	"<input type=\"text\" class=\"form-control\" name=\"$valeur[1]\" />";
						}
						$contenu.=	"</div>										
								</div>";
					}
					$contenu.="</div>
							<div class=\"col-md-3\"></div>";
				}				
			}
			
			$apercu = $contenu;
						
			$_SESSION[tabChampsArticles]=$tabChamps;
						
		} else {
			$message = "$erreur";
		}		
	}
	$libNavigSupp="<b>Importer des ".$type."s au format CSV - Etape 3</b>";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	
	eval(charge_template($langue,$referencepage,"ImportEtape3Articles"));
}



/** ********************************************************************************
 *************************** Prévisualisation de l'import **************************
 ************************************* FIN ETAPE 3 ****************************** **/



/** ************************************ ETAPE 2 ***********************************
 ************************** Structure du fichier à importer ************************
 ******************************************************************************** **/
if (isset($action) && $action == "importer"){
	// fichier import
	$fichierImport = "import_" . $type . "s.php";
	// liste des champs cochés
	$listeInputsHidden = "";
	$lignesPrevisu = "";
	$prefixe = $type . "_";
	$compteur = 0;
	foreach($_POST as $champ => $valeur ){
		$prefixeChamp = substr ( $champ, 0, strlen ( $prefixe ) );
		if ($prefixeChamp == $prefixe) {
			$compteur ++;
			$listeInputsHidden .= "<input type=\"hidden\" name=\"$champ\" value=\"on\">\n";
			$lignesPrevisu .= transformeIdLettresExcel($compteur).") $valeur<br>";
		}	
	}
	
	$libNavigSupp="<b>Importer des ".$type."s au format CSV - Etape 2</b>";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	
	eval(charge_template($langue,$referencepage,"ImportEtape2"));
}
/** ********************************** FIN ETAPE 2 *********************************
 ************************** Structure du fichier à importer ************************
 ******************************************************************************** **/


/** ****************************** AFFICHAGE DEPART *******************************
***********************************************************************************
******************************************************************************** **/	

if (!isset($action) || $action == "") {
	$activeimport="active";
	
	/** ********************** IMPORT Catégories ****************************************/	
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while ($site = $DB_site->fetch_array($sites)){
		eval(charge_template($langue,$referencepage,"CategorieLibelleBit"));
		eval(charge_template($langue,$referencepage,"CategorieDescriptionBit"));
		eval(charge_template($langue,$referencepage,"CategorieMetaTitleBit"));
		eval(charge_template($langue,$referencepage,"CategorieMetaDescBit"));
		eval(charge_template($langue,$referencepage,"CategorieMetaKeyBit"));
	}	
	
	if (in_array("5836", $modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"Categorie5836"));
	}
	if (in_array("5913", $modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"Categorie5913"));
	}
	if (in_array("5927", $modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"Categorie5927"));
	}
	/** ********************** Fin IMPORT Catégories ****************************************/
	
	
	/** ********************** IMPORT Articles ****************************************/
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while ($site = $DB_site->fetch_array($sites)){
		eval(charge_template($langue,$referencepage,"ArticleLibelleBit"));
		eval(charge_template($langue,$referencepage,"ArticleTitre2Bit"));
		eval(charge_template($langue,$referencepage,"ArticlePrixBit"));
		eval(charge_template($langue,$referencepage,"ArticlePrixpublicBit"));
		eval(charge_template($langue,$referencepage,"ArticleDelaiBit"));
		eval(charge_template($langue,$referencepage,"ArticleCommandableBit"));
		eval(charge_template($langue,$referencepage,"ArticleActiveV1Bit"));
		eval(charge_template($langue,$referencepage,"ArticleActiveV2Bit"));
		eval(charge_template($langue,$referencepage,"ArticleLegendeBit"));
		eval(charge_template($langue,$referencepage,"ArticleLegende2Bit"));
		eval(charge_template($langue,$referencepage,"ArticleDescriptionBit"));
		if(in_array(5846, $modules) || $mode == "test_modules"){
			eval(charge_template($langue,$referencepage,"ArticleFichetechniqueBit"));
		}
		if(in_array(5847,$modules) || $mode == "test_modules"){
			eval(charge_template($langue,$referencepage,"ArticleNotreavisBit"));
		}		
		eval(charge_template($langue,$referencepage,"ArticleMetatitleBit"));
		eval(charge_template($langue,$referencepage,"ArticleMetadescBit"));
		eval(charge_template($langue,$referencepage,"ArticleMetakeyBit"));
		
		// Tags
		if (in_array(134,$modules) || $mode == "test_modules") {
			eval(charge_template($langue,$referencepage,"Article134Bit"));
		}		
	}
	
	//Code ASIN général
	if (in_array("5983", $modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"Article5983"));
	}
	
	//Numéro tarifaire La Poste
	if(in_array("5864",$modules) || in_array("5957",$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"Article5864_5957"));
	}	
	
	// Google shop
	if(in_array (5922,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"Article5922"));
	}
		
	// Dimensions
	if(in_array(5950,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"Article5950"));
	}
	
	// Vendu au mètre
	if(in_array(5937,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"Article5937"));
	}
	
	if (in_array(5944,$modules) || $mode == "test_modules") {
		eval(charge_template($langue,$referencepage,"Article5944"));
	}
		
	// Articles bundles 
	if (in_array(5901,$modules) || $mode == "test_modules") {
		eval(charge_template($langue,$referencepage,"Article5901"));
	}
	/** ********************** Fin IMPORT Articles ****************************************/
	
	
	/** ********************** IMPORT Promotions ****************************************/
	/** ********************** Fin IMPORT Promotions ****************************************/
	
	
	/** ********************** EXPORT Catégories ****************************************/
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while ($site = $DB_site->fetch_array($sites)){
		// Sites pour partie gauche
		eval(charge_template($langue,$referencepage,"CategorieSiteBit"));
		
		eval(charge_template($langue,$referencepage,"CategorieExpLibelleBit"));
		eval(charge_template($langue,$referencepage,"CategorieExpDescriptionBit"));
		eval(charge_template($langue,$referencepage,"CategorieExpMetaTitleBit"));
		eval(charge_template($langue,$referencepage,"CategorieExpMetaDescBit"));
		eval(charge_template($langue,$referencepage,"CategorieExpMetaKeyBit"));
		
		// Champs non présents dans l'import
		eval(charge_template($langue,$referencepage,"CategorieExpUrlBit"));
		eval(charge_template($langue,$referencepage,"CategorieExpArboBit"));
		eval(charge_template($langue,$referencepage,"CategorieExpImageBit"));
		if(in_array(5813, $modules) || $mode == "test_modules"){
			eval(charge_template($langue,$referencepage,"CategorieExpImage2Bit"));
		}
		if(in_array(5927, $modules) || $mode == "test_modules"){
			eval(charge_template($langue,$referencepage,"CategorieExpImage3Bit"));
		}		

		eval(charge_template($langue,$referencepage,"CategorieExpArticlesActifsBit"));
	}
	
	if (in_array("5836", $modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"CategorieExp5836"));
	}
	if (in_array("5913", $modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"CategorieExp5913"));
	}
	if (in_array("5927", $modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"CategorieExp5927"));
	}
	// Background catégorie
	if (in_array("5929", $modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"CategorieExp5929"));
	}
	
	if(in_array(4,$modules)){
		eval(charge_template($langue,$referencepage,"CategorieExpArticlesStock"));
	}else{
		$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
		while ($site = $DB_site->fetch_array($sites)){
			eval(charge_template($langue,$referencepage,"CategorieExpArticlesCommandableBit"));
		}
	}		
	/** ********************** Fin EXPORT Catégories ****************************************/
	
	/** ********************** EXPORT Articles ****************************************/
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while ($site = $DB_site->fetch_array($sites)){
		// Sites pour partie gauche
		eval(charge_template($langue,$referencepage,"ArticleExpSiteBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpSiteBit2"));
		
		eval(charge_template($langue,$referencepage,"ArticleExpLibelleBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpTitre2Bit"));
		eval(charge_template($langue,$referencepage,"ArticleExpPrixBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpPrixpublicBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpDelaiBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpCommandableBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpActiveV1Bit"));
		eval(charge_template($langue,$referencepage,"ArticleExpActiveV2Bit"));
		eval(charge_template($langue,$referencepage,"ArticleExpLegendeBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpLegende2Bit"));
		eval(charge_template($langue,$referencepage,"ArticleExpDescriptionBit"));
		if(in_array(5846, $modules) || $mode == "test_modules"){
			eval(charge_template($langue,$referencepage,"ArticleExpFichetechniqueBit"));
		}
		if(in_array(5847,$modules) || $mode == "test_modules"){
			eval(charge_template($langue,$referencepage,"ArticleExpNotreavisBit"));
		}
		eval(charge_template($langue,$referencepage,"ArticleExpMetatitleBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpMetadescBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpMetakeyBit"));
		
		
		eval(charge_template($langue,$referencepage,"ArticleExpPrixpromoBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpPctpromoBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpDatedebutBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpDatefinBit"));
		eval(charge_template($langue,$referencepage,"ArticleExpNbventesBit"));
		
		// Tags
		if (in_array(134,$modules) || $mode == "test_modules") {
			eval(charge_template($langue,$referencepage,"ArticleExp134Bit"));
		}
	}
	
	//*** Critères gauche
	$fournisseurs=$DB_site->query("SELECT DISTINCT(fournisseurid), libelle FROM fournisseur f INNER JOIN article a USING (fournisseurid) ORDER BY libelle");
	if ($DB_site->num_rows($fournisseurs>0)){			
		while($fournisseur=$DB_site->fetch_array($fournisseurs)) {
			eval(charge_template($langue,$referencepage,"ArticleExpFournisseurBit"));
		}
		eval(charge_template($langue,$referencepage,"ArticleExpFournisseur"));
	}
	
	$marques=$DB_site->query("SELECT DISTINCT(marqueid), libelle FROM marque m 
			INNER JOIN marque_site AS ms USING(marqueid) 
			INNER JOIN article_marque am USING (marqueid) WHERE ms.siteid='1' ORDER BY ms.libelle");	
	if ($DB_site->num_rows($marques>0)){		
		while($marque=$DB_site->fetch_array($marques)) {
			eval(charge_template($langue,$referencepage,"ArticleExpMarqueBit"));
		}
		eval(charge_template($langue,$referencepage,"ArticleExpMarque"));
	}
	
	//Module promotions
	if(in_array(5,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExpTri5"));
	}
	//Module nouveautés
	if(in_array(17,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExpTri7"));
	}
	//Module top vente
	if(in_array(19,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExpTri19"));
	}
	//Module coups de coeur
	if(in_array(21,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExpTri21"));
	}
	//Module stock
	if(in_array(4,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExpTri4"));
	}
	//Module produits immatériels
	if(in_array(5888,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExpTri5888"));
	}
	if(in_array(5901,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExpTri5901"));
	}
	
	//*** FIN Critères gauche
	
	
	//Code ASIN général
	if (in_array("5983", $modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExp5983"));
	}
	
	//Numéro tarifaire La Poste
	if(in_array("5864",$modules) || in_array("5957",$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExp5864_5957"));
	}
	
	// Google shop
	if(in_array (5922,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExp5922"));
	}
	
	// Dimensions
	if(in_array(5950,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExp5950"));
	}
	
	// Vendu au mètre
	if(in_array(5937,$modules) || $mode == "test_modules"){
		eval(charge_template($langue,$referencepage,"ArticleExp5937"));
	}
	
	if (in_array(5944,$modules) || $mode == "test_modules") {
		eval(charge_template($langue,$referencepage,"ArticleExp5944"));
	}
	
	// Articles bundles
	if (in_array(5901,$modules) || $mode == "test_modules") {
		eval(charge_template($langue,$referencepage,"ArticleExp5901"));
	}
	/** ********************** Fin EXPORT Articles ****************************************/
	
	

	
	/** ********************** EXPORT Promotions ****************************************/
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while ($site = $DB_site->fetch_array($sites)){
		// Sites pour partie gauche
		eval(charge_template($langue,$referencepage,"PromotionExpSiteBit"));
	}
	/** ********************** Fin EXPORT Promotions ****************************************/
	


	eval(charge_template($langue,$referencepage,"Depart"));
}
/** ********************** FIN AFFICHAGE DEPART **************************************
 *************************************************************************************
 ********************************************************************************** **/

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage,"Includejavascript"));
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>