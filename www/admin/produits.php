<?php
include "./includes/header.php";

$referencepage="produits";
$pagetitle = "Produits - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if (isset ($artid) and $artid != "") {
	clearDir($GLOBALS[rootpath]."configurations/".$GLOBALS[host]."/cache/articles/$artid");
}



// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//Article_promo_site : copie des promos FR pour tous les autres sites
/*$promos_site = $DB_site->query("SELECT * FROM  article_promo_site WHERE siteid = '1'");
while($promo_site = $DB_site->fetch_array($promos_site)){

	$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
	while($site = $DB_site->fetch_array($sites)){
		$existe_promo_site = $DB_site->query_first("SELECT * FROM article_promo_site
														WHERE artid = '$couleur_site[artid]'
														AND siteid='$site[siteid]'");
			
		if($existe_promo_site[promoid] == ""){
			$DB_site->query("INSERT INTO article_promo_site (artid, siteid, pctpromo, datedebut, datefin, datesaisie)
							VALUES ('$promo_site[artid]', '$site[siteid]', '$promo_site[pctpromo]', '$promo_site[datedebut]', '$promo_site[datefin]', '$promo_site[datesaisie]')");
		}else{
			$DB_site->query("UPDATE article_promo_site SET pctpromo='$promo_site[pctpromo]',  datedebut='$promo_site[datedebut]', 
								datefin='$promo_site[datefin]', datesaisie='$promo_site[datesaisie]'
								WHERE promoid = '$existe_promo_site[promoid]'");
		}
	}
}*/

//$mode = "test_modules";

$timenow = time();

if(isset($action) && $action == "supprimerPng"){
	if($admin_droit[$scriptcourant][suppression]){
		$folder = $rootpath."configurations/$host/images/articles";
		if (file_exists($folder."/".$artid.".png")) {
			unlink($folder."/".$artid.".png");
		}
		header("location: produits.php?action=modifier&form=photos&artid=$artid");
	}else{
		header('location: produits.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "supprimerPrincipale"){
	if($admin_droit[$scriptcourant][suppression]){
		$article = $DB_site->query_first("SELECT image FROM article WHERE artid = '$artid'");
		$fichier = $artid.".".$article[image] ;
		deletephoto($fichier);
		
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			$DB_site->query("UPDATE article SET image = '' WHERE artid = '$artid'");
		}
		header("location: produits.php?action=modifier&form=photos&artid=$artid");
	}else{
		header('location: produits.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "supprimerSupp"){
	if($admin_droit[$scriptcourant][suppression]){
		$article = $DB_site->query_first("SELECT image FROM articlephoto WHERE artid = '$artid' AND articlephotoid = '$photoid'");
		$fichier = $artid.".".$article[image]."_".$photoid ;
		deletephoto($fichier);
		
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			$DB_site->query("UPDATE articlephoto SET image = '' WHERE articlephotoid = '$photoid' ");
		}
		header("location: produits.php?action=modifier&form=photos&artid=$artid");
	}else{
		header('location: produits.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "supprPromo"){
	if($admin_droit[$scriptcourant][suppression]){
		$date_historique = $DB_site->query_first("SELECT datefin, datedebut FROM article_promo_site WHERE promoid = '$promoid'");
	
		if($date_historique[datefin] > time() && $date_historique[datedebut] < time()){
			$DB_site->query("UPDATE article_historique_prix_site SET datefin = '".time()."' WHERE promoid = '$promoid'");
		}
		
		if($date_historique[datefin] > time() && $date_historique[datedebut] > time()){
			$DB_site->query("DELETE FROM article_historique_prix_site WHERE promoid = '$promoid'");
		}
		
		$DB_site->query("DELETE FROM article_promo_site WHERE promoid = '$promoid'");
		header("location: produits.php?action=modifier&form=promotion&artid=$artid");
	}
}


if (isset($action) and $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		supprimer_article($DB_site, $artid);
		$tabcatidsEnfants = array();
		compter_produits($DB_site);
		compter_produits_actifs($DB_site);
		header('location: produits.php');
	}
}


if (isset($action) and $action == "modifier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		switch ($form){
			case "infos":
				if ($artid == ""){
					$DB_site->query("INSERT INTO article(artid, catid) VALUES ('', '$catid')");
					$artid = $DB_site->insert_id();
					
					$max_position = $DB_site->query_first("SELECT MAX(position) position FROM position WHERE catid='$catid'");
					$new_position = $max_position[0]+1;
					$DB_site->query("INSERT INTO position (artid, catid, position) VALUES ('$artid', '$catid', '$new_position')");
											
					$DB_site->query("INSERT INTO article_site(artid, siteid) VALUES ('$artid', '1')");
					$DB_site->query("INSERT INTO stock(artid) VALUES ('$artid')");
					$nouvelarticle = 1;
				}else{
					$old_catid = $DB_site->query_first("SELECT catid FROM article WHERE artid = '$artid' ");
					$old_catid = $old_catid[0];
					if($old_catid != $catid){
						$old_position = $DB_site->query_first("SELECT position FROM position WHERE catid = '$old_catid' AND artid = '$artid' ");
						$old_position = $old_position[0];
						$DB_site->query("UPDATE position SET position = position - 1 WHERE catid = '$old_catid' AND position > $old_position");
						$DB_site->query("DELETE FROM position WHERE artid = '$artid' AND catid = '$old_catid' ");
						
						$max_position = $DB_site->query_first("SELECT MAX(position) position FROM position WHERE catid='$catid'");
						$new_position = $max_position[0]+1;
						$DB_site->query("INSERT INTO position (artid, catid, position) VALUES ('$artid', '$catid', '$new_position')");
					}
				}
				$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site WHERE article.artid = '$artid' AND siteid = '1'");
				$article_historique = $DB_site->query_first("SELECT * FROM article_historique_prix_site WHERE artid='$artid' AND siteid='1' AND datefin='0'");
				if(!$article_historique[article_historique_prixid]){
					$DB_site->query("INSERT INTO article_historique_prix_site(artid, siteid, prix, prixpro, datesaisie, datedebut) VALUES ('$artid', '1', '$prix', '$prixpro', '".time()."', '".time()."')");
				}else{
					if($prix == ""){
						$nouveau_prix = 0;
					}else{
						$nouveau_prix = $prix;
					}
					
					if($prixpro == ""){
						$nouveau_prixpro = 0;
					}else{
						$nouveau_prixpro = $prixpro;
					}
					if ($nouveau_prix != $article_historique[prix] || $nouveau_prixpro != $article_historique[prixpro]){
						$DB_site->query("UPDATE article_historique_prix_site SET datefin = '".time()."' WHERE article_historique_prixid = '$article_historique[article_historique_prixid]'");
						$DB_site->query("INSERT INTO article_historique_prix_site(artid, siteid, prix, prixpro, datedebut, datesaisie) VALUES ('$artid', '1', '$nouveau_prix', '$nouveau_prixpro', '".time()."', '" . time() . "')");
					}
				}
				$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET artcode = '" . securiserSql($_POST[artcode]) . "', code_EAN = '" . securiserSql($_POST[code_EAN]) . "', reference_fabricant = '" . securiserSql($_POST[reference_fabricant]) . "', catid = '$catid',
								prix = '" . securiserSql($_POST[prix]) . "', prixpro = '" . ($prixpro ? securiserSql($_POST[prixpro]) : 0) . "',  prixpublic = '" . securiserSql($_POST[prixpublic]) . "', ecotaxe = '" . securiserSql($_POST[ecotaxe]) . "',
								tauxchoisi = '" . securiserSql($_POST[tauxtva]) . "', poids = '" . securiserSql($_POST[poids]) . "', delai = '" . securiserSql($_POST[delai]) . "',
								libelle = '" . securiserSql($_POST[libelle]) . "', titre2 = '" . securiserSql($_POST[titre2]) . "', commandable = '" . ($_POST[commandable] ? 1 : 0) . "', typearticle = '" . securiserSql($_POST[typearticle]) . "', activeV1 = '" . ($_POST[activeV1] ? 1 : 0) . "', activeV2 = '" . ($_POST[activeV2] ? 1 : 0) . "',
								etiquette_R = '".($_POST[appliquer_remise] ? 1 : 0)."', commentaire = '" . securiserSql($_POST[commentaire]) . "', stock_illimite = '" . securiserSql($_POST[stock_illimite]) . "', colisagefournisseur = '" . securiserSql($_POST[colisagefournisseur]) . "', eco_participation ='" . ($_POST[eco_participation] ? 1 : 0) . "',
								pays_origine = '" . securiserSql($_POST[paysid]) . "', non_mecanisable = '" . ($non_mecanisable ? securiserSql($_POST[non_mecanisable]) : 0) . "', non_mecanisable_tarifid = '" . ($non_mecanisable_tarifid ? securiserSql($_POST[non_mecanisable_tarifid]) : 0) . "',
								ASIN = '" . securiserSql($_POST[ASIN]) . "', article_flash = '" . securiserSql($_POST[article_flash]) . "', article_texte_perso = '" . securiserSql($_POST[article_texte_perso]) . "'
								WHERE artid = '$artid' AND siteid = '1'");
				if (in_array(5950, $modules) || $mode == "test_modules") {
					if ($longueur == "" || $longueur < 0)
						$longueur = 0;
					if ($largeur == "" || $largeur < 0)
						$largeur = 0;
					if ($hauteur == "" || $hauteur < 0)
						$hauteur = 0;
					$DB_site->query("UPDATE article SET longueur = '" . securiserSql($_POST[longueur]) . "', largeur = '" . securiserSql($_POST[largeur]) . "', hauteur = '" . securiserSql($_POST[hauteur]) . "' WHERE artid = '$artid'");
				}
				if (in_array (5956, $modules) || $mode == "test_modules")
					$DB_site->query("UPDATE article SET produit_pro = '" . ($produit_pro ? 1 : 0) . "', produit_part = '" . ($produit_part ? 1 : 0) . "' WHERE artid = '$artid'");
				if (in_array(5908, $modules) || $mode == "test_modules")
					$DB_site->query("UPDATE article SET etiquetteid = '" . securiserSql($_POST[etiquetteid]) . "' WHERE artid = '$artid'");
				
				// Dev spé Monterrrat
				$DB_site->query("UPDATE article SET articlemoduleid = '" . securiserSql($_POST[articlemoduleid]) . "' WHERE artid = '$artid'");
				
				if (in_array(5937, $modules) || $mode == "test_modules")
					$DB_site->query("UPDATE article SET prixaumetre = '" . ($prixaumetre ? 1 : 0) . "' WHERE artid = '$artid'");
				if (count($marquesTab)){
					$DB_site->query("DELETE FROM article_marque WHERE artid = '$artid'");
					foreach ($marquesTab as $key => $value)
						$DB_site->query("INSERT INTO article_marque(artid, marqueid) VALUES ('$artid', '$key')");
				}
				if (count($fournisseurTab)){
					foreach ($fournisseurTab as $key => $value)
						$DB_site->query("UPDATE article SET fournisseurid = '$key' WHERE artid = '$artid'");
				} else {
					$DB_site->query("UPDATE article SET fournisseurid = '0' WHERE artid = '$artid'");
				}
				if (in_array("5806" , $modules) || $mode == "test_modules"){
					if (in_array("5869" , $modules) || $mode == "test_modules"){
						$rueducommerce = $DB_site->query_first("SELECT COUNT(artid) count FROM rueducommerce WHERE artid = '$artid'");
						if (!$rueducommerce[count])
							$DB_site->query("INSERT INTO rueducommerce(artid, MCID) VALUES ('$artid', '" . securiserSql($_POST[MCID]) . "')");
						else
							$DB_site->query("UPDATE rueducommerce SET MCID = '" . securiserSql($_POST[MCID]) . "' WHERE artid = '$artid'");
					}
					if (in_array("5870" , $modules) || $mode == "test_modules"){
						$pixmania = $DB_site->query_first("SELECT COUNT(artid) count FROM pixmania WHERE artid = '$artid'");
						if (!$pixmania[count])
							$DB_site->query("INSERT INTO pixmania(artid, segmentID, marqueID) VALUES ('$artid', '" . securiserSql($_POST[segmentID]) . "', '" . securiserSql($_POST[marqueID]) . "')");
						else
							$DB_site->query("UPDATE pixmania SET segmentID = '" . securiserSql($_POST[segmentID]) . "', marqueID = '" . securiserSql($_POST[marqueID]) . "' WHERE artid = '$artid'");
					}
				}
				if (in_array("5922" , $modules) || $mode == "test_modules"){
					$DB_site->query("DELETE FROM googleshopping WHERE artid = '$artid'");
					if ($googleshoppingattributid)
						$DB_site->query("INSERT INTO googleshopping(attributid, artid) VALUES ('" . securiserSql($_POST[googleshoppingattributid]) . "','$artid')");
				}
				if (in_array("5955" , $modules) || $mode == "test_modules") {
					$DB_site->query("DELETE FROM trouversoncadeau_articles WHERE artid = '$artid'");
					if (count($trouversoncadeau)){
						foreach($trouversoncadeau as $key => $value){
							if ($value > 0)
								$DB_site->query("INSERT INTO trouversoncadeau_articles(artid, trouversoncadeau_catid) VALUES ('$artid', '$value')");
						}
					}
				}
				if (in_array("5954" , $modules) || $mode == "test_modules"){
					$DB_site->query("DELETE FROM nextag WHERE artid = '$artid'");
					if ($nextagcatnextagid)
						$DB_site->query("INSERT INTO nextag(catnextagid, artid) VALUES ('$nextagcatnextagid', '$artid')");
				}
				if (in_array("5936" , $modules) || $mode == "test_modules") {
					$DB_site->query("DELETE FROM article_kelpack WHERE artid='$artid'");
					if ($kelpackselect)
						$DB_site->query("INSERT INTO article_kelpack(kelpackid, artid) VALUES ('$kelpackselect', '$artid')");
				}
				if (in_array("110", $modules) || $mode == "test_modules")
					$DB_site->query("UPDATE article SET fianet_categorieid = '$fianet_categorieid' WHERE artid = '$artid'");
				if (in_array("5872", $modules) || $mode == "test_modules")
					$DB_site->query("UPDATE article_site SET dateparution = '" . strtotime(str_replace('/', '-', $dateparution)) . "', dateparutionfin = '" . strtotime(str_replace('/', '-', $dateparutionfin)) . "' WHERE artid = '$artid' AND siteid = '1'");
				if (in_array("5888", $modules) || $mode == "test_modules")
					$DB_site->query("UPDATE article SET immateriel = '". ($immateriel ? 1 : 0) . "' WHERE artid = '$artid'");
				if (in_array("5901", $modules) || $mode == "test_modules"){
					$DB_site->query("UPDATE article SET isbundle = '" . ($isbundle ? 1 : 0) . "' WHERE artid = '$artid'");
					if ($isbundle){
						$DB_site->query("DELETE FROM article_caractval WHERE artid = '$artid'");
						$DB_site->query("DELETE FROM stocks WHERE artid = '$artid'");
						$DB_site->query("UPDATE stock SET nombre = '0' WHERE artid = '$artid'");
					}else{
						$DB_site->query("DELETE FROM bundle WHERE artid = '$artid'");
						$DB_site->query("DELETE FROM bundle_caractval WHERE bundleid NOT IN (SELECT bundleid FROM bundle)");
					}
				}
				if (in_array("5864", $modules) || in_array("5957", $modules) || $mode == "test_modules")
					$DB_site->query("UPDATE article SET numero_tarifaire_laposte = '" . securiserSql($_POST[numero_tarifaire_laposte]) . "' WHERE artid = '$artid'");
				
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					if ($nouvelarticle){
						$DB_site->query("INSERT INTO article_site(artid, siteid) VALUES ('$artid', '$site[siteid]')");
					}
					
					$existe_site_article = $DB_site->query_first("SELECT * FROM article 
																	INNER JOIN article_site USING(artid)
																	WHERE artid = '$artid'
																	AND siteid = '$site[siteid]'");
					if (!$existe_site_article[artid]){
						$DB_site->query("INSERT INTO article_site(artid, siteid) VALUES ('$artid', '$site[siteid]')");
					}
					
					$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE article.artid = '$artid' AND siteid = '$site[siteid]'");
					$article_historique_site = $DB_site->query_first("SELECT * FROM article_historique_prix_site WHERE artid = '$artid' AND siteid = '$site[siteid]' AND datefin = '0' ");
					if(!$article_historique_site[article_historique_prixid]){
						$DB_site->query("INSERT INTO article_historique_prix_site(artid, siteid, prix, prixpro, datesaisie, datedebut) VALUES ('$artid', '$site[siteid]', '${"prix".$site[siteid]}', '${"prixpro" . $site[siteid]}', '".time()."', '".time()."')");
					}else{
						if(${"prix".$site[siteid]} == ""){
							$nouveau_prix = 0;
						}else{
							$nouveau_prix = ${"prix".$site[siteid]};
						}
						if(${"prixpro" . $site[siteid]} == ""){
							$nouveau_prixpro = 0;
						}else{
							$nouveau_prixpro = ${"prixpro" . $site[siteid]};
						}
						if ($nouveau_prix != $article_historique_site[prix] || $nouveau_prixpro != $article_historique_site[prixpro]){
							$DB_site->query("UPDATE article_historique_prix_site SET datefin = '".time()."' WHERE article_historique_prixid = '$article_historique_site[article_historique_prixid]'");
							$DB_site->query("INSERT INTO article_historique_prix_site(artid, siteid, prix, prixpro, datedebut, datesaisie) VALUES ('$artid', '$site[siteid]', '$nouveau_prix', '$nouveau_prixpro', '".time()."', '" . time() . "')");
						}	
					}
					
					$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET prix = '" . securiserSql($_POST["prix$site[siteid]"]) . "', prixpro = '" . (${"prixpro" . $site[siteid]} ? securiserSql($_POST["prixpro$site[siteid]"]) : 0) . "',  prixpublic = '" . securiserSql($_POST["prixpublic$site[siteid]"]) . "', ecotaxe = '" . securiserSql($_POST["ecotaxe$site[siteid]"]) . "',
									delai = '" . securiserSql($_POST["delai$site[siteid]"]) . "', libelle = '" . securiserSql($_POST["libelle$site[siteid]"]) . "', titre2 = '" . securiserSql($_POST["titre2_$site[siteid]"]) . "', commandable = '" . ($_POST["commandable$site[siteid]"] ? 1 : 0) . "',
									activeV1 = '" . ($_POST["activeV1_$site[siteid]"] ? 1 : 0) . "', activeV2 = '" . ($_POST["activeV2_$site[siteid]"] ? 1 : 0) . "', eco_participation ='" . ($_POST[eco_participation] ? 1 : 0) . "'
									WHERE artid = '$artid' AND siteid = '$site[siteid]'");
					
					if (in_array("5872", $modules) || $mode == "test_modules")
						$DB_site->query("UPDATE article_site SET dateparution = '" . strtotime(str_replace('/', '-', ${"dateparution" . $site[siteid]})) . "', dateparutionfin = '" . strtotime(str_replace('/', '-', ${dateparutionfin . $site[siteid]})) . "' WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				}
				break;
			case "photos":
				$erreur = "";			
				if ($_FILES[imagepng][name]){
					$listeTypesAutorise = array("image/png");
					erreurUpload("imagepng", $listeTypesAutorise, 1048576);
					if (!$erreur){
						$path = $rootpath . "configurations/$host/images/articles/$artid.png";
						copier_image($path, "imagepng");
					}
				}
				$erreur = "";
				if ($_FILES[image][name]){
					$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif");
					erreurUpload("image", $listeTypesAutorise, 1048576);
					if (!$erreur){
						$type_fichier = pathinfo($_FILES[image][name], PATHINFO_EXTENSION);
						$type_fichier = ($type_fichier == "jpeg" ? "jpg" : $type_fichier);
						$DB_site->query("UPDATE article SET image = '$type_fichier' WHERE artid = '$artid'");
						$path = $rootpath . "configurations/$host/images/produits/$artid.$type_fichier";
						copier_image($path, "image");
						redimentionner_image_complet($path, $artid . "." . $type_fichier);
					}
				}
				if ($imageurl && @fopen($imageurl, 'r')){
					$type_fichier = pathinfo(basename($imageurl), PATHINFO_EXTENSION);
					$type_fichier = ($type_fichier == "jpeg" ? "jpg" : $type_fichier);
					$DB_site->query("UPDATE article SET image = '$type_fichier' WHERE artid = '$artid'");
					$path = $rootpath . "configurations/$host/images/produits/$artid.$type_fichier";
					copier_image_url($path, $imageurl, $type_fichier);
					redimentionner_image_complet($path, $artid . "." . $type_fichier);
				}
				$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET legende = '" . securiserSql($_POST[legende])."' WHERE artid = '$artid' AND siteid = '1'");
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites))
					$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET legende = '" . securiserSql($_POST["legende_$site[siteid]"])."' WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				
				foreach ($articlephoto as $key => $value){		
					if ($_FILES["image$value"][name]){
						$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif");
						erreurUpload("image$value", $listeTypesAutorise, 1048576);
						if (!$erreur){
							$type_fichier = pathinfo($_FILES["image$value"][name], PATHINFO_EXTENSION);
							$type_fichier = ($type_fichier == "jpeg" ? "jpg" : $type_fichier);
							$photo = $DB_site->query_first("SELECT * FROM articlephoto WHERE artid = '$artid' AND position = '$value'");
							if ($photo[articlephotoid] == ""){
								$DB_site->query("INSERT INTO articlephoto(artid, image, position) VALUES ('$artid', '$type_fichier', '$value')");
								$photo[articlephotoid] = $DB_site->insert_id();
								$DB_site->query("INSERT INTO articlephoto_site(articlephotoid, siteid) VALUES ('$photo[articlephotoid]', '1')");
							}else{
								$DB_site->query("UPDATE articlephoto SET image = '$type_fichier' WHERE articlephotoid = '$photo[articlephotoid]' AND position = '$value'");	
							}
							$DB_site->query("UPDATE articlephoto_site SET legende = '" . securiserSql($_POST["legende$value"])."' WHERE articlephotoid = '$photo[articlephotoid]' AND siteid = '1'");
							$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
							while ($site = $DB_site->fetch_array($sites)){
								$articlephotosite = $DB_site->query_first("SELECT * FROM articlephoto INNER JOIN articlephoto_site USING(articlephotoid) WHERE articlephotoid = '$photo[articlephotoid]' AND artid = '$artid' AND siteid = '$site[siteid]'");
								if ($articlephotosite[articlephotoid] == "")
									$DB_site->query("INSERT INTO articlephoto_site(articlephotoid, siteid) VALUES ('$photo[articlephotoid]', '$site[siteid]')");
								$DB_site->query("UPDATE articlephoto INNER JOIN articlephoto_site USING(articlephotoid) SET legende = '" . securiserSql($_POST["legende$value" . "_" . "$site[siteid]"])."' WHERE articlephotoid = '$photo[articlephotoid]' AND artid = '$artid' AND siteid = '$site[siteid]'");
							}
							$path = $rootpath . "configurations/$host/images/produits/$artid" . "_" . "$photo[articlephotoid].$type_fichier";
							copier_image($path, "image$value");
							redimentionner_image_complet($path, $artid . "_" . $photo[articlephotoid] . "." . $type_fichier);
						}
					}elseif (${"imageurl" . $value} && @fopen(${"imageurl" . $value}, 'r')){
						$type_fichier = pathinfo(basename(${"imageurl" . $value}), PATHINFO_EXTENSION);
						$type_fichier = ($type_fichier == "jpeg" ? "jpg" : $type_fichier);
						$photo = $DB_site->query_first("SELECT * FROM articlephoto WHERE artid = '$artid' AND position = '$value'");
						if ($photo[articlephotoid] == ""){
							$DB_site->query("INSERT INTO articlephoto(artid, image, position) VALUES ('$artid', '$type_fichier', '$value')");
							$photo[articlephotoid] = $DB_site->insert_id();
							$DB_site->query("INSERT INTO articlephoto_site(articlephotoid, siteid) VALUES ('$photo[articlephotoid]', '1')");
						}else{
							$DB_site->query("UPDATE articlephoto SET image = '$type_fichier' WHERE articlephotoid = '$photo[articlephotoid]' AND position = '$value'");	
						}
						$DB_site->query("UPDATE articlephoto_site SET legende = '" . securiserSql($_POST["legende$value"])."' WHERE articlephotoid = '$photo[articlephotoid]' AND siteid = '1'");
						$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
						while ($site = $DB_site->fetch_array($sites)){
							$articlephotosite = $DB_site->query_first("SELECT * FROM articlephoto INNER JOIN articlephoto_site USING(articlephotoid) WHERE articlephotoid = '$photo[articlephotoid]' AND artid = '$artid' AND siteid = '$site[siteid]'");
							if ($articlephotosite[articlephotoid] == "")
								$DB_site->query("INSERT INTO articlephoto_site(articlephotoid, siteid) VALUES ('$photo[articlephotoid]', '$site[siteid]')");
							$DB_site->query("UPDATE articlephoto INNER JOIN articlephoto_site USING(articlephotoid) SET legende = '" . securiserSql($_POST["legende$value" . "_" . "$site[siteid]"])."' WHERE articlephotoid = '$photo[articlephotoid]' AND artid = '$artid' AND siteid = '$site[siteid]'");
						}
						$path = $rootpath . "configurations/$host/images/produits/$artid" . "_" . "$photo[articlephotoid].$type_fichier";
						copier_image_url($path, ${"imageurl" . $value}, $type_fichier);
						redimentionner_image_complet($path, $artid . "_" . $photo[articlephotoid] . "." . $type_fichier);
					
					//Pas d'image ni d'url enovoy� => traitement des l�gendes des photos suppl�mentaires
					}else{
						$photo = $DB_site->query_first("SELECT * FROM articlephoto WHERE artid = '$artid' AND position = '$value'");
						/*if ($photo[articlephotoid] == ""){
							$DB_site->query("INSERT INTO articlephoto(artid, position) VALUES ('$artid', '$value')");
							$photo[articlephotoid] = $DB_site->insert_id();
							$DB_site->query("INSERT INTO articlephoto_site(articlephotoid, siteid) VALUES ('$photo[articlephotoid]', '1')");
						}*/
						$DB_site->query("UPDATE articlephoto_site SET legende = '".securiserSql($_POST["legende$value"])."' WHERE articlephotoid = '$photo[articlephotoid]' AND siteid = '1'");
	
						$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
						while ($site = $DB_site->fetch_array($sites)){
							/*$articlephotosite = $DB_site->query_first("SELECT * FROM articlephoto INNER JOIN articlephoto_site USING (articlephotoid) WHERE articlephotoid = '$photo[articlephotoid]' AND artid = '$artid' AND siteid = '$site[siteid]'");
							if ($articlephotosite[articlephotoid] == ""){
								$DB_site->query("INSERT INTO articlephoto_site(articlephotoid, siteid) VALUES ('$photo[articlephotoid]', '$site[siteid]')");
							}*/
							$DB_site->query("UPDATE articlephoto_site SET legende = '".securiserSql($_POST["legende$value"."_"."$site[siteid]"])."' WHERE articlephotoid = '$photo[articlephotoid]' AND siteid = '$site[siteid]'");
						}					
					}
				}
				break;
			case "vue3d":
				$erreur = "";
				if ($_FILES[image3D][name]){
					$listeTypesAutorise = array("application/x-shockwave-flash");
					erreurUpload("image3D", $listeTypesAutorise, 1048576);
					if (!$erreur){
						$type_fichier = pathinfo($_FILES[image3D][name], PATHINFO_EXTENSION);
						$DB_site->query("UPDATE article USING(artid) SET image3D = '$type_fichier' WHERE artid = '$artid'");
						$path = $rootpath . "configurations/$host/images/produits/3d/$artid.$type_fichier";
						copier_image($path, "image3D");
					}
				}
				if ($image3Durl && @fopen($image3Durl, 'r')){
					$type_fichier = pathinfo(basename($image3Durl), PATHINFO_EXTENSION);
					$DB_site->query("UPDATE article SET image3D = '$type_fichier' WHERE artid = '$artid'");
					$path = $rootpath . "configurations/$host/images/produits/3d/$artid.$type_fichier";
					copy($image3Durl, $path);
				}
				$largeur3D = ($largeur3D >= 0 ? $largeur3D : 0);
				$hauteur3D = ($hauteur3D >= 0 ? $hauteur3D : 0);
				$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET largeur3D = '" . securiserSql($largeur3D) . "', hauteur3D = '" . securiserSql($hauteur3D) . "' WHERE artid = '$artid'");
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					if ($_FILES["image3D$site[siteid]"][name]){
						$listeTypesAutorise = array("application/x-shockwave-flash");
						erreurUpload("image3D$site[siteid]", $listeTypesAutorise, 1048576);
						if (!$erreur){
							$type_fichier = pathinfo($_FILES["image3D$site[siteid]"][name], PATHINFO_EXTENSION);
							$DB_site->query("UPDATE article SET image3D = '$type_fichier' WHERE artid = '$artid' AND siteid = '$site[siteid]'");
							$path = $rootpath . "configurations/$host/images/produits/3d/$artid" . "_" . "$site[siteid].$type_fichier";
							copier_image($path, "image3D$site[siteid]");
						}
					}
					if (${"image3Durl" . $site[siteid]} && @fopen(${"image3Durl" . $site[siteid]}, 'r')){
						$type_fichier = pathinfo(basename(${"image3Durl" . $site[siteid]}), PATHINFO_EXTENSION);
						$DB_site->query("UPDATE article SET image3D = '$type_fichier' WHERE artid = '$artid' AND siteid = '$site[siteid]'");
						$path = $rootpath . "configurations/$host/images/produits/3d/$artid" . "_" . "$site[siteid].$type_fichier";
						copy(${"image3Durl" . $site[siteid]}, $path);
					}
					$largeur3D = ($largeur3D >= 0 ? $largeur3D : 0);
					$hauteur3D = ($hauteur3D >= 0 ? $hauteur3D : 0);
					$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET largeur3D = '" . securiserSql($_POST["largeur3D$site[siteid]"]) . "', hauteur3D = '" . securiserSql($_POST["largeur3D$site[siteid]"]) . "' WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				}
				break;
			case "notice":
				if (isset($formaction) and $formaction == "ajouter"){
					$DB_site->query("INSERT INTO article_notice(artid, noticeid) VALUES ('$artid', '$noticeid')");
				}
				if (isset($formaction) and $formaction == "supprimerassignees"){
					$DB_site->query("DELETE FROM article_notice WHERE noticeid = '$noticeid'");
				}
				if (isset($formaction) and $formaction == "supprimerexistantes"){
					$notice = $DB_site->query_first("SELECT * FROM notice WHERE noticeid = '$noticeid'");
					@unlink($rootpath . "configurations/$host/images/notices/$notice[noticeid].$notice[contenu]");
					$DB_site->query("DELETE FROM article_notice WHERE noticeid = '$noticeid'");
					$DB_site->query("DELETE FROM notice WHERE noticeid = '$noticeid'");
				}
				if (!isset($formaction) or $formaction == ""){
					$erreur = "";
					if ($_FILES[notice][name]){
						//$listeTypesAutorise = array("application/pdf");
						//erreurUpload("notice", $listeTypesAutorise, 1048576);
						if (!$erreur){
							$type_fichier = strtolower(pathinfo($_FILES[notice][name], PATHINFO_EXTENSION));
							$DB_site->query("INSERT INTO notice(contenu, description) VALUES ('$type_fichier', '" . securiserSql($_POST[description]) . "')");
							$noticeid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO article_notice(artid, noticeid) values ('$artid', '$noticeid')");
							$path = $rootpath . "configurations/$host/images/notices/$noticeid.$type_fichier";
							copier_image($path, "notice");
						}
					}
				}
				break;
			case "description":
				$description = str_replace('src="/userfiles', 'src=\"http://' . $host . '/userfiles', stripslashes($_POST[description]));
				$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET description = '" . addslashes($description) . "' WHERE artid = '$artid' AND siteid = '1'");
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					$descriptionsite = str_replace('src="/userfiles', 'src=\"http://' . $host . '/userfiles', stripslashes($_POST["description" . $site[siteid]]));
					$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET description = '" . addslashes($descriptionsite) . "' WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				}
				break;
			case "fichetechnique":
				$fichetechnique = str_replace('src="/userfiles', 'src=\"http://' . $host . '/userfiles', stripslashes($_POST[fichetechnique]));
				$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET fichetechnique = '" . addslashes($fichetechnique) . "' WHERE artid = '$artid' AND siteid = '1'");
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					$fichetechniquesite = str_replace('src="/userfiles', 'src=\"http://' . $host . '/userfiles', stripslashes($_POST["fichetechnique" . $site[siteid]]));
					$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET fichetechnique = '" . addslashes($fichetechniquesite) . "' WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				}
				break;
			case "notreavis":
				$notreavis = str_replace('src="/userfiles', 'src=\"http://' . $host . '/userfiles', stripslashes($_POST[notreavis]));
				$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET notreavis = '" . addslashes($notreavis) . "' WHERE artid = '$artid' AND siteid = '1'");
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					$notreavissite = str_replace('src="/userfiles', 'src=\"http://' . $host . '/userfiles', stripslashes($_POST["notreavis" . $site[siteid]]));
					$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET notreavis = '" . addslashes($notreavissite) . "' WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				}
				break;
			case "ensavoirplus":
				$ensavoirplus = str_replace('src="/userfiles', 'src=\"http://' . $host . '/userfiles', stripslashes($_POST[ensavoirplus]));
				$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET ensavoirplus = '" . addslashes($ensavoirplus) . "' WHERE artid = '$artid' AND siteid = '1'");
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					$ensavoirplussite = str_replace('src="/userfiles', 'src=\"http://' . $host . '/userfiles', stripslashes($_POST["ensavoirplus" . $site[siteid]]));
					$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET ensavoirplus = '" . addslashes($ensavoirplussite) . "' WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				}
				break;
			case "livraison":
				$DB_site->query("DELETE FROM article_moyen_livraison WHERE artid = '$artid'");
				if (sizeof($modelivraisonid) > 0){
					foreach ($modelivraisonid as $key => $value){
						$DB_site->query("INSERT INTO article_moyen_livraison(artid, modelivraisonid) VALUES ('$artid', '$value')");
					}
				}
				break;
			case "compositionlot":
				if (isset($formaction) and $formaction == "supprimer"){
					$DB_site->query("DELETE FROM bundle WHERE bundleid = '$bundleid'");
					$DB_site->query("DELETE FROM bundle_site WHERE bundleid = '$bundleid'");
				}
				if (!isset($formaction) or $formaction == ""){
					if (count($articlesTab)){
						foreach ($articlesTab as $key => $value){
							$DB_site->query("INSERT INTO bundle(artid, artid_bundle) VALUES ('$artid', '$value')");
							$bundleid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO bundle_site(bundleid, siteid, prix, prixpro, tauxchoisi, quantite)) VALUES ('$bundleid', '1', '0', '0', '1', '0')");
							$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
							while ($site = $DB_site->fetch_array($sites))
								$DB_site->query("INSERT INTO bundle_site(bundleid, siteid, prix, prixpro, tauxchoisi, quantite) VALUES ('$bundleid', '$site[siteid]', '0', '0', '1', '0')");
						}
					} 
					$bundles = $DB_site->query("SELECT * FROM bundle INNER JOIN bundle_site USING(bundleid) WHERE artid = '$artid' AND siteid = '1'");
					while ($bundle = $DB_site->fetch_array($bundles)){
						$DB_site->query("UPDATE bundle_site SET prix = '" . securiserSql($_POST["prix$bundle[bundleid]"]) . "',
										prixpro = '" . securiserSql($_POST["prixpro$bundle[bundleid]"]) . "',
										tauxchoisi = '" . securiserSql($_POST["tauxchoisi$bundle[bundleid]"]) . "',
										quantite = '" . securiserSql($_POST["quantite$bundle[bundleid]"]) . "' WHERE bundleid = '$bundle[bundleid]' AND siteid = '1'");
						$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
						while ($site = $DB_site->fetch_array($sites)){
							$DB_site->query("UPDATE bundle_site SET prix = '" . securiserSql($_POST["prix$bundle[bundleid]_$site[siteid]"]) . "',
											prixpro = '" . securiserSql($_POST["prixpro$bundle[bundleid]_$site[siteid]"]) . "',
											tauxchoisi = '" . securiserSql($_POST["tauxchoisi$bundle[bundleid]_$site[siteid]"]) . "'
											WHERE bundleid = '$bundle[bundleid]' AND siteid = '$site[siteid]'");
						}
					}
				}
				break;
			case "caracteristiques":
				if (in_array(5909,$modules) || $mode == "test_modules"){
					$commandable = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid'");
					$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET commandable = '0' WHERE artid = '$artid'");
					global $client;
					$passession = "oui";
					$backgroundLunch = 1;
					$liste_artid = $artid;
					include $_SERVER['DOCUMENT_ROOT'] . "admin/placedemarche_neteven.php";
					if ($commandable[commandable])
						$DB_site->query("UPDATE article INNER JOIN article_site USING(artid) SET commandable = '1' WHERE artid = '$artid'");
				}
				$DB_site->query("DELETE FROM article_caractval WHERE artid = '$artid'");
				if (sizeof($caractvalid) > 0){
					foreach ($caractvalid as $key => $value){
						$DB_site->query("INSERT INTO article_caractval(artid, caractvalid) VALUES ('$artid', '$value')");
					}
				}
				construireStockArticle($DB_site, $artid);
				break;
			case "photoscaracteristiques":
				if (isset($formaction) and $formaction == "supprimer"){
					$photo = $DB_site->query_first("SELECT * FROM article_caractval_photo INNER JOIN article_caractval_photo_site USING(articlecaractvalphotoid) WHERE articlecaractvalphotoid = '$articlecaractvalphotoid'");
					@unlink($rootpath . "configurations/$host/images/produits_caractvals/$photo[articlecaractvalphotoid].$photo[image]");
					$DB_site->query("DELETE FROM article_caractval_photo WHERE articlecaractvalphotoid = '$photo[articlecaractvalphotoid]'");
					$DB_site->query("DELETE FROM article_caractval_photo_site WHERE articlecaractvalphotoid = '$photo[articlecaractvalphotoid]'");
				}
				if (!isset($formaction) or $formaction == ""){
					foreach ($_FILES as $caractval => $image){
						$erreur = "";
						if ($image[name]){ 
							$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
							erreurUpload($caractval, $listeTypesAutorise, 1048576);
							if (!$erreur){
								$type_fichier = pathinfo($image[name], PATHINFO_EXTENSION);
								$type_fichier = ($type_fichier == "jpeg" ? "jpg" : $type_fichier);
								$caractvalid = explode("_", $caractval);
								$caractvalid = $caractvalid[1];
								$DB_site->query("INSERT INTO article_caractval_photo(artid, caractvalid) VALUES ('$artid', '$caractvalid')");
								$articlecaractvalphotoid = $DB_site->insert_id();
								$DB_site->query("INSERT INTO article_caractval_photo_site(articlecaractvalphotoid, siteid, image, legende) VALUES ('$articlecaractvalphotoid', '1', '$type_fichier', '" . securiserSql($_POST["legende_" . $caractvalid]) . "')");
								$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
								while ($site = $DB_site->fetch_array($sites))
									$DB_site->query("INSERT INTO article_caractval_photo_site(articlecaractvalphotoid, siteid, image, legende) VALUES ('$articlecaractvalphotoid', '$site[siteid]', '$type_fichier', '" . securiserSql($_POST["legende$site[siteid]_" . $caractvalid]) . "')");
								$path = $rootpath . "configurations/$host/images/produits_caractvals/$articlecaractvalphotoid.$type_fichier";
								copier_image($path, $caractval);
								redimentionner_image_completCaractval($path, $articlecaractvalphotoid . "." . $type_fichier);
							}
						}
					}
				}
				break;
				
			case "couleurs":
				$DB_site->query("DELETE FROM article_couleur_site WHERE artid = '$artid'");
				if(isset($_POST[couleurid])){
					foreach($_POST[couleurid] as $key => $couleurid){
						$sites = $DB_site->query("SELECT * FROM site");
						while($site = $DB_site->fetch_array($sites)){
							$DB_site->query("INSERT INTO article_couleur_site (artid, couleurid, modifprix, siteid) VALUES ('$artid','$couleurid','".${'modifprix_'.$couleurid.'_'.$site[siteid]}."','$site[siteid]')");
						}
					}
				}
				break;
			case "stocks":
				foreach ($_POST as $champ => $stocks){
					if (substr($champ,0,2) == "s-"){
						$libChamp = substr($champ, 2);
						$pro = substr($libChamp, 0, 19);
						echo $pro."<br>";
						foreach ($stocks as $stockid => $valeur) {
							if ($libChamp == "total"){
								if ($valeur)
									decrementerStock($DB_site, $artid, $stockid, $valeur);
							}elseif (substr($libChamp, 0, 19) == "differenceprixproht"){
								$siteid = substr($libChamp, 19, strlen($libChamp));
								$DB_site->query("UPDATE stocks_site SET differenceprixproht = '$valeur' WHERE stockid = '$stockid' AND siteid = '$siteid'");
							}elseif (substr($libChamp, 0, 14) == "differenceprix"){
								$siteid = substr($libChamp, 14, strlen($libChamp));
								$DB_site->query("UPDATE stocks_site SET differenceprix = '$valeur' WHERE stockid = '$stockid' AND siteid = '$siteid'");
							}elseif (substr($libChamp, 0, 14) != "differenceprix" && substr($libChamp, 0, 19) != "differenceprixproht"){
								$DB_site->query("UPDATE stocks INNER JOIN stocks_site USING(stockid) SET $libChamp = '$valeur' WHERE stockid = '$stockid'");
							}
						}
					}
					if (substr($champ,0,3) == "as-"){
						$libChamp = substr($champ, 3);
						if ($libChamp == "nombre"){
							if ($stocks)
								decrementerStock($DB_site, $artid, 0, $stocks);
						}elseif ($libChamp == "prixachat" || $libChamp == "prixachatmoyen"){
							$DB_site->query("UPDATE article SET $libChamp = '$stocks' WHERE artid = '$artid'");
						}else{
							$DB_site->query("UPDATE stock SET $libChamp = '$stocks' WHERE artid = '$artid'");
						}
					}
					
				}
				break;
			case "rueducommerce":
				$DB_site->query("DELETE FROM article_attribut WHERE artid = '$artid'");
				if (sizeof($attributs) > 0){
					foreach ($attributs as $key => $value){
						$DB_site->query("INSERT INTO article_attribut(artid, attributid, valeur) values ('$artid', '$key', '" . securiserSql($value) . "')");
					}
				}
				break;
			case "balisesmeta":
				$DB_site->query("UPDATE article INNER JOIN article_site USING(artid)
								SET ref_title = '" . securiserSql($_POST[ref_title]) . "',
								ref_description = '" . securiserSql($_POST[ref_description]) . "',
								ref_keywords = '" . securiserSql($_POST[ref_keywords]) . "' WHERE artid = '$artid' AND siteid = '1'");
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					$DB_site->query("UPDATE article INNER JOIN article_site USING(artid)
									SET ref_title = '" . securiserSql($_POST["ref_title$site[siteid]"]) . "',
									ref_description = '" . securiserSql($_POST["ref_description$site[siteid]"]) . "',
									ref_keywords = '" . securiserSql($_POST["ref_keywords$site[siteid]"]) . "' WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				}
				break;
			case "tags":
				$DB_site->query("DELETE FROM article_tag WHERE artid = '$artid'");
				if (sizeof($tagid) > 0){
					foreach ($tagid as $key => $value){
						$DB_site->query("INSERT INTO article_tag(artid, tagid) VALUES ('$artid', '$value')");
					}
				}
				break;
			case "articlesconseilles":
				if (isset($formaction) and $formaction == "supprimer"){
					$DB_site->query("DELETE FROM article_conseil WHERE id = '$id'");
				}
				if (!isset($formaction) or $formaction == ""){
					if (count($articlesTab)){
						foreach ($articlesTab as $key => $value){
							$articleconseil = $DB_site->query_first("SELECT MAX(position) position FROM article_conseil WHERE artid = '$artid'");
							$articleconseil[position] = ($articleconseil[position] ? $articleconseil[position] + 1 : 0);
							$DB_site->query("INSERT INTO article_conseil(artid, artid_conseille, position, modifiable) VALUES ('$artid', '$value', '$articleconseil[position]', '0')");
						}
					}
				}
				break;
			case "articlescomplementaires":
				if (isset($formaction) and $formaction == "supprimer"){
					$DB_site->query("DELETE FROM article_complement WHERE id = '$id'");
				}
				if (!isset($formaction) or $formaction == ""){
					if (count($articlesTab)){
						foreach ($articlesTab as $key => $value){
							$articlecomplement = $DB_site->query_first("SELECT MAX(position) position FROM article_complement WHERE artid = '$artid'");
							$articlecomplement[position] = ($articlecomplement[position] ? $articlecomplement[position] + 1 : 0);
							$DB_site->query("INSERT INTO article_complement(artid, artid_complement, position) VALUES ('$artid', '$value', '$articlecomplement[position]')");
						}
					}
				}
				break;
			case "affichagemultiple":
				$DB_site->query("DELETE FROM position WHERE artid = '$artid'");
				$affichage = json_decode($affichage);
				if (sizeof($affichage) > 0){
					foreach ($affichage as $key => $value){
						$DB_site->query("INSERT INTO position (artid, catid) VALUES ('$artid', '$value')");
					}
				}
				break;
			case "personnalisation":
				if (isset($formaction) and $formaction == "supprimer"){
					$DB_site->query("DELETE article_champ_valeur_site FROM article_champ_valeur INNER JOIN article_champ_valeur_site
									USING(articlechampvaleurid) WHERE articlechampid = '$articlechampid'");
					$DB_site->query("DELETE FROM article_champ_valeur WHERE articlechampid = '$articlechampid'");
					$DB_site->query("DELETE FROM article_champ_site WHERE articlechampid = '$articlechampid'");
					$DB_site->query("DELETE FROM article_champ WHERE articlechampid = '$articlechampid'");
				}
				if (isset($formaction) and $formaction == "modifier" and $_POST[nom]){
					$nouveauchamp = 0;
					if ($articlechampid == ""){
						$DB_site->query("INSERT INTO article_champ(articlechampid, artid) VALUES ('', '$artid')");
						$articlechampid = $DB_site->insert_id();
						$nouveauchamp = 1;
					}
					$champvaleurs = $DB_site->query("SELECT * FROM article_champ_valeur WHERE articlechampid = '$articlechampid'");
					while ($champvaleur = $DB_site->fetch_array($champvaleurs)){
						$DB_site->query("DELETE FROM article_champ_valeur_site WHERE articlechampvaleurid	= '$articlechampvaleurid'");
					}
					$DB_site->query("DELETE FROM article_champ_valeur WHERE articlechampid = '$articlechampid'");
					switch ($_POST[type]){
						case "1":
							$DB_site->query("INSERT INTO article_champ_valeur(articlechampvaleurid, articlechampid) VALUES ('', '$articlechampid')");
							$articlechampvaleurid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid) VALUES ('$articlechampvaleurid')");
							$cols = intval($_POST[cols]);
							$DB_site->query("UPDATE article_champ_valeur SET libelle = 'cols',
									valeur = '$cols' WHERE articlechampvaleurid = '$articlechampvaleurid'");
							$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '1',
									description = 'Largeur' WHERE articlechampvaleurid = '$articlechampvaleurid'");
							$DB_site->query("INSERT INTO article_champ_valeur(articlechampvaleurid, articlechampid) VALUES ('', '$articlechampid')");
							$articlechampvaleurid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid) VALUES ('$articlechampvaleurid')");
							$rows = intval($_POST[rows]);
							$DB_site->query("UPDATE article_champ_valeur SET libelle = 'rows',
									valeur = '$rows' WHERE articlechampvaleurid = '$articlechampvaleurid'");
							$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '1',
									description = 'Hauteur' WHERE articlechampvaleurid = '$articlechampvaleurid'");
							break;
						case "2":
							$i = 0;
							$name = "valueboutonsRadio" . $i;
							while ($_POST[$name]){
								$DB_site->query("INSERT INTO article_champ_valeur(articlechampvaleurid, articlechampid) VALUES ('', '$articlechampid')");
								$articlechampvaleurid = $DB_site->insert_id();
								$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid, siteid) VALUES ('$articlechampvaleurid', '1')");
								$DB_site->query("UPDATE article_champ_valeur SET libelle = 'value',
										valeur = '$i' WHERE articlechampvaleurid = '$articlechampvaleurid'");
								$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '1',
									description = '" . securiserSql($_POST[$name]) . "' WHERE articlechampvaleurid = '$articlechampvaleurid' AND siteid = '1'");
								$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
								while ($site = $DB_site->fetch_array($sites)){
									$name = "valueboutonsRadio" . $i . "site" . $site[siteid];
									$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid, siteid) VALUES ('$articlechampvaleurid', '$site[siteid]')");
									$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '$site[siteid]',
													description = '" . securiserSql($_POST[$name]) . "' WHERE articlechampvaleurid = '$articlechampvaleurid' AND siteid = '$site[siteid]'");
									$name = "valueboutonsRadio" . $i . "site" . $site[siteid];
								}
								++$i;
								$name = "valueboutonsRadio" . $i;
							}
							break;
						case "3":
							$i = 0;
							$name = "valuecasesACocher" . $i;
							while ($_POST[$name]){
								$DB_site->query("INSERT INTO article_champ_valeur(articlechampvaleurid, articlechampid) VALUES ('', '$articlechampid')");
								$articlechampvaleurid = $DB_site->insert_id();
								$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid, siteid) VALUES ('$articlechampvaleurid', '1')");
								$DB_site->query("UPDATE article_champ_valeur SET libelle = 'value',
												valeur = '$i' WHERE articlechampvaleurid = '$articlechampvaleurid'");
								$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '1',
												description = '" . securiserSql($_POST[$name]) . "' WHERE articlechampvaleurid = '$articlechampvaleurid' AND siteid = '1'");
								$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
								while ($site = $DB_site->fetch_array($sites)){
									$name = "valuecasesACocher" . $i . "site" . $site[siteid];
									$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid, siteid) VALUES ('$articlechampvaleurid', '$site[siteid]')");
									$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '$site[siteid]',
													description = '" . securiserSql($_POST[$name]) . "' WHERE articlechampvaleurid = '$articlechampvaleurid' AND siteid = '$site[siteid]'");
									$name = "valuecasesACocher" . $i . "site" . $site[siteid];
								}
								++$i;
								$name = "valuecasesACocher" . $i;
							}
							break;
						case "4":
							$DB_site->query("INSERT INTO article_champ_valeur(articlechampvaleurid, articlechampid) VALUES ('', '$articlechampid')");
							$articlechampvaleurid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid) VALUES ('$articlechampvaleurid')");
							$maxlength = intval($_POST[maxlength]);
							$DB_site->query("UPDATE article_champ_valeur SET libelle = 'maxlength',
											valeur = '$maxlength' WHERE articlechampvaleurid = '$articlechampvaleurid'");
							$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '1',
											description = 'Longueur' WHERE articlechampvaleurid = '$articlechampvaleurid'");
							$DB_site->query("INSERT INTO article_champ_valeur(articlechampvaleurid, articlechampid) VALUES ('', '$articlechampid')");
							$articlechampvaleurid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid) VALUES ('$articlechampvaleurid')");
							$size = intval($_POST[size]);
							$DB_site->query("UPDATE article_champ_valeur SET libelle = 'size',
											valeur = '$size' WHERE articlechampvaleurid = '$articlechampvaleurid'");
							$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '1',
											description = 'Taille' WHERE articlechampvaleurid = '$articlechampvaleurid'");
							break;
						case "5":
							$i = 0;
							$name = "valuelisteASelectionUnique" . $i;
							while ($_POST[$name]){
								$DB_site->query("INSERT INTO article_champ_valeur(articlechampvaleurid, articlechampid) VALUES ('', '$articlechampid')");
								$articlechampvaleurid = $DB_site->insert_id();
								$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid, siteid) VALUES ('$articlechampvaleurid', '1')");
								$DB_site->query("UPDATE article_champ_valeur SET libelle = 'value',
												valeur = '$i' WHERE articlechampvaleurid = '$articlechampvaleurid'");
								$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '1',
												description = '" . securiserSql($_POST[$name]) . "' WHERE articlechampvaleurid = '$articlechampvaleurid' AND siteid = '1'");
								$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
								while ($site = $DB_site->fetch_array($sites)){
									$name = "valuelisteASelectionUnique" . $i . "site" . $site[siteid];
									$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid, siteid) VALUES ('$articlechampvaleurid', '$site[siteid]')");
									$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '$site[siteid]',
													description = '" . securiserSql($_POST[$name]) . "' WHERE articlechampvaleurid = '$articlechampvaleurid' AND siteid = '$site[siteid]'");
									$name = "valuelisteASelectionUnique" . $i . "site" . $site[siteid];
								}
								++$i;
								$name = "valuelisteASelectionUnique" . $i;
							}
							break;
						case "6":
							$i = 0;
							$name = "valuelisteASelectionsMultiples" . $i;
							while ($_POST[$name]){
								$DB_site->query("INSERT INTO article_champ_valeur(articlechampvaleurid, articlechampid) VALUES ('', '$articlechampid')");
								$articlechampvaleurid = $DB_site->insert_id();
								$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid, siteid) VALUES ('$articlechampvaleurid', '1')");
								$DB_site->query("UPDATE article_champ_valeur SET libelle = 'value',
												valeur = '$i' WHERE articlechampvaleurid = '$articlechampvaleurid'");
								$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '1',
												description = '" . securiserSql($_POST[$name]) . "' WHERE articlechampvaleurid = '$articlechampvaleurid' AND siteid = '1'");
								$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
								while ($site = $DB_site->fetch_array($sites)){
									$name = "valuelisteASelectionsMultiples" . $i . "site" . $site[siteid];
									$DB_site->query("INSERT INTO article_champ_valeur_site(articlechampvaleurid, siteid) VALUES ('$articlechampvaleurid', '$site[siteid]')");
									$DB_site->query("UPDATE article_champ_valeur_site SET siteid = '$site[siteid]',
													description = '" . securiserSql($_POST[$name]) . "' WHERE articlechampvaleurid = '$articlechampvaleurid' AND siteid = '$site[siteid]'");
									$name = "valuelisteASelectionsMultiples" . $i . "site" . $site[siteid];
								}
								++$i;
								$name = "valuelisteASelectionsMultiples" . $i;
							}
							break;
					}
					$obligatoire = ($_POST[obligatoire] ? 1 : 0);
					$sites = $DB_site->query("SELECT * FROM site");
					while ($site = $DB_site->fetch_array($sites)){
						if ($nouveauchamp)
							$DB_site->query("INSERT INTO article_champ_site(articlechampid, siteid) VALUES ('$articlechampid', '$site[siteid]')");
						$existe_site_champ_article = $DB_site->query_first("SELECT * FROM article_champ INNER JOIN article_champ_site USING(articlechampid)
																			WHERE articlechampid = '$articlechampid' AND siteid = '$site[siteid]'");
						if ($existe_site_champ_article[articlechampid] == "")
							$DB_site->query("INSERT INTO article_champ_site(articlechampid, siteid) VALUES ('$articlechampid','$site[siteid]')");
						$libellesite = "libelle$site[siteid]";
						$prixpersosite = "prixperso$site[siteid]";
						$prixpersoprosite = "prixpersopro$site[siteid]";
						$DB_site->query("UPDATE article_champ_site SET libelle = '" . addslashes($_POST[$libellesite]) . "', 
										prixperso = '" . addslashes($_POST[$prixpersosite]) . "',
										prixpersopro = '" . addslashes($_POST[$prixpersoprosite]) . "'
										WHERE articlechampid = '$articlechampid' AND siteid = '$site[siteid]'");
					}
					$DB_site->query("UPDATE article_champ SET nom = '" . addslashes($_POST[nom]) . "', type = '" . addslashes($_POST[type]) . "',
									obligatoire = '$obligatoire' WHERE articlechampid = '$articlechampid'");
				}
				break;
			case "piecesjointes":
				if (isset($formaction) and $formaction == "ajouter"){
					$DB_site->query("INSERT INTO article_piece(artid, pieceid) VALUES ('$artid', '$pieceid')");
				}
				if (isset($formaction) and $formaction == "supprimerassignees"){
					$DB_site->query("DELETE FROM article_piece WHERE pieceid = '$pieceid'");
				}
				if (isset($formaction) and $formaction == "supprimerexistantes"){
					$piece = $DB_site->query_first("SELECT * FROM piece WHERE pieceid = '$pieceid'");
					@unlink($rootpath . "configurations/$host/images/player/pieces_jointes/$piece[pieceid].$piece[contenu]");
					$DB_site->query("DELETE FROM article_piece WHERE pieceid = '$pieceid'");
					$DB_site->query("DELETE FROM piece WHERE pieceid = '$pieceid'");
				}
				if (!isset($formaction) or $formaction == ""){
					if ($_FILES[piece][name]){
						$contenu = strtolower(pathinfo($_FILES[piece][name], PATHINFO_EXTENSION));
						$description = ($_POST[description] ? securiserSql($_POST[description]) : securiserSql(pathinfo($_FILES[piece][name], PATHINFO_FILENAME)));
						$DB_site->query("INSERT INTO piece(pieceid, format, contenu, description) VALUES ('', '$contenu', '$contenu', '$description')");
						$pieceid = $DB_site->insert_id();
						$DB_site->query("INSERT INTO article_piece(artid, pieceid) values ('$artid', '$pieceid')");
						$path = $rootpath . "configurations/$host/images/player/pieces_jointes/$pieceid.$type_fichier";
						copier_image($path, "piece");
					}
				}
				break;
			case "votesetcommentaires":
				if (isset($formaction) and $formaction == "supprimer"){
					$DB_site->query("DELETE FROM articlevote WHERE pieceid = '$articlevoteid'");
				}
				break;
			case "promotion":
				$sites = $DB_site->query("SELECT * FROM site");
				while($site = $DB_site->fetch_array($sites)){
					if(isset(${"pourcentage".$site[siteid]})){
						$pourcentage = ${"pourcentage".$site[siteid]};
						$pctpromo = $pourcentage;
					}else{
						$pctpromo = "";
					}
					
					if(isset(${"montant".$site[siteid]})){
						$montant = ${"montant".$site[siteid]};
					}
					
					if($montant != "" || $pourcentage != ""){
						$datedebut = ${"datedebut".$site[siteid]};
						$datefin =  ${"datefin".$site[siteid]};
						if($datedebut != "" && $datefin != ""){
							$date_debut = "0";
							$date_fin = "0";
							$timedebut = ${"timedebut".$site[siteid]};
							$timefin =  ${"timefin".$site[siteid]};
							if ($datedebut != "") {
								list($jour, $mois, $annee) = explode('/', $datedebut);
								list($heure, $min, $sec) = explode(':', $timedebut);
								$date_debut = mktime($heure, $min, $sec, $mois, $jour, $annee);
							}
							if ($datefin != "") {
								list($jour, $mois, $annee) = explode('/', $datefin);
								list($heure, $min, $sec) = explode(':', $timefin);
								$date_fin = mktime($heure, $min, $sec, $mois, $jour, $annee);
							}
							$date_saisie = time();
							$prix_article = $DB_site->query_first("SELECT prix FROM article_site WHERE artid = '$artid' AND siteid = '$site[siteid]'");
								
							if($pctpromo == ""){
								if($prix_article == "0" || $prix_article[prix] == ""){
									$pctpromo = 0;
								}else{
									$nouveau_prix = floatval($prix_article[prix])-floatval($montant);
									$pctpromo = ( ( floatval($prix_article[prix]) - $nouveau_prix ) / floatval($prix_article[prix]) ) * 100;
								}
							}
							$DB_site->query("INSERT INTO article_promo_site (artid, siteid, pctpromo, datedebut, datefin, datesaisie) VALUES ('$artid','$site[siteid]','$pctpromo','$date_debut','$date_fin','$date_saisie')");
							$promoid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO article_historique_prix_site (siteid, artid, prix, pctpromo, datesaisie, datedebut, datefin, promoid) VALUES ('$site[siteid]','$artid','$prix_article[prix]','$pctpromo','$date_saisie','$date_debut','$date_fin','$promoid')");
						}
					}
				}
				break;
			case "remisesgros":
				if (isset($formaction) and $formaction == "supprimer"){
					$DB_site->query("DELETE FROM remise_site WHERE remiseid = '$remiseid'");
					$DB_site->query("DELETE FROM remisearticle WHERE remiseid = '$remiseid'");
					$DB_site->query("DELETE FROM remise WHERE remiseid = '$remiseid'");
				}
				if (!isset($formaction) or $formaction == ""){
					if ($valeur > 0 && $pctremise > 0){
						$remise = $DB_site->query_first("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisearticle USING(remiseid) WHERE artid = '$artid' AND valeur = '" . securiserSql($valeur) . "' AND pctremise = '" . securiserSql($pctremise) . "' AND siteid = '1'");
						if (!$remise[remiseid]){
							$DB_site->query("INSERT INTO remise(remiseid, typeremise, actif, cumul) VALUES ('', '" . ($typeremise ? 1 : 0) . "', '1', '1')");
							$remiseid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO remisearticle(artid, remiseid) VALUES ('$artid', '$remiseid')");
							$DB_site->query("INSERT INTO remise_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '1', '" . securiserSql($valeur) . "', '" . securiserSql($pctremise) . "')");
						}
					}
					$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
					while ($site = $DB_site->fetch_array($sites)){
						$valeursite = ${"valeur" . $site[siteid]};
						$pctremisesite = ${"pctremise" . $site[siteid]};
						if ($valeursite > 0 && $pctremisesite > 0){
							$remise = $DB_site->query_first("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisearticle USING(remiseid) WHERE artid = '$artid' AND valeur = '" . securiserSql($valeursite) . "' AND pctremise = '" . securiserSql($pctremisesite) . "' AND siteid = '$site[siteid]'");
							if (!$remise[remiseid]){
								$DB_site->query("INSERT INTO remise(remiseid, typeremise, actif, cumul) VALUES ('', '" . (${"typeremise" . $site[siteid]} ? 1 : 0) . "', '1', '1')");
								$remiseid = $DB_site->insert_id();
								$DB_site->query("INSERT INTO remisearticle(artid, remiseid) VALUES ('$artid', '$remiseid')");
								$DB_site->query("INSERT INTO remise_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '$site[siteid]', '" . securiserSql($valeursite) . "', '" . securiserSql($pctremisesite) . "')");
							}
						}
					}
				}
				break;
			case "remisesgrospro":
				if (isset($formaction) and $formaction == "supprimer"){
					$DB_site->query("DELETE FROM remisepro_site WHERE remiseid = '$remiseid'");
					$DB_site->query("DELETE FROM remiseproarticle WHERE remiseid = '$remiseid'");
					$DB_site->query("DELETE FROM remisepro WHERE remiseid = '$remiseid'");
				}
				if (!isset($formaction) or $formaction == ""){
					if ($valeur > 0 && $pctremise > 0){
						$remise = $DB_site->query_first("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseproarticle USING(remiseid) WHERE artid = '$artid' AND valeur = '" . securiserSql($valeur) . "' AND pctremise = '" . securiserSql($pctremise) . "' AND siteid = '1'");
						if (!$remise[remiseid]){
							$DB_site->query("INSERT INTO remisepro(remiseid, typeremise, actif, cumul) VALUES ('', '" . ($typeremise ? 1 : 0) . "', '1', '1')");
							$remiseid = $DB_site->insert_id();
							$DB_site->query("INSERT INTO remiseproarticle(artid, remiseid) VALUES ('$artid', '$remiseid')");
							$DB_site->query("INSERT INTO remisepro_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '1', '" . securiserSql($valeur) . "', '" . securiserSql($pctremise) . "')");
						}
					}
					$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
					while ($site = $DB_site->fetch_array($sites)){
						$valeursite = ${"valeur" . $site[siteid]};
						$pctremisesite = ${"pctremise" . $site[siteid]};
						if ($valeursite > 0 && $pctremisesite > 0){
							$remise = $DB_site->query_first("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseproarticle USING(remiseid) WHERE artid = '$artid' AND valeur = '" . securiserSql($valeursite) . "' AND pctremise = '" . securiserSql($pctremisesite) . "' AND siteid = '$site[siteid]'");
							if (!$remise[remiseid]){
								$DB_site->query("INSERT INTO remisepro(remiseid, typeremise, actif, cumul) VALUES ('', '" . (${"typeremise" . $site[siteid]} ? 1 : 0) . "', '1', '1')");
								$remiseid = $DB_site->insert_id();
								$DB_site->query("INSERT INTO remiseproarticle(artid, remiseid) VALUES ('$artid', '$remiseid')");
								$DB_site->query("INSERT INTO remisepro_site(remiseid, siteid, valeur, pctremise) VALUES ('$remiseid', '$site[siteid]', '" . securiserSql($valeursite) . "', '" . securiserSql($pctremisesite) . "')");
							}
						}
					}
				}
				break;
			case "fichiers":
				if (isset($formaction) and $formaction == "ajouter"){
					if (pathinfo($fichierid, PATHINFO_EXTENSION)){
						$extension = strtolower(pathinfo($fichierid, PATHINFO_EXTENSION));
						$libelle = strtolower(pathinfo($fichierid, PATHINFO_FILENAME));
						$DB_site->query("INSERT INTO fichier(fichierid, extension) VALUES ('', '$extension')");
						$fichierid = $DB_site->insert_id();
						$DB_site->query("INSERT INTO fichier_site(fichierid, libelle, siteid) VALUES ('$fichierid', '$libelle', '1')");
						$DB_site->query("INSERT INTO article_fichier(artid, fichierid) values ('$artid', '$fichierid')");
						copy($rootpath . "configurations/$host/ftp/$libelle.$extension", $rootpath . "configurations/$host/articlefichier/$fichierid.$extension");
						@unlink($rootpath . "configurations/$host/ftp/$libelle.$extension");
					}else{
						$DB_site->query("INSERT INTO article_fichier(artid, fichierid) VALUES ('$artid', '$fichierid')");
					}
				}
				if (isset($formaction) and $formaction == "supprimerassignes"){
					$DB_site->query("DELETE FROM article_fichier WHERE fichierid = '$fichierid'");
				}
				if (isset($formaction) and $formaction == "supprimerexistants"){
					$fichier = $DB_site->query_first("SELECT * FROM fichier WHERE fichierid = '$fichierid'");
					@unlink($rootpath . "configurations/$host/articlefichier/$fichier[fichierid].$fichier[extension]");
					$DB_site->query("DELETE FROM article_fichier WHERE fichierid = '$fichierid'");
					$DB_site->query("DELETE FROM fichier_site WHERE fichierid = '$fichierid'");
					$DB_site->query("DELETE FROM fichier WHERE fichierid = '$fichierid'");
				}
				if (!isset($formaction) or $formaction == ""){
					if ($_FILES[fichier][name]){
						$_POST[extension] = strtolower(pathinfo($_FILES[fichier][name], PATHINFO_EXTENSION));
						$DB_site->query("INSERT INTO fichier(fichierid, extension) VALUES ('', '" . securiserSql($_POST[extension]) . "')");
						$fichierid = $DB_site->insert_id();
						$DB_site->query("INSERT INTO fichier_site(fichierid, libelle, siteid) VALUES ('$fichierid', '" . securiserSql($_POST[libelle]) . "', '1')");
						$DB_site->query("INSERT INTO article_fichier(artid, fichierid) values ('$artid', '$fichierid')");
						$path = $rootpath . "configurations/$host/articlefichier/$fichierid.$_POST[extension]";
						copier_image($path, "fichier");
					}
				}
				break;
		}
	 	header("location: produits.php?action=modifier&form=$next&artid=$artid");
	}else{
		header('location: produits.php?erreurdroits=1');	
	}
};

if (isset($action) and $action == "modifier"){
	$form = (isset($form) && $form ? $form : 'infos');
	${'active' . $form} = 'active';
	
	$article = $DB_site->query_first("SELECT *, a.image as image FROM article a INNER JOIN article_site USING(artid) WHERE artid = '$artid'");
	if ($article[artid]){
		$lignefacture = $DB_site->query_first("SELECT SUM(qte) total FROM lignefacture INNER JOIN facture USING(factureid) WHERE etatid IN(1,5) AND artid = '$article[artid]'");
		$lignefacture[total] = ($lignefacture[total] ? $lignefacture[total] : 0);
		$statarticle = $DB_site->query_first("SELECT SUM(vues) total FROM stat_article WHERE artid = '$artid'");
		$statarticle[total] = ($statarticle[total] ? $statarticle[total] : 0);
		$article[datedernierajoutpanier] = ($article[datedernierajoutpanier] ? date('d/m/Y', $article[datedernierajoutpanier]) : $multilangue[aucun]);
		$article[datedernierachat] = ($article[datedernierachat] ? date('d/m/Y', $article[datedernierachat]) : $multilangue[aucun]);
		eval(charge_template($langue, $referencepage, "ModificationInformations"));
		if (in_array("5909", $modules) || $mode == "test_modules")
			eval(charge_template($langue, $referencepage, "ModificationExporterVersNeteven"));
		eval(charge_template($langue, $referencepage, "ModificationIdentifiantInterne"));
	}	
	if (in_array("5859", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationVue3D"));
	if (in_array("5945", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationNotice"));
	if (in_array("5846", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationFicheTechnique"));
	if (in_array("5847", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationNotreAvis"));
	if (in_array("5915", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationEnSavoirPlus"));
	if (in_array("5867", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationMoyensDeLivraison"));
	if (isset($artid) && $artid != "" && $article[isbundle] == "1" && (in_array("5901",$modules) || $mode == "test_modules")){
		eval(charge_template($langue, $referencepage, "ModificationCompositionLot"));
	}else{
		eval(charge_template($langue, $referencepage, "ModificationCaracteristiques"));
		if (in_array("5941", $modules) || $mode == "test_modules")
			eval(charge_template($langue, $referencepage, "ModificationPhotosCaracteristiques"));
		
		if (!$article[stock_illimite] && (in_array("4", $modules) || $mode == "test_modules")){
			eval(charge_template($langue, $referencepage, "ModificationStocks"));
		}else{
			eval(charge_template($langue, $referencepage, "ModificationInfosCaracteristiques"));
		}
	}
	if (isset($artid) && $artid && ((in_array("5869", $modules) && in_array("5806", $modules)) || $mode == "test_modules")){
		$rueducommerce = $DB_site->query_first("SELECT * FROM rueducommerce WHERE artid = '$artid'");
		if ($rueducommerce[MCID])
			eval(charge_template($langue, $referencepage, "ModificationAttributsRdc"));
	}
	if (in_array("134", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationTags"));
	if (in_array("117", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationArticlesConseilles"));
	if (in_array("5807", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationArticleServiesComplementaires"));
	if (in_array("105", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationAffichageMultiple"));
	if (in_array("113", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationPersonnalisation"));
	if (in_array("116", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationPiecesJointes"));
	if (in_array("18", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationVotesEtCommentaires"));
	if (in_array("5", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationPromotion"));
	if (in_array("3", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationRemisesGros"));
	if ((in_array("3", $modules) && in_array("122", $modules)) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationRemisesGrosPro"));
	if (in_array("5888", $modules) || $mode == "test_modules")
		eval(charge_template($langue, $referencepage, "ModificationFichiers"));
	
	
	$pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = '57'");
	switch ($article[tauxchoisi]){
		case 0:
			$tauxtvaaappliquer_depart=0;
			break;
		case 1:
			$tauxtvaaappliquer_depart=$pays[TVAtauxnormal];
			break;
		case 2:
			$tauxtvaaappliquer_depart=$pays[TVAtauxreduit];
			break;
		case 3:
			$tauxtvaaappliquer_depart=$pays[TVAtauxintermediaire];
			break;
		default:
			$tauxtvaaappliquer_depart=0;
			break;
	}
	
	switch ($form){
		case 'infos':
			if (in_array("5983", $modules) || $mode == "test_modules")
				eval(charge_template($langue, $referencepage, "ModificationASIN"));
			if (in_array("5864", $modules) || in_array("5957",$modules) || $mode == "test_modules")
				eval(charge_template($langue, $referencepage, "ModificationNumeroTarifaireLaposte"));
			
			$articlemodules = $DB_site->query("SELECT * FROM articlemodule");
			while ($articlemodule = $DB_site->fetch_array($articlemodules)){
				$selected = ($articlemodule[articlemoduleid] == $article[articlemoduleid] ? 'selected' : '');
				eval(charge_template($langue, $referencepage, "ModificationArticleModuleBit"));
			}			
			
			$etiquettes = $DB_site->query("SELECT * FROM etiquette");
			while ($etiquette = $DB_site->fetch_array($etiquettes)){
				$selected = ($etiquette[etiquetteid] == $article[etiquetteid] ? 'selected' : '');
				eval(charge_template($langue, $referencepage, "ModificationEtiquetteBit"));
			}
			if (in_array("5908", $modules) || $mode == "test_modules")
				eval(charge_template($langue, $referencepage, "ModificationEtiquette"));
			
			if (in_array("5806", $modules) || $mode == "test_modules"){
				if (in_array("5869", $modules) || $mode == "test_modules"){
					$rueducommerce = $DB_site->query_first("SELECT * FROM rueducommerce WHERE artid = '$artid'");
					eval(charge_template($langue, $referencepage, "ModificationMCID"));
				}
				if (in_array("5870", $modules) || $mode == "test_modules"){
					$pixmania = $DB_site->query_first("SELECT * FROM pixmania WHERE artid = '$artid'");
					eval(charge_template($langue, $referencepage, "ModificationPixmania"));
				}
			}
			if (in_array("5922", $modules) || $mode == "test_modules"){
				$googleshopping = $DB_site->query_first("SELECT * FROM googleshopping WHERE artid = '$artid'");
				$googleshopping[attributid] = ($googleshopping[attributid] ? $googleshopping[attributid] : 0);
				categorie_googleshopping($DB_site, $googleshopping[attributid]);
				eval(charge_template($langue, $referencepage, "ModificationGoogleShopping"));
			}
			if (in_array("5936", $modules) || $mode == "test_modules"){
				$kelpackid = $DB_site->query_first("SELECT * FROM article_kelpack WHERE artid = '$artid'");
				$kelpackcategories = $DB_site->query("SELECT * FROM kelpack_categories ORDER BY kelpackid");
				while ($kelpackcategorie = $DB_site->fetch_array($kelpackcategories)){
					$selected = ($kelpackcategorie[kelpackid] == $kelpackid[kelpackid] ? 'selected' : '');
					eval(charge_template($langue, $referencepage, "ModificationKelpackBit"));
				}
				eval(charge_template($langue, $referencepage, "ModificationKelpack"));
			}
			if (in_array("5955", $modules) || $mode == "test_modules"){
				$niveaux = $DB_site->query("SELECT DISTINCT(niveau) FROM trouversoncadeau_categories ORDER BY niveau");
				while ($niveau = $DB_site->fetch_array($niveaux)){
					$TemplateProduitsModificationTrouverSonCadeauBitBit = "";
					$trouversoncadeau = $DB_site->query_first("SELECT * FROM trouversoncadeau_articles INNER JOIN trouversoncadeau_categories USING(trouversoncadeau_catid)
															  WHERE artid = '$artid' AND niveau = '$niveau[niveau]'");
					$categories = $DB_site->query("SELECT * FROM trouversoncadeau_categories INNER JOIN trouversoncadeau_categories_site USING(trouversoncadeau_catid) WHERE niveau = '$niveau[niveau]' ORDER BY libelle");
					while ($categorie = $DB_site->fetch_array($categories)){
						$selected = ($categorie[trouversoncadeau_catid] == $trouversoncadeau[trouversoncadeau_catid] ? 'selected' : '');
						eval(charge_template($langue, $referencepage, "ModificationTrouverSonCadeauBitBit"));
					}
					eval(charge_template($langue, $referencepage, "ModificationTrouverSonCadeauBit"));
				}
				eval(charge_template($langue, $referencepage, "ModificationTrouverSonCadeau"));
			}
			if (in_array("5954", $modules) || $mode == "test_modules"){
				$nextag = $DB_site->query_first("SELECT * FROM nextag WHERE artid = '$artid'");
				$nextag[catnextagid] = ($nextag[catnextagid] ? $nextag[catnextagid] : 0);
				categorie_nextag($DB_site, $nextag[catnextagid]);
				eval(charge_template($langue, $referencepage, "ModificationNextag"));
			}
			if (in_array("110", $modules) || $mode == "test_modules"){
				$fianets = $DB_site->query("SELECT * FROM fianet_categorie ORDER BY legende");
				while ($fianet = $DB_site->fetch_array($fianets)){
					$selected = ($fianet[fianet_categorieid] == $article[fianet_categorieid] ? 'selected' : '');
					eval(charge_template($langue, $referencepage, "ModificationFianetBit"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFianet"));
			}
			if (in_array("5872", $modules) || $mode == "test_modules"){
				$article[dateparution] = ($article[dateparution] ? date('d/m/Y H:i', $article[dateparution]) : '');
				$article[dateparutionfin] = ($article[dateparutionfin] ? date('d/m/Y H:i', $article[dateparutionfin]) : '');
				eval(charge_template($langue, $referencepage, "ModificationVenteFlash"));
			}
			if (in_array("5888", $modules) || $mode == "test_modules"){
				$checkedimmateriel = ($article[immateriel] ? 'checked' : '');
				eval(charge_template($langue, $referencepage, "ModificationProduitImmateriel"));
			}
			$marques = $DB_site->query("SELECT * FROM article_marque INNER JOIN marque_site USING(marqueid) WHERE artid = '$artid' AND siteid = '1'");
			while ($marque = $DB_site->fetch_array($marques))
				eval(charge_template($langue, $referencepage, "ModificationMarques"));
			$fournisseur = $DB_site->query_first("SELECT * FROM fournisseur WHERE fournisseurid = '$article[fournisseurid]'");
			if ($fournisseur[fournisseurid])
				eval(charge_template($langue, $referencepage, "ModificationFournisseur"));
			$payss = $DB_site->query("SELECT * FROM pays ORDER BY libelle");
			while ($pays = $DB_site->fetch_array($payss)){
				$selected = ($pays[paysid] == $article[pays_origine] ? 'selected' : '');
				eval(charge_template($langue, $referencepage, "ModificationPays"));
			}
			if (in_array("5807", $modules) || in_array("5961", $modules) || $mode == "test_modules"){
				$selected1 = ($article[typearticle] == "1" ? 'selected' : '');
				if (in_array("5807", $modules) || $mode == "test_modules"){
					$selected0 = ($article[typearticle] == "0" ? 'selected' : '');
					eval(charge_template($langue, $referencepage, "ModificationTypeArticleService"));
				}
				if (in_array("5961", $modules) || $mode == "test_modules"){
					$selected2 = ($article[typearticle] == "2" ? 'selected' : '');
					eval(charge_template($langue, $referencepage, "ModificationTypeArticleChequeCadeau"));
				}
				eval(charge_template($langue, $referencepage, "ModificationTypeArticle"));
			}
			if (in_array("5901", $modules) || $mode == "test_modules"){
				$checkedisbundle = ($article[isbundle] ? 'checked' : '');
				eval(charge_template($langue, $referencepage, "ModificationArticleEstLot"));
			}
			if (in_array("5980", $modules) || $mode == "test_modules"){
				$checkednonmecanisable = ($article[non_mecanisable] ? 'checked' : '');
				$tarifs = $DB_site->query("SELECT * FROM non_mecanisable_tarif INNER JOIN non_mecanisable_tarif_site USING(tarifid) WHERE siteid = '1' ORDER BY prix");
				while ($tarif = $DB_site->fetch_array($tarifs)){
					$selected = ($tarif[tarifid] == $article[tarifid] ? 'selected' : '');
					eval(charge_template($langue, $referencepage, "ModificationNonMecanisableBit"));
				}
				eval(charge_template($langue, $referencepage, "ModificationNonMecanisable"));
			}
			if (in_array("5950", $modules) || $mode == "test_modules")
				eval(charge_template($langue, $referencepage, "ModificationDimensions"));
			$pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = '57'");
			$selected0 = '';
			$selected1 = '';
			$selected2 = '';
			$selected3 = '';
			switch ($article[tauxchoisi]){
				case 0:
					$tauxtvaaappliquer_depart=0;
					$selected0 = 'selected';
					break;
				case 1:				
					$tauxtvaaappliquer_depart=$pays[TVAtauxnormal];	
					$selected1 = 'selected';
					break;
				case 2:				
					$tauxtvaaappliquer_depart=$pays[TVAtauxreduit];	
					$selected2 = 'selected';
					break;
				case 3:	
					$tauxtvaaappliquer_depart=$pays[TVAtauxintermediaire];				
					$selected3 = 'selected';
					break;
				default:
					$tauxtvaaappliquer_depart=0;
					$tva = 0;
					$selected1 = 'selected';
					break;
			}
			if (in_array("122", $modules) || $mode == "test_modules")
				eval(charge_template($langue, $referencepage, "ModificationPrixPro"));
			$checkedecopart = ($article[eco_participation] ? 'checked' : '');
			if (in_array("5937", $modules) || $mode == "test_modules"){
				$checkedprixaumetre = ($article[prixaumetre] ? 'checked' : '');
				eval(charge_template($langue, $referencepage, "ModificationVenduAuMetre"));
			}
			$checkedV1 = ($article[activeV1] || !$artid ? 'checked' : '');
			$checkedV2 = ($article[activeV2] || !$artid ? 'checked' : '');
			$checkedcommandable = ($article[commandable] || !$artid ? 'checked' : '');
			$checkedappliquer_remise = ($article[etiquette_R] ? 'checked' : '');
			
			if (in_array("4", $modules) || $mode == "test_modules"){
				$checkedstockillimite = ($article[stock_illimite] ? 'checked' : '');
				eval(charge_template($langue, $referencepage, "ModificationStockIllimite"));
			}
			if (in_array("5956", $modules) || $mode == "test_modules"){
				$checkedpart = ($article[produit_part] || !$artid ? 'checked' : '');
				$checkedpro = ($article[produit_pro] || !$artid ? 'checked' : '');
				eval(charge_template($langue, $referencepage, "ModificationArticleVisible"));
			}
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			while ($site = $DB_site->fetch_array($sites)){
				$TemplateProduitsModificationSiteVenteFlash = "";
				$TemplateProduitsModificationSitePrixPro = "";
				$devise_site_actuel=$tabsites[$site[siteid]][devise_symbole];
				$articlesite = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				if (in_array("5872", $modules) || $mode == "test_modules"){
					$articlesite[dateparution] = ($articlesite[dateparution] ? date('d/m/Y H:m', $articlesite[dateparution]) : '');
					$articlesite[dateparutionfin] = ($articlesite[dateparutionfin] ? date('d/m/Y H:m', $articlesite[dateparutionfin]) : '');
					eval(charge_template($langue, $referencepage, "ModificationSiteVenteFlash"));
				}
				if (in_array("122", $modules) || $mode == "test_modules")
					eval(charge_template($langue, $referencepage, "ModificationSitePrixPro"));
				$checkedecopartsite = ($articlesite[eco_participation] ? 'checked' : '');
				$checkedV1site = ($articlesite[activeV1] || !$artid ? 'checked' : '');
				$checkedV2site = ($articlesite[activeV2] || !$artid ? 'checked' : '');
				$checkedcommandablesite = ($articlesite[commandable] || !$artid ? 'checked' : '');
				eval(charge_template($langue, $referencepage, "ModificationFormulaireInformationsSite"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireInformations"));
			break;
		case 'photos':
			$article[image] = ($article[image] ? 'http://' . $host . '/br-' . url_rewrite($article[libelle]) . '-' . $article[artid] . '.' . $article[image].'?date='.$timenow : '');
			$i = 1;
			$articlephotos = $DB_site->query("SELECT * FROM articlephoto INNER JOIN articlephoto_site USING(articlephotoid) WHERE artid = '$artid' AND siteid = '1' ORDER BY position");
			while (($articlephoto = $DB_site->fetch_array($articlephotos)) || $i <= $params[nb_images_supplementaires]){
				$articlephoto[image] = ($articlephoto[image] ? 'http://' . $host . '/br-' . url_rewrite($article[libelle]) . '-' . $article[artid] . '_' . $articlephoto[articlephotoid] . '.' . $articlephoto[image].'?date='.$timenow : '');
				eval(charge_template($langue, $referencepage, "ModificationFormulairePhotosSupplementaires"));
				++$i;
			}
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			while ($site = $DB_site->fetch_array($sites)){
				$TemplateProduitsModificationFormulairePhotosSiteBit ="";
				$articlesite = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				$i = 1;
				$articlephotosites = $DB_site->query("SELECT * FROM articlephoto INNER JOIN articlephoto_site USING(articlephotoid) WHERE artid = '$artid' AND siteid = '$site[siteid]' ORDER BY position");
				while (($articlephotosite = $DB_site->fetch_array($articlephotosites)) || $i <= $params[nb_images_supplementaires]){
					$legendesite = 'legende' . $i .'_' . $site[siteid];
					eval(charge_template($langue, $referencepage, "ModificationFormulairePhotosSiteBit"));
					++$i;
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulairePhotosSite"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulairePhotos"));
			break;
		case 'vue3d':
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			while ($site = $DB_site->fetch_array($sites)){
				$articlesite = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireVue3DSite"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireVue3D"));
			break;
		case 'notice':
			$notices = $DB_site->query("SELECT * FROM notice INNER JOIN article_notice USING(noticeid) WHERE artid = '$artid'");
			if ($DB_site->num_rows($notices)){
				while ($notice = $DB_site->fetch_array($notices))
					eval(charge_template($langue, $referencepage, "ModificationFormulaireNoticeAssigneesBit"));
				eval(charge_template($langue, $referencepage, "ModificationFormulaireNoticeAssignees"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulaireNoticeAssigneesVide"));
			}
			$notices = $DB_site->query("SELECT * FROM notice WHERE noticeid NOT IN(SELECT noticeid FROM article_notice WHERE artid = '$artid')");
			if ($DB_site->num_rows($notices)){
				while ($notice = $DB_site->fetch_array($notices))
					eval(charge_template($langue, $referencepage, "ModificationFormulaireNoticeExistantesBit"));
				eval(charge_template($langue, $referencepage, "ModificationFormulaireNoticeExistantes"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulaireNoticeExistantesVide"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireNotice"));
			break;
		case 'description':
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			while ($site = $DB_site->fetch_array($sites)){
				$articlesite = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireDescriptionSite"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireDescription"));
			break;
		case 'fichetechnique':
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			while ($site = $DB_site->fetch_array($sites)){
				$articlesite = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireFicheTechniqueSite"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireFicheTechnique"));
			break;
		case 'notreavis':
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			while ($site = $DB_site->fetch_array($sites)){
				$articlesite = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireNotreAvisSite"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireNotreAvis"));
			break;
		case 'ensavoirplus':
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			while ($site = $DB_site->fetch_array($sites)){
				$articlesite = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireEnSavoirPlusSite"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireEnSavoirPlus"));
			break;
		case 'livraison':
			$moyens = $DB_site->query("SELECT * FROM mode_livraison INNER JOIN mode_livraison_site USING(modelivraisonid) WHERE siteid = '1' AND (activeV1 = '1' OR activeV2 = '1' " . (in_array("5932",$modules) ? "OR activeV1M = '1' OR activeV2M = '1'" : "") . ") ORDER BY position");
			while ($moyen = $DB_site->fetch_array($moyens)){
				$articlemoyen = $DB_site->query_first("SELECT * FROM article_moyen_livraison WHERE artid = '$artid' AND modelivraisonid = '$moyen[modelivraisonid]'");
				$textsuccess = ($articlemoyen[modelivraisonid] ? 'text-success' : '');
				$checked = ($articlemoyen[modelivraisonid] ? 'checked' : '');
				$versions = '(' . ($moyen[activeV1] ? ' V1 ' : '') . ($moyen[activeV2] ? ' V2 ' : '') . ($moyen[activeV1M] ? ' V1 ' . $multilangue[mobile] . ' ' : '') . ($moyen[activeV2M] ? ' V2 ' . $multilangue[mobile] . ' ' : '') . ')';
				eval(charge_template($langue, $referencepage, "ModificationFormulaireMoyensDeLivraisonBit"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireMoyensDeLivraison"));
			break;
		case 'compositionlot':
			$bundles = $DB_site->query("SELECT * FROM bundle INNER JOIN bundle_site USING(bundleid) WHERE artid = '$artid' AND siteid = '1'");
			while ($bundle = $DB_site->fetch_array($bundles)){
				$bundlearticle = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$bundle[artid_bundle]' AND siteid = '1'");
				$bundle[tauxchoisi] = (in_array($bundle[tauxchoisi], array(0, 1, 2, 3)) ? $bundle[tauxchoisi] : 1);
				$selected0 = '';
				$selected1 = '';
				$selected2 = '';
				$selected3 = '';
				${'selected' . $bundle[tauxchoisi]} = 'selected';
				eval(charge_template($langue, $referencepage, "ModificationFormulaireCompositionLotBit"));
			}
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			while ($site = $DB_site->fetch_array($sites)){
				$bundlesites = $DB_site->query("SELECT * FROM bundle INNER JOIN bundle_site USING(bundleid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				if ($DB_site->num_rows($bundlesites)){
					while ($bundlesite = $DB_site->fetch_array($bundlesites)){
						$bundlearticle = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$bundlesite[artid_bundle]' AND siteid = '1'");
						$bundlesite[tauxchoisi] = (in_array($bundlesite[tauxchoisi], array(0, 1, 2, 3)) ? $bundlesite[tauxchoisi] : 1);
						$selected0 = '';
						$selected1 = '';
						$selected2 = '';
						$selected3 = '';
						${'selected' . $bundlesite[tauxchoisi]} = 'selected';
						eval(charge_template($langue, $referencepage, "ModificationFormulaireCompositionLotSiteBit"));
					}
					eval(charge_template($langue, $referencepage, "ModificationFormulaireCompositionLotSite"));
				}
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireCompositionLot"));
			break;
		case 'caracteristiques':
			$caracteristiques = $DB_site->query("SELECT * FROM caracteristique INNER JOIN caracteristique_site USING(caractid) WHERE siteid = '1' ORDER BY position");
			$caractcochee = false;
			while ($caracteristique = $DB_site->fetch_array($caracteristiques)){
				$caractexiste = false;
				$TemplateProduitsModificationFormulaireCaracteristiquesValeurs =  "";
				$TemplateProduitsModificationFormulaireCaracteristiquesCocheesValeurs =  "";
				$caracteristiquevaleurs = $DB_site->query("SELECT * FROM caracteristiquevaleur INNER JOIN caracteristiquevaleur_site USING(caractvalid) WHERE caractid = '$caracteristique[caractid]' AND siteid = '1' ORDER BY position");
				while ($caracteristiquevaleur = $DB_site->fetch_array($caracteristiquevaleurs)){
					$articlecaracteristiquevaleur = $DB_site->query_first("SELECT * FROM article_caractval WHERE artid = '$artid' AND caractvalid = '$caracteristiquevaleur[caractvalid]'");
					if($articlecaracteristiquevaleur[caractvalid]){
						$caractexiste = true;
						$textsucess = 'text-success';
						eval(charge_template($langue, $referencepage, "ModificationFormulaireCaracteristiquesCocheesValeurs"));
					}else{
						$textsucess = '';
						eval(charge_template($langue, $referencepage, "ModificationFormulaireCaracteristiquesValeurs"));
					}
				}
				if($caractexiste){
					$caractcochee = true;
					eval(charge_template($langue, $referencepage, "ModificationFormulaireCaracteristiquesCocheesListe"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireCaracteristiquesBit"));
			}
			if($caractcochee){
				eval(charge_template($langue, $referencepage, "ModificationFormulaireCaracteristiquesCochees"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireCaracteristiques"));
			break;
		case 'photoscaracteristiques':
			$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
			$caracteristiques = $DB_site->query("SELECT * FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) INNER JOIN caracteristiquevaleur_site USING(caractvalid) WHERE artid = '$artid' AND siteid = '1' ORDER BY position");
			while ($caracteristique = $DB_site->fetch_array($caracteristiques)){
				$TemplateProduitsModificationFormulairePhotosCaracteristiquesSite = "";
				$TemplateProduitsModificationFormulairePhotosCaracteristiquesPhoto = "";
				$TemplateProduitsModificationFormulairePhotosCaracteristiquesPhotoBit = "";
				$TemplateProduitsModificationFormulairePhotosCaracteristiquesPhotoVide = "";
				$caracteristiquephotos = $DB_site->query("SELECT * FROM article_caractval_photo INNER JOIN article_caractval_photo_site USING(articlecaractvalphotoid) WHERE artid = '$artid' AND caractvalid = '$caracteristique[caractvalid]' AND siteid = '1' ORDER BY position");
				if ($DB_site->num_rows($caracteristiquephotos)){
					while ($caracteristiquephoto = $DB_site->fetch_array($caracteristiquephotos))
						eval(charge_template($langue, $referencepage, "ModificationFormulairePhotosCaracteristiquesPhotoBit"));
					eval(charge_template($langue, $referencepage, "ModificationFormulairePhotosCaracteristiquesPhoto"));
				}else{
					eval(charge_template($langue, $referencepage, "ModificationFormulairePhotosCaracteristiquesPhotoVide"));
				}
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites))
					eval(charge_template($langue, $referencepage, "ModificationFormulairePhotosCaracteristiquesSite"));
				eval(charge_template($langue, $referencepage, "ModificationFormulairePhotosCaracteristiquesBit"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulairePhotosCaracteristiques"));
			break;
			
		case 'couleurs':
			$groupes = $DB_site->query("SELECT * FROM couleur_groupe");
			while($groupe = $DB_site->fetch_array($groupes)){
				$TemplateProduitsModificationFormulaireCouleursListeBit = "";
				$TemplateProduitsModificationFormulaireCouleursModifPrixBit = "";
				$couleurs_groupe = $DB_site->query("SELECT * FROM couleur INNER JOIN couleur_site USING (couleurid) WHERE couleurgroupeid = '$groupe[couleurgroupeid]' AND siteid = '1' ");
				while($couleur_groupe = $DB_site->fetch_array($couleurs_groupe)){
					$TemplateProduitsModificationFormulaireCouleursModifPrixBit = "";
					$existe_article_couleur = $DB_site->query("SELECT * FROM article_couleur_site WHERE artid = '$artid' AND couleurid = '$couleur_groupe[couleurid]'");
					$tout_cocher = true;
					if($DB_site->num_rows($existe_article_couleur)){
						$checkedcouleur = "checked='checked'";	
					}else{
						$checkedcouleur = "";
						$tout_cocher = false;
					}
					$sites = $DB_site->query("SELECT * FROM site");
					while($site = $DB_site->fetch_array($sites)){
						$devise_site_actuel = $tabsites[$site[siteid]][devise_complete];
						$modif_prix = $DB_site->query_first("SELECT modifprix FROM article_couleur_site WHERE artid = '$artid' AND couleurid = '$couleur_groupe[couleurid]' AND siteid = '$site[siteid]'");
						if($modif_prix[modifprix]){
							$modif_prix = $modif_prix[modifprix];
						}else{
							$modif_prix = 0;
						}
						eval(charge_template($langue, $referencepage, "ModificationFormulaireCouleursModifPrixBit"));
					}
					eval(charge_template($langue, $referencepage, "ModificationFormulaireCouleursListeBit"));
				}
				if($tout_cocher){
					$display_tout_cocher = "style='display:none'";
					$display_tout_decocher = "";
				}else{
					$display_tout_cocher = "";
					$display_tout_decocher = "style='display:none'";
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireCouleursGroupeBit"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireCouleurs"));
				
			break;
		case 'stocks':
			if ((in_array("4", $modules) || $mode == "test_modules") && !$article[stock_illimite]){
				$lignefacture = $DB_site->query_first("SELECT SUM(qte) qte FROM lignefacture INNER JOIN facture USING(factureid) WHERE artid = '$artid'
													  AND dateexpedition = '0000-00-00' AND datedecrementation != '0000-00-00'
													  AND dateincrementation = '0000-00-00' AND facture.deleted = '0'
													  AND datefacture > '" . date("Y-m-d", strtotime('-20 days')) . "'");

				if ($lignefacture[qte])
					eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksReserve"));
			}	
			
			$stock_reserve = ($lignefacture[qte] ? $lignefacture[qte] : 0);
			$stock_reel_total += retournerStockArticle($DB_site, $artid);
			
			
			$caracteristiques = $DB_site->query("SELECT DISTINCT(caracteristique.caractid), caracteristique_site.libelle FROM caracteristique 
												INNER JOIN caracteristique_site USING(caractid) INNER JOIN caracteristiquevaleur USING(caractid) 
												INNER JOIN article_caractval USING(caractvalid) 
												WHERE artid = '$artid' AND siteid = '1' ORDER BY caracteristique.position");
			while ($caracteristique = $DB_site->fetch_array($caracteristiques))
				eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksCaracteristique"));
			if ($DB_site->num_rows($caracteristiques))
				$lignesstock = $DB_site->query("SELECT * FROM stocks INNER JOIN stocks_site USING(stockid) WHERE artid = '$artid' AND siteid = '1' ORDER BY stockid");
			if (in_array("5983", $modules) || $mode == "test_modules")
				eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksASIN"));
			if ((in_array("4", $modules) || $mode == "test_modules") && !$article[stock_illimite])
				eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksTitre"));
			if ($DB_site->num_rows($caracteristiques) && $DB_site->num_rows($lignesstock) > 1){
				$sites = $DB_site->query("SELECT * FROM site");
				while($site = $DB_site->fetch_array($sites)){
					if (in_array("122", $modules) || $mode == "test_modules")
						eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksCaracteristiquePrixProSite"));
					eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksCaracteristiquePrixSite"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksCaracteristiquePrix"));
			}
			$pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = '57'");
			switch ($article[tauxchoisi]){
				case 0:
					$tva = 0;
					break;
				case 1:
					$tva = $pays[TVAtauxnormal];
					break;
				case 2:
					$tva = $pays[TVAtauxreduit];
					break;
				case 3:
					$tva = $pays[TVAtauxintermediaire];
					break;
				default:
					$tva = 0;
					break;
			}
			if ($DB_site->num_rows($caracteristiques)){				
				while ($lignestock = $DB_site->fetch_array($lignesstock)){
					$TemplateProduitsModificationFormulaireStocksBitCaracteristique = "";
					$caracteristiquevaleurs = $DB_site->query("SELECT * FROM caracteristiquevaleur INNER JOIN caracteristiquevaleur_site USING(caractvalid) INNER JOIN caracteristique USING(caractid) INNER JOIN stocks_caractval USING(caractvalid) WHERE stockid = '$lignestock[stockid]' AND siteid='1' ORDER BY caracteristique.position");
					while ($caracteristiquevaleur = $DB_site->fetch_array($caracteristiquevaleurs)){
						$marge .= " AND lignefactureid IN (select lignefactureid from lignefacturecaracteristique where caractvalid = '$caracteristiquevaleur[caractvalid]')";
						eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitCaracteristique"));
					}
					$where = "";
					$join = "";
					$i = 1;
					$caracts = $DB_site->query("SELECT caractvalid FROM stocks_caractval WHERE stockid = '$lignestock[stockid]'");
					while ($caract = $DB_site->fetch_array($caracts)){
						$join .= " INNER JOIN lignefacturecaracteristique lfc$i USING(lignefactureid) ";
						$where .= " AND lfc$i.caractvalid = '$caract[caractvalid]' ";
						++$i;
					}
					$lignefacture = $DB_site->query_first("SELECT SUM(qte) qte FROM lignefacture INNER JOIN facture USING(factureid) $join WHERE artid = '$artid' $where
														  AND dateexpedition = '0000-00-00' AND datedecrementation != '0000-00-00'
														  AND dateincrementation = '0000-00-00' AND facture.deleted = '0'
														  AND datefacture > '" . date("Y-m-d", strtotime('-20 days')) . "'");
					
					$lignefacture[qte] = ($lignefacture[qte] ? $lignefacture[qte] : 0);
					
					$TemplateProduitsModificationFormulaireStocksBitLigneStock = "";
					$TemplateProduitsModificationFormulaireStocksBitLigneStockASIN = "";
					$TemplateProduitsModificationFormulaireStocksBitLigneStockVide = "";
					$TemplateProduitsModificationFormulaireStocksBitLigneStockVideASIN = "";
					if ($DB_site->num_rows($lignesstock) > 1){
						if (in_array("5983", $modules) || $mode == "test_modules")
							eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitLigneStockASIN"));
						eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitLigneStock"));
					}else{
						if (in_array("5983", $modules) || $mode == "test_modules")
							eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitLigneStockVideASIN"));
						eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitLigneStockVide"));
					}
					$TemplateProduitsModificationFormulaireStocksStocks = "";
					if ((in_array("4", $modules) || $mode == "test_modules") && !$article[stock_illimite])
						eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksStocks"));
					$TemplateProduitsModificationFormulaireStocksBitLigneStockDelaiLivraison = "";
					$TemplateProduitsModificationFormulaireStocksBitLigneStockDelaiLivraisonVide = "";
					if ($DB_site->num_rows($lignesstock) > 1)
						eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitLigneStockDelaiLivraison"));
					else
						eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitLigneStockDelaiLivraisonVide"));
					$TemplateProduitsModificationFormulaireStocksBitPrixSite = "";
					$TemplateProduitsModificationFormulaireStocksBitPrixProSite = "";
					$TemplateProduitsModificationFormulaireStocksBitPrix = "";
					if ($DB_site->num_rows($lignesstock) > 1){
						$sites = $DB_site->query("SELECT * FROM site");
						while($site = $DB_site->fetch_array($sites)){
							$devise_actuelle = $tabsites[$site[siteid]][devise_complete];
							$article_site = $DB_site->query_first("SELECT * FROM article_site WHERE artid = '$article[artid]' AND siteid = '$site[siteid]'");
							$lignestock_site = $DB_site->query_first("SELECT * FROM stocks_site WHERE stockid = '$lignestock[stockid]' AND siteid = '$site[siteid]'");
							$modifprix = $article_site[prix] + $lignestock_site[differenceprix];
							$modifprixpro = $article_site[prixpro] + $lignestock_site[differenceprixproht];
							$modifpoids = $article[poids] + $lignestock[differencepoids];
							if (in_array("122", $modules) || $mode == "test_modules")
								eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitPrixProSite"));
							eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitPrixSite"));
						}
						eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitPrix"));
					}
					$marge = (($article[prix] + $lignestock[differenceprix]) * round(2, (100 / (100 + $tva)))) - $lignestock[prixachat];
					$marge = formaterPrix($marge);
					$TemplateProduitsModificationFormulaireStocksBitMargePro = "";
					if (in_array("122", $modules) || $mode == "test_modules"){
						$margepro = (($article[prixpro] + $lignestock[differenceprixproht]) * round(2, (100 / (100 + $tva)))) - $lignestock[prixachat];
						$margepro = formaterPrix($margepro);
						eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitMargePro"));
					}
					eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksCaracteristiquesBit"));
				}
			}else{
				$stock = $DB_site->query_first("SELECT * FROM stock WHERE artid = '$artid'");
				if (in_array("5983", $modules) || $mode == "test_modules")
					eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitASIN"));
				if ((in_array("4", $modules) || $mode == "test_modules") && !$article[stock_illimite])
					eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitStocks"));
				$marge = ($article[prix] * round(2, (100 / (100 + $tva)))) - $article[prixachat];
				$marge = formaterPrix($marge);
				if (in_array("122", $modules) || $mode == "test_modules"){
					$margepro = $article[prixpro] - $lignestock[prixachat];
					$margepro = formaterPrix($margepro);
					eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBitMargePro"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksBit"));
			}
			$factures = array('0');
			$historiques = $DB_site->query("SELECT * FROM stock_historique WHERE artid = '$artid' ORDER BY dateline DESC");
			if ($DB_site->num_rows($historiques)){
				while ($historique = $DB_site->fetch_array($historiques)){
					$historique[dateline] = date("d/m/Y", $historique[dateline]);
					if (!$historique[factureid]){
						$facture = $DB_site->query_first("SELECT * FROM lignefacture INNER JOIN facture USING(factureid)
														 WHERE artid = '$historique[artid]' AND qte = '" . ($historique[delta] * -1) . "'
														 AND factureid IN (SELECT factureid FROM facture WHERE datedecrementation = '$datefacture'
														 AND factureid NOT IN (" . implode(',', $factures) . ") ORDER BY factureid DESC)");
						if ($facture[factureid] && !in_array($facture[factureid], $factures))
							$factures[] = $facture[factureid];
						$historique[factureid] = $facture[factureid];
					}
					if ($historique[stockid]){
						$caractvals = $DB_site->query("SELECT * FROM caracteristiquevaleur INNER JOIN caracteristiquevaleur_site USING(caractvalid) INNER JOIN stocks_caractval USING(caractvalid) WHERE stockid = '$historique[stockid]' AND siteid = '1'");
						while ($caractval = $DB_site->fetch_array($caractvals))
							$historique[label] .= stripslashes($caractval[libelle]) . " ";
					}
					$textsuccess = ($historique[delta] >= 0 ? 'text-success bold' : 'text-danger bold');
					$historique[delta] = ($historique[delta] >= 0 ? '+ ' . $historique[delta] : '- ' . abs($historique[delta])); 
					eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksHistoriqueBit"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksHistorique"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulaireStocksHistoriqueVide"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireStocks"));
			break;
		case 'rueducommerce':
			$rueducommerce = $DB_site->query_first("SELECT * FROM rueducommerce WHERE artid = '$artid'");
			$attributs = $DB_site->query("SELECT * FROM rueducommerce_attribut WHERE MCID = '$rueducommerce[MCID]' ORDER BY attributid");
			if ($DB_site->num_rows($attributs) > 0){
				while ($attribut = $DB_site->fetch_array($attributs)){
					$attributarticle = $DB_site->query_first("SELECT valeur FROM article_attribut WHERE artid = '$artid' AND attributid = '$attribut[attributid]'");
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRueDuCommerceBit"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRueDuCommerceMCID"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRueDuCommerceVide"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireRueDuCommerce"));
			break;
		case 'balisesmeta':
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			while ($site = $DB_site->fetch_array($sites)){
				$articlesite = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireBalisesMetaSite"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireBalisesMeta"));
			break;
		case 'tags':
			$tags = $DB_site->query("SELECT * FROM tags WHERE siteid = '1'");
			while ($tag = $DB_site->fetch_array($tags)){
				$articletag = $DB_site->query_first("SELECT * FROM article_tag WHERE artid = '$artid' AND tagid = '$tag[tagid]'");
				$textsuccess = ($articletag[tagid] ? 'text-success' : '');
				$checked = ($articletag[tagid] ? 'checked' : '');
				eval(charge_template($langue, $referencepage, "ModificationFormulaireTagsBit"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireTags"));
			break;
		case 'articlesconseilles':
			//if ($article[catid]){
				$articleconseils = $DB_site->query("SELECT * FROM article_conseil WHERE artid = '$artid' ORDER BY position");
				if ($DB_site->num_rows($articleconseils) > 0){
					while ($articleconseil = $DB_site->fetch_array($articleconseils)){
						$articleconseille = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$articleconseil[artid_conseille]' AND siteid = '1'");
						eval(charge_template($langue, $referencepage, "ModificationFormulaireArticlesConseillesCatidTableBit"));
					}
					eval(charge_template($langue, $referencepage, "ModificationFormulaireArticlesConseillesCatidTable"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireArticlesConseillesCatid"));
			/*}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulaireArticlesConseillesRacine"));
			}*/
			eval(charge_template($langue, $referencepage, "ModificationFormulaireArticlesConseilles"));
			break;
		case 'articlescomplementaires':
			//if ($article[catid]){
				$articlecomplements = $DB_site->query("SELECT * FROM article_complement WHERE artid = '$artid' ORDER BY position");
				if ($DB_site->num_rows($articlecomplements) > 0){
					while ($articlecomplement = $DB_site->fetch_array($articlecomplements)){
						$articlecomplementaire = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$articlecomplement[artid_complement]' AND siteid = '1'");
						eval(charge_template($langue, $referencepage, "ModificationFormulaireArticlesComplementairesCatidTableBit"));
					}
					eval(charge_template($langue, $referencepage, "ModificationFormulaireArticlesComplementairesCatidTable"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireArticlesComplementairesCatid"));
			/*}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulaireArticlesComplementairesRacine"));
			}*/
			eval(charge_template($langue, $referencepage, "ModificationFormulaireArticlesComplementaires"));
			break;
		case 'affichagemultiple':
			//if ($article[catid]){
				eval(charge_template($langue, $referencepage, "ModificationFormulaireAffichageMultipleCatid"));
			/*}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulaireAffichageMultipleRacine"));
			}*/
			eval(charge_template($langue, $referencepage, "ModificationFormulaireAffichageMultiple"));
			break;
		case 'personnalisation':
			if (isset($formaction) and $formaction == "modifier"){
				$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
				if ($articlechampid != ""){
					$champ = $DB_site->query_first("SELECT * FROM article_champ INNER JOIN article_champ_site
													USING(articlechampid) WHERE articlechampid = '$articlechampid' AND siteid = '1'");
					$checked = ($champ[obligatoire] == 1 ? "checked" : "");
				}else{
					$checked = "checked";
					$champ[type] = "1";
				}
				if (in_array("122", $modules) || $mode == "test_modules")
					eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationChampDefautBitPro"));
				$largeur = "40";
				$hauteur = "6";
				$longueur = "30";
				$taille = "100";
				$types = $DB_site->query("SELECT * FROM article_type_champ");
				while ($type = $DB_site->fetch_array($types)){
					if ($champ[type] == $type[articletypechampid]){
						$selected = "selected";
						$displayboiteDeTexte = ($champ[type] == "1" ? "display" : "none");
						$displayboutonsRadio = ($champ[type] == "2" ? "display" : "none");
						$displaycasesACocher = ($champ[type] == "3" ? "display" : "none");
						$displaychampTexte = ($champ[type] == "4" ? "display" : "none");
						$displaylisteASelectionUnique = ($champ[type] == "5" ? "display" : "none");
						$displaylisteASelectionsMultiples = ($champ[type] == "6" ? "display" : "none");
						$displayinsertionDeFichier = ($champ[type] == "7" ? "display" : "none");
						$tabid = array("2" => "boutonsRadio", "3" => "casesACocher", "5" => "listeASelectionUnique", "6" => "listeASelectionsMultiples");
						if (in_array($champ[type], array("2", "3", "5", "6"))){
							$name = $tabid[$champ[type]];
							$i = 1;
							$values = $DB_site->query("SELECT * FROM article_champ_valeur
														WHERE libelle = 'value' AND articlechampid = '$articlechampid'");
							$description = $DB_site->query_first("SELECT * FROM article_champ_valeur
																INNER JOIN article_champ_valeur_site USING(articlechampvaleurid)
																WHERE libelle = 'value' AND valeur = '0'
																AND articlechampid = '$articlechampid' AND siteid = '1'");
							$first = $description[description];
							$DB_site->fetch_array($values);
							while ($DB_site->fetch_array($values)){
								$description = $DB_site->query_first("SELECT * FROM article_champ_valeur
																	INNER JOIN article_champ_valeur_site USING(articlechampvaleurid)
																	WHERE libelle = 'value' AND valeur = '$i'
																	AND articlechampid = '$articlechampid' AND siteid = '1'");
								eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationChampDefautBitAddvalue$name"));
								++$i;
							}
						}
					}else{
						$selected = "";
					}
					eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationListeTypesBit"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationListeTypes"));
				$valeurs = $DB_site->query("SELECT * FROM article_champ_valeur INNER JOIN article_champ_valeur_site USING(articlechampvaleurid) WHERE articlechampid = '$articlechampid' AND siteid = '1'");
				while ($valeur = $DB_site->fetch_array($valeurs)){
					if ($valeur[libelle] == "cols" && $valeur[valeur] != "")
						$largeur = $valeur[valeur];
					if ($valeur[libelle] == "rows" && $valeur[valeur] != "")
						$hauteur = $valeur[valeur];
					if ($valeur[libelle] == "maxlength" && $valeur[valeur] != "")
						$longueur = $valeur[valeur];
					if ($valeur[libelle] == "size" && $valeur[valeur] != "")
						$taille = $valeur[valeur];
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationChampDefautBit"));
				$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
				while ($site = $DB_site->fetch_array($sites)){
					$TemplateProduitsModificationFormulairePersonnalisationChampSiteBitPro = "";
					$devise_site_actuel = $tabsites[$site[siteid]][devise_complete];
					$articlechampsite = $DB_site->query_first("SELECT * from article_champ
															  INNER JOIN article_champ_site USING(articlechampid)
															  WHERE articlechampid = '$articlechampid'
															  AND siteid = '$site[siteid]'");
					if (in_array("122", $modules) || $mode == "test_modules")
						eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationChampSiteBitPro"));
					if (in_array($champ[type], array("2", "3", "5", "6"))){
						$name = $tabid[$champ[type]] . "Site";
						$TemplateProduitsModificationFormulairePersonnalisationChampDefautBitAddvalue.$name = "";
						$i = 1;
						$values = $DB_site->query("SELECT * FROM article_champ_valeur
												 WHERE libelle = 'value' AND articlechampid = '$articlechampid'");
						$description = $DB_site->query_first("SELECT * FROM article_champ_valeur
															  INNER JOIN article_champ_valeur_site USING(articlechampvaleurid)
															  WHERE libelle = 'value' AND valeur = '0'
															  AND articlechampid = '$articlechampid' AND siteid = '$site[siteid]'");
						$first = $description[description];
						$DB_site->fetch_array($values);
						while ($DB_site->fetch_array($values)){
							$description = $DB_site->query_first("SELECT * FROM article_champ_valeur
																 INNER JOIN article_champ_valeur_site USING(articlechampvaleurid)
																 WHERE libelle = 'value' AND valeur = '$i'
																 AND articlechampid = '$articlechampid' AND siteid = '$site[siteid]'");
							eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationChampDefautBitAddvalue$name"));
							++$i;
						}
					}
					eval(charge_template($langue,$referencepage,"ModificationFormulairePersonnalisationChampSiteBit"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationChamp"));
			}
			if (!isset($formaction) or $formaction == ""){
				$champs = $DB_site->query("SELECT * FROM article_champ INNER JOIN article_champ_site USING(articlechampid) WHERE artid = '$artid' AND siteid = '1' ORDER BY position");
				if ($DB_site->num_rows($champs) > 0){
					if (in_array("122", $modules) || $mode == "test_modules")
						eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationPro"));
					while ($champ = $DB_site->fetch_array($champs)){
						$TemplateProduitsModificationFormulairePersonnalisationProBit = "";
						$typechamp = $DB_site->query_first("SELECT * FROM article_type_champ WHERE articletypechampid = '$champ[type]'");
						$champ[obligatoire] = ($champ[obligatoire] ? $multilangue[oui] : $multilangue[non]);
						$champ[prixperso] = formaterPrix($champ[prixperso]);
						if (in_array("122", $modules) || $mode == "test_modules"){
							$champ[prixpersopro] = formaterPrix($champ[prixpersopro]);
							eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationProBit"));
						}
						eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationBit"));
					}
					eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationTable"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisationAjouter"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulairePersonnalisation"));
			break;
		case 'piecesjointes':
			$pieces = $DB_site->query("SELECT * FROM piece INNER JOIN article_piece USING(pieceid) WHERE artid = '$artid'");
			if ($DB_site->num_rows($pieces)){
				while ($piece = $DB_site->fetch_array($pieces))
					eval(charge_template($langue, $referencepage, "ModificationFormulairePiecesJointesAssigneesBit"));
				eval(charge_template($langue, $referencepage, "ModificationFormulairePiecesJointesAssignees"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulairePiecesJointesAssigneesVide"));
			}
			$pieces = $DB_site->query("SELECT * FROM piece WHERE pieceid NOT IN(SELECT pieceid FROM article_piece WHERE artid = '$artid')");
			if ($DB_site->num_rows($pieces)){
				while ($piece = $DB_site->fetch_array($pieces))
					eval(charge_template($langue, $referencepage, "ModificationFormulairePiecesJointesExistantesBit"));
				eval(charge_template($langue, $referencepage, "ModificationFormulairePiecesJointesExistantes"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulairePiecesJointesExistantesVide"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulairePiecesJointes"));
			break;
		case 'votesetcommentaires':
			$articlevotes = $DB_site->query("SELECT * FROM articlevote WHERE artid = '$artid' ORDER BY datevote DESC");
			if ($DB_site->num_rows($articlevotes)){
				while ($articlevote = $DB_site->fetch_array($articlevotes)){
					$articlevote[datevote] = date('d/m/Y', strtotime($articlevote[datevote]));
					$note = '';
					for ($i = 0; $i < $articlevote[note]; ++$i)
						$note .= '<i class="fa fa-star"></i>';
					eval(charge_template($langue, $referencepage, "ModificationFormulaireVotesEtCommentairesTableBit"));
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireVotesEtCommentairesTable"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireVotesEtCommentaires"));
			break;
		case 'promotion':
			$sites = $DB_site->query("SELECT * FROM site");
			while($site = $DB_site->fetch_array($sites)){
				$devise_site_actuel = $tabsites[$site[siteid]][devise_complete];
				$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
				eval(charge_template($langue, $referencepage, "ModificationFormulairePromotionSiteBit"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulairePromotion"));
			break;
		case 'remisesgros':
			// Site principal
			$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
			$remises = $DB_site->query("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisearticle USING(remiseid) WHERE artid = '$artid' AND siteid = '1' ORDER BY valeur");
			if ($DB_site->num_rows($remises)){
				while ($remise = $DB_site->fetch_array($remises)){			
					$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
					$typeremisevaleur = ($remise[typeremise] ? $tabsites[1][devise_complete] : "$multilangue[articles]");
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosBit"));
				}
				$remise = $DB_site->query_first("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisearticle USING(remiseid) WHERE artid = '$artid' AND siteid = '1'");
				
				$display_articles_principal = $display_devise_principal = "";
				if($remise[typeremise]){
					$display_articles_principal="style=\"display:none;\"";
				}else{
					$display_devise_principal="style=\"display:none;\"";
				}
				
				$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
				//$typeremisevaleur = ($remise[typeremise] ? "&euro;" : "$multilangue[articles]");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosTypeExistant"));
			}else{
				$display_articles_principal = $display_devise_principal = "";
				$display_articles_principal="style=\"display:none;\"";
				$typeremise = $multilangue[sur_le_prix];
				$typeremisevaleur = $tabsites[1][devise_complete];
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosType"));
			}
			
			// Autres sites
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			$validatejscatalogue="";
			while ($site = $DB_site->fetch_array($sites)){
				$TemplateProduitsModificationFormulaireRemisesGrosSiteBit="";
				$TemplateProduitsModificationFormulaireRemisesGrosSiteType="";
				
				$devise_site_actuel=$tabsites[$site[siteid]][devise_complete];
				
				$remisessite = $DB_site->query("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisearticle USING(remiseid) WHERE artid = '$artid' AND siteid = '$site[siteid]' ORDER BY valeur");
				if ($DB_site->num_rows($remisessite)){
					while ($remisesite = $DB_site->fetch_array($remisessite)){
						$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
						$typeremisesitevaleur = ($remisesite[typeremise] ? $tabsites[$site[siteid]][devise_complete] : "$multilangue[articles]");
						eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosSiteBit"));
					}
					$remisesite = $DB_site->query_first("SELECT * FROM remise INNER JOIN remise_site USING(remiseid) INNER JOIN remisearticle USING(remiseid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
					
					$display_articles = $display_devise = "";
					if($remisesite[typeremise]){
						$display_articles="style=\"display:none;\"";
					}else{
						$display_devise="style=\"display:none;\"";
					}
					
					$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
					//$typeremisesitevaleur = ($remisesite[typeremise] ? "&euro;" : "$multilangue[articles]");
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosSiteTypeExistant"));
				}else{
					$display_articles = $display_devise = "";
					$display_articles="style=\"display:none;\"";
					$typeremisesite = $multilangue[sur_le_prix];
					$typeremisesitevaleur = $tabsites[$site[siteid]][devise_complete];
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosSiteType"));
				}
				$validatejscatalogue.=",valeur$site[siteid]: {
											min: 1
										},
											pctremise$site[siteid]: {
											range: [0,100]
										}";
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosSite"));
			}
			
			eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGros"));
			break;
		case 'remisesgrospro':
			// Site principal
			$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
			$remises = $DB_site->query("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseproarticle USING(remiseid) WHERE artid = '$artid' AND siteid = '1' ORDER BY valeur");
			if ($DB_site->num_rows($remises)){
				while ($remise = $DB_site->fetch_array($remises)){
					$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
					$typeremisevaleur = ($remise[typeremise] ? $tabsites[1][devise_complete] : "$multilangue[articles]");
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProBit"));
				}
				$remise = $DB_site->query_first("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseproarticle USING(remiseid) WHERE artid = '$artid' AND siteid = '1'");
				
				$display_articles_principal = $display_devise_principal = "";
				if($remise[typeremise]){
					$display_articles_principal="style=\"display:none;\"";
				}else{
					$display_devise_principal="style=\"display:none;\"";
				}
				
				$typeremise = ($remise[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
				//$typeremisevaleur = ($remise[typeremise] ? "&euro;" : "$multilangue[articles]");
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProTypeExistant"));
			}else{
				$display_articles_principal = $display_devise_principal = "";
				$display_articles_principal="style=\"display:none;\"";
				$typeremise = $multilangue[sur_le_prix];
				$typeremisevaleur = $tabsites[1][devise_complete];
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProType"));
			}
			
			// Autres sites
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			$validatejscataloguepro="";
			while ($site = $DB_site->fetch_array($sites)){
				$TemplateProduitsModificationFormulaireRemisesGrosProSiteBit="";
				$TemplateProduitsModificationFormulaireRemisesGrosProSiteType="";
				
				$devise_site_actuel=$tabsites[$site[siteid]][devise_complete];				
				
				$remisessite = $DB_site->query("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseproarticle USING(remiseid) WHERE artid = '$artid' AND siteid = '$site[siteid]' ORDER BY valeur");
				if ($DB_site->num_rows($remisessite)){
					while ($remisesite = $DB_site->fetch_array($remisessite)){
						$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
						$typeremisesitevaleur = ($remisesite[typeremise] ? $tabsites[$site[siteid]][devise_complete] : "$multilangue[articles]");
						eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProSiteBit"));
					}
					$remisesite = $DB_site->query_first("SELECT * FROM remisepro INNER JOIN remisepro_site USING(remiseid) INNER JOIN remiseproarticle USING(remiseid) WHERE artid = '$artid' AND siteid = '$site[siteid]'");
					
					$display_articles = $display_devise = "";
					if($remisesite[typeremise]){
						$display_articles="style=\"display:none;\"";
					}else{
						$display_devise="style=\"display:none;\"";
					}
					
					$typeremisesite = ($remisesite[typeremise] ? $multilangue[sur_le_prix] : $multilangue[sur_la_quantite]);
					//$typeremisesitevaleur = ($remisesite[typeremise] ? "&euro;" : "$multilangue[articles]");
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProSiteTypeExistant"));
				}else{
					$display_articles = $display_devise = "";
					$display_articles="style=\"display:none;\"";
					$typeremisesite = $multilangue[sur_le_prix];
					$typeremisesitevaleur = $tabsites[$site[siteid]][devise_complete];
					eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProSiteType"));
				}
				$validatejscataloguepro.=",valeur$site[siteid]: {
												min: 1
											},
												pctremise$site[siteid]: {
												range: [0,100]
											}";
				eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosProSite"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireRemisesGrosPro"));
			break;
		case 'fichiers':
			$fichiers = $DB_site->query("SELECT * FROM fichier INNER JOIN fichier_site USING(fichierid) INNER JOIN article_fichier USING(fichierid) WHERE artid = '$artid' AND siteid = '1'");
			if ($DB_site->num_rows($fichiers)){
				while ($fichier = $DB_site->fetch_array($fichiers))
					eval(charge_template($langue, $referencepage, "ModificationFormulaireFichiersAssignesBit"));
				eval(charge_template($langue, $referencepage, "ModificationFormulaireFichiersAssignes"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulaireFichiersAssignesVide"));
			}
			if ($dir = @opendir($rootpath . "configurations/$host/ftp")){
				while ($file = readdir($dir)){
					if (pathinfo($file, PATHINFO_EXTENSION)){
						$tab[] = $file;
					}
				}
				closedir($dir);
			}
			$fichiers = $DB_site->query("SELECT * FROM fichier INNER JOIN fichier_site USING(fichierid) WHERE fichierid NOT IN(SELECT fichierid FROM article_fichier WHERE artid = '$artid') AND siteid = '1'");
			if ($DB_site->num_rows($fichiers) || count($tab)){
				while ($fichier = $DB_site->fetch_array($fichiers))
					eval(charge_template($langue, $referencepage, "ModificationFormulaireFichiersExistantsBit"));
				if (count($tab)){
					foreach ($tab as $key => $value){
						$fichier[libelle] = $value;
						$fichier[fichierid] = $value;
						eval(charge_template($langue, $referencepage, "ModificationFormulaireFichiersExistantsBit"));
					}
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireFichiersExistants"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationFormulaireFichiersExistantsVide"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireFichiers"));
			break;
		case 'historiqueprix':
			
			if (in_array("122" , $modules) || $mode == "test_modules"){
				eval(charge_template($langue, $referencepage, "ModificationFormulaireHistoriquePrixPro"));
				eval(charge_template($langue, $referencepage, "ModificationFormulaireHistoriquePrixProSite"));
			}
			
			$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
			$historiques = $DB_site->query("SELECT * FROM article_historique_prix_site WHERE artid = '$artid' AND siteid = '1' AND datefin != '0' ORDER BY datefin");
			while ($historique = $DB_site->fetch_array($historiques)){
				$TemplateProduitsModificationsFormulaireHistoriquePrixProBit = "";
				$devise_site_actuel = $tabsites[1][devise_complete];
				$devise_site_actuel_prixpromo = $tabsites[1][devise_complete];
				$historique[periode] = "$multilangue[du] " . date('<b>d/m/Y</b> H:i:s', $historique[datedebut]) . " $multilangue[au] " . date('<b>d/m/Y</b> H:i:s', $historique[datefin]);
				$historique[prix] = formaterPrix($historique[prix]);
				$historique[prixpromo] = formaterPrix($historique[prix] * (1 - ($historique[pctpromo] / 100)));
				if (in_array("122" , $modules) || $mode == "test_modules"){
					if($historique[prixpro] == "0"){
						$historique[prixpro] = "-";
						$devise_site_actuel_prixpro = "";
					}else{
						$historique[prixpro] = formaterPrix($historique[prixpro]);
						$devise_site_actuel_prixpro = $tabsites[1][devise_complete];
					}
					eval(charge_template($langue, $referencepage, "ModificationsFormulaireHistoriquePrixProBit"));
				}
				if($historique[prixpromo] == $historique[prix]){
					$historique[prixpromo] = "-";
					$devise_site_actuel_prixpromo = "";
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireHistoriquePrixBit"));
			}
			
			$TemplateProduitsModificationsFormulaireHistoriquePrixProBit = "";
			$historique_sans_fin = $DB_site->query_first("SELECT * FROM article_historique_prix_site WHERE artid = '$artid' AND siteid = '1' AND datefin = '0'");
			$devise_site_actuel = $tabsites[1][devise_complete];
			$devise_site_actuel_prixpromo = $tabsites[1][devise_complete];
			$historique[periode] = "$multilangue[a_partir_du] " . date('<b>d/m/Y</b> H:i:s', $historique_sans_fin[datedebut]);
			$historique[prix] = formaterPrix($historique_sans_fin[prix]);
			$historique[prixpromo] = formaterPrix($historique_sans_fin[prix] * (1 - ($historique_sans_fin[pctpromo] / 100)));
			if (in_array("122" , $modules) || $mode == "test_modules"){
				if($historique_sans_fin[prixpro] == "0"){
					$historique[prixpro] = "-";
					$devise_site_actuel_prixpro = "";
				}else{
					$historique[prixpro] = formaterPrix($historique_sans_fin[prixpro]);
					$devise_site_actuel_prixpro = $tabsites[1][devise_complete];
				}
				eval(charge_template($langue, $referencepage, "ModificationsFormulaireHistoriquePrixProBit"));
			}
			if($historique[prixpromo] == $historique[prix]){
				$historique[prixpromo] = "-";
				$devise_site_actuel_prixpromo = "";
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireHistoriquePrixBit"));
			
			
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
			while ($site = $DB_site->fetch_array($sites)){
				$TemplateProduitsModificationFormulaireHistoriquePrixSiteBit = "";
				$TemplateProduitsModificationsFormulaireHistoriquePrixProSiteBit = "";
				$historiquessite = $DB_site->query("SELECT * FROM article_historique_prix_site WHERE artid = '$artid' AND siteid = '$site[siteid]' AND datefin != '0' ORDER BY datefin");
				while ($historiquesite = $DB_site->fetch_array($historiquessite)){
					$TemplateProduitsModificationsFormulaireHistoriquePrixProSiteBit = "";
					$devise_site_actuel = $tabsites[$site[siteid]][devise_complete];
					$devise_site_actuel_prixpromo = $tabsites[$site[siteid]][devise_complete];
					$devise_site_actuel_prixpro = $tabsites[$site[siteid]][devise_complete];
					$historiquesite[periode] = "$multilangue[du] " . date('<b>d/m/Y</b> H:i:s', $historiquesite[datedebut]) . " $multilangue[au] " . date('<b>d/m/Y</b> H:i:s', $historiquesite[datefin]);
					$historiquesite[prix] = formaterPrix($historiquesite[prix]);
					$historiquesite[prixpromo] = formaterPrix($historiquesite[prix] * (1 - ($historiquesite[pctpromo] / 100)));
					if (in_array("122" , $modules) || $mode == "test_modules"){
						if($historiquesite[prixpro] == "0"){
							$historiquesite[prixpro] = "-";
							$devise_site_actuel_prixpro = "";
						}else{
							$historiquesite[prixpro] = formaterPrix($historiquesite[prixpro]);
							$devise_site_actuel_prixpro = $tabsites[$site[siteid]][devise_complete];
						}
						eval(charge_template($langue, $referencepage, "ModificationsFormulaireHistoriquePrixProSiteBit"));
					}
					
					if($historiquesite[prixpromo] == $historiquesite[prix]){
						$historiquesite[prixpromo] = "-";
						$devise_site_actuel_prixpromo = "";
					}
					
					eval(charge_template($langue, $referencepage, "ModificationFormulaireHistoriquePrixSiteBit"));
				}
				
				$historiquesite_sans_fin = $DB_site->query_first("SELECT * FROM article_historique_prix_site WHERE artid = '$artid' AND siteid = '$site[siteid]' AND datefin = '0'");
				$TemplateProduitsModificationsFormulaireHistoriquePrixProSiteBit = "";
				$devise_site_actuel = $tabsites[$site[siteid]][devise_complete];
				$devise_site_actuel_prixpromo = $tabsites[$site[siteid]][devise_complete];
				$historiquesite[periode] = "$multilangue[a_partir_du] " . date('<b>d/m/Y</b> H:i:s', $historiquesite_sans_fin[datedebut]);
				$historiquesite[prix] = formaterPrix($historiquesite_sans_fin[prix]);
				$historiquesite[prixpromo] = formaterPrix($historiquesite_sans_fin[prix] * (1 - ($historiquesite_sans_fin[pctpromo] / 100)));
				if (in_array("122" , $modules) || $mode == "test_modules"){
					if($historiquesite_sans_fin[prixpro] == "0"){
						$historiquesite[prixpro] = "-";
						$devise_site_actuel_prixpro = "";
					}else{
						$historiquesite[prixpro] = formaterPrix($historiquesite_sans_fin[prixpro]);
						$devise_site_actuel_prixpro = $tabsites[$site[siteid]][devise_complete];
					}
					eval(charge_template($langue, $referencepage, "ModificationsFormulaireHistoriquePrixProSiteBit"));
				}
				if($historiquesite[prixpromo] == $historiquesite[prix]){
					$historiquesite[prixpromo] = "-";
					$devise_site_actuel_prixpromo = "";
				}
				eval(charge_template($langue, $referencepage, "ModificationFormulaireHistoriquePrixSiteBit"));
				
				eval(charge_template($langue, $referencepage, "ModificationFormulaireHistoriquePrixSite"));
			}
			eval(charge_template($langue, $referencepage, "ModificationFormulaireHistoriquePrix"));
			break;
	}
	if (isset($artid)){
		$libNavigSupp = "$multilangue[ajt_modif_article] : $article[libelle] - $article[artcode]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{
		$libNavigSupp = $multilangue[ajt_article];
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}
	eval(charge_template($langue,$referencepage,"Modification"));
}

if (!isset($action) or $action == ""){
	$sites = $DB_site->query("SELECT * FROM site");
	$num_colonne = 16;
	$colonne_table = "";
	while($site = $DB_site->fetch_array($sites)){
		$colonne_table .= "{'sClass': 'ta-center', 'bSortable': false, 'mData': 'activer$site[siteid]', 'iDataSort': $num_colonne, 'data': 'activer$site[siteid]', 'bVisible': false},";
		eval(charge_template($langue, $referencepage, "ListeColonneSiteBit"));
		eval(charge_template($langue, $referencepage, "ListeHeaderSiteBit"));
		$num_colonne++;
	}
	$colonne_table .= '{"sClass": "ta-center", "bSortable": false, "mData": "actions", "iDataSort": '.$num_colonne.', "data": "actions", "bVisible": true}';
	
	eval(charge_template($langue, $referencepage, "Liste"));
	$libNavigSupp = $multilangue[liste_produits];
	eval(charge_template($langue, $referencepage, "NavigSupp"));
}

$TemplateIncludejavascript = eval(charge_template($langue,  $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>