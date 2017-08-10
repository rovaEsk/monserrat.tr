<?php
include "./includes/header.php";

$referencepage="codes_reduction";
$pagetitle = "Codes de réduction - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

//$mode = "test_modules";

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) && $action == "voirutilisations") {
	$contenuFichier = "$multilangue[code];$multilangue[date_de_commande];$multilangue[numero_commande];$multilangue[client];$multilangue[montant_commande];$multilangue[montant_reduction]";
	$cadeau = $DB_site->query_first("SELECT code FROM cadeau WHERE cadeauid = '$cadeauid'");
	$devise = $DB_site->query_first("SELECT * FROM devise WHERE deviseid = '$cadeau[deviseid]'");
	$devise[symbole] = ($devise[symbole] != "" ? $devise[symbole] : "&euro;");
	$factures = $DB_site->query("SELECT * FROM facture WHERE etatid IN (1,5) AND cadeauid = '$cadeauid'");
	while ($facture = $DB_site->fetch_array($factures)) {
		$contenuFichier .= "\n";
		$contenuFichier .= "$cadeau[code];";
		$contenuFichier .= convertirDateEnChaine($facture[datefacture]).";";
		$contenuFichier .= "$facture[factureid];";
		$contenuFichier .= "$facture[nom] $facture[prenom];";
		$contenuFichier .= formaterPrix($facture[montanttotal_ttc]).";";
		$contenuFichier .= formaterPrix($facture[montantcadeau]).";";
	}
	if (!is_dir($rootpath . "configurations/$host/exports"))
		mkdir($rootpath . "configurations/$host/exports", 0777);
	$path = $rootpath . "configurations/$host/exports/cadeau.csv";
	if ($fd = fopen($path, 'w')) {
		fwrite($fd, stripslashes(html_entity_decode($contenuFichier)));
		fclose($fd);
	}
	$totalCommande = 0;
	$totalReduction = 0;
	$commandes = $DB_site->query("SELECT * FROM facture WHERE etatid IN (1,5) AND deleted='0' AND cadeauid = '$cadeauid' ORDER BY factureid DESC");
	while ($commande = $DB_site->fetch_array($commandes)){
		$totalCommande +=$commande[montanttotal_ttc];
		$totalReduction += $commande[montantcadeau];
		$commande[numerofacture] = (!$commande[numerofacture] ? "--" : $commande[numerofacture]);
		$commande[datefacture] = convertirDateEnChaine($commande[datefacture]);
		$commande[montanttotal_ttc] = formaterPrix($commande[montanttotal_ttc]);
		$commande[montantcadeau] = formaterPrix($commande[montantcadeau]);
		eval(charge_template($langue,  $referencepage, "ListeUtilisationsBit"));
	}
	$totalCommande = formaterPrix($totalCommande);
	$totalReduction = formaterPrix($totalReduction);
	eval(charge_template($langue,  $referencepage, "ListeUtilisations"));
	$libNavigSupp = $multilangue[liste_clients_utilise_bon];
	eval(charge_template($langue,  $referencepage, "NavigSupp"));
}

if (isset($action) and $action == "modifier2") {
	if($admin_droit[$scriptcourant][ecriture]){
		$count = $DB_site->query_first("SELECT COUNT(*) count FROM cadeau WHERE code = '" .  securiserSql($_POST[code]) . "'");
		if ($count[count] > 0 && $_POST[import] != ""){
			$texteErreur = $multilangue[code_deja_existant];
			eval(charge_template($langue,$referencepage,"Erreur"));
			$action = "modifier";
		}
		if (strlen($_POST[code]) <= 4 && $_POST[import] != ""){
			$texteErreur = $multilangue[code_longueur_minimum];
			eval(charge_template($langue,$referencepage,"Erreur"));
			$action = "modifier";
		}
		$toutimporte = 1;
		// Ajout avec import de codes
		if(isset($_FILES['code']) && $_FILES['code']['name'] != ""){
			$listeTypesAutorise = array("text/csv");
			erreurUpload("code", $listeTypesAutorise, 1048576);
			if(!$erreur){
				$fileData=fopen($_FILES['code']['tmp_name'],'r');
				if (($handle = $fileData) !== FALSE) {
					// Chaque ligne du fichier
					while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
						if(strlen($data[0]) > 4){
							$DB_site->query("INSERT INTO cadeau (cadeauid) VALUES ('')");
							$cadeauid = $DB_site->insert_id();
							if ($_POST[datefin] != "") {
								list($jour, $mois, $annee) = explode('/', $_POST[datefin]);
								$date = mktime(0, 0, 0, $mois, $jour, $annee);
							}
							if($commentaire == ""){
								$commentaire = $data[1];
							}
							$DB_site->query("UPDATE cadeau SET code = '" . securiserSql($data[0]) . "',
												commentaire = '" . securiserSql($commentaire) . "',
												siteid = '".securiserSql($_POST[site])."',
												typecadeauid = '" . securiserSql($_POST[typecadeauid]) . "',
												valeurcadeau = '" . securiserSql($_POST[valeurcadeau]) . "',
												montantminimum = '" . securiserSql($_POST[montantminimum]) . "',
												nbrfois = '" . securiserSql($_POST[nbrfois]) . "',
												nbrmaxi = '" . securiserSql($_POST[nbrmaxi]) . "',
												datefin = '$date', 
												userid = '" . securiserSql($_POST[userid]) . "',
												conditionUtilisation = '" . securiserSql($_POST[conditionUtilisation]) . "'
												WHERE cadeauid = '$cadeauid'");
							switch ($_POST[application]) {
								case 1:
									$catidOffres = explode(",", $_POST[catidOffre]);
									foreach($catidOffres as $key => $value) {
										if ($value != "0")
											$DB_site->query("INSERT INTO categorie_cadeau (cadeauid, catid) VALUES ('$cadeauid', '" . securiserSql($value) . "')");
									}
									break;
								case 2:
									if ($_POST[artid] != "")
										$DB_site->query("INSERT INTO article_cadeau (cadeauid, artid) VALUES ('$cadeauid', '" . securiserSql($_POST[artid]) . "')");
									break;
								case 3:
									$articles = explode(",", $_POST[articles]);
									foreach($articles as $value) {
										$value = explode("t", $value);
										if($value[0] == "ar"){
											$DB_site->query("INSERT INTO article_cadeau (cadeauid, artid) VALUES ('$cadeauid', '$value[1]')");
										}
									}
									break;
								case 4:
									$DB_site->query("UPDATE cadeau SET marqueid= '$marqueid' WHERE cadeauid = '$cadeauid'");
									break;
							}
							if ($_POST[typecadeauid] == 2 || $_POST[typecadeauid] == 3){
								if(sizeof($mode) > 0){
									foreach ($mode as $key => $value)
										$DB_site->query("INSERT INTO mode_livraison_cadeau (cadeauid, modelivraisonid) VALUES ('$cadeauid', '$value')");
								}
							}
						} else {
							$toutimporte = 0;	
						}
						$commentaire = "";
					}
					fclose($handle);
				}
			}else{
				header('location: codes_reduction.php?action=modifier&erreur=1');
			}
		// Ajout d'un seul code sans import
		} else {
			$DB_site->query("INSERT INTO cadeau (cadeauid) VALUES ('')");
			$cadeauid = $DB_site->insert_id();
			if ($_POST[datefin] != "") {
				list($jour, $mois, $annee) = explode('/', $_POST[datefin]);
				$date = mktime(0, 0, 0, $mois, $jour, $annee);
			}
			$DB_site->query("UPDATE cadeau SET code = '" . securiserSql($_POST[code]) . "',
								commentaire = '" . securiserSql($_POST[commentaire]) . "',
								siteid = '".securiserSql($_POST[site])."',
								typecadeauid = '" . securiserSql($_POST[typecadeauid]) . "',
								valeurcadeau = '" . securiserSql($_POST[valeurcadeau]) . "',
								montantminimum = '" . securiserSql($_POST[montantminimum]) . "',
								nbrfois = '" . securiserSql($_POST[nbrfois]) . "',
								nbrmaxi = '" . securiserSql($_POST[nbrmaxi]) . "',
								datefin = '$date', 
								userid = '" . securiserSql($_POST[userid]) . "',
								conditionUtilisation = '" . securiserSql($_POST[conditionUtilisation]) . "'
								WHERE cadeauid = '$cadeauid'");
			switch ($_POST[application]) {
				case 1:
					$catidOffres = explode(",", $_POST[catidOffre]);
					foreach($catidOffres as $key => $value) {
						if ($value != "0")
							$DB_site->query("INSERT INTO categorie_cadeau (cadeauid, catid) VALUES ('$cadeauid', '" . securiserSql($value) . "')");
					}
					break;
				case 2:
					if ($_POST[artid] != "")
						$DB_site->query("INSERT INTO article_cadeau (cadeauid, artid) VALUES ('$cadeauid', '" . securiserSql($_POST[artid]) . "')");
					break;
				case 3:
					$articles = explode(",", $_POST[articles]);
					foreach($articles as $value) {
						$value = explode("t", $value);
						if($value[0] == "ar"){
							$DB_site->query("INSERT INTO article_cadeau (cadeauid, artid) VALUES ('$cadeauid', '$value[1]')");
						}
					}
					break;
				case 4:
					$DB_site->query("UPDATE cadeau SET marqueid= '$marqueid' WHERE cadeauid = '$cadeauid'");
					break;
			}
			if ($_POST[typecadeauid] == 2 || $_POST[typecadeauid] == 3){
				if(sizeof($mode) > 0){
					foreach ($mode as $key => $value)
						$DB_site->query("INSERT INTO mode_livraison_cadeau (cadeauid, modelivraisonid) VALUES ('$cadeauid', '$value')");
				}
			}
		}
		if($toutimporte == 0){
			header('location: codes_reduction.php?erreur=1');
		}else{
			header('location: codes_reduction.php');
		}
	}else{
		header('location: codes_reduction.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "modifier") {
	if(isset($erreur) && $erreur == "1"){
		$texteErreur = $multilangue[fichier_doit_etre_csv];
		eval(charge_template($langue,$referencepage,"Erreur"));
	}
	if(isset($cadeauid)){
		$code = substr($multilangue[code_longueur_minimum], strrpos($multilangue[code_longueur_minimum], '5'), -1);
		$cadeau = $DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid = '$cadeauid'");
		$sites = $DB_site->query("SELECT * from site ORDER BY siteid");
		while($site = $DB_site->fetch_array($sites)){
			$devise_site_actuel = $tabsites[$site[siteid]][devise_complete];
			if($site[siteid] == $cadeau[siteid]){
				$selected = "selected=\"selected\"";
				$display = "";
			} else {
				$selected = "";
				$display = "style=\"display:none;\"";
			}
			eval(charge_template($langue,$referencepage,"ListeSiteBit"));
			eval(charge_template($langue,$referencepage,"DeviseSymboleBit"));
			eval(charge_template($langue,$referencepage,"DeviseSymboleBit2"));
		}
		if (in_array("5949", $modules) || $mode == "test_modules")
			eval(charge_template($langue,$referencepage,"ModificationSelectBonachat"));
		$selected0 = "";
		$selected1 = "";
		$selected2 = "";
		$selected3 = "";
		$selected4 = "";
		$selected5 = "";
		switch ($cadeau[typecadeauid]) {
			case "0":
				$selected0= "selected";
				break;
			case "1":
				$selected1 = "selected";
				break;
			case "2":
				$selected2 = "selected";
				break;
			case "3":
				$selected3 = "selected";
				break;
			case "4":
				$selected4 = "selected";
				break;
		}
		$modes = $DB_site->query("SELECT * FROM mode_livraison INNER JOIN mode_livraison_site USING(modelivraisonid) WHERE activeV1 = '1' ORDER BY position");
		while ($modeLivraison = $DB_site->fetch_array($modes)) {
			$modelivraisoncadeau = $DB_site->query_first("SELECT * FROM mode_livraison_cadeau WHERE cadeauid = '$cadeauid' AND modelivraisonid = '$modeLivraison[modelivraisonid]'");
			$checked = ($modelivraisoncadeau[modelivraisonid] != "" ? "checked" : "");
			eval(charge_template($langue,$referencepage,"ModificationSelectMode"));
		}
		$rayon = $DB_site->query("SELECT * FROM categorie_cadeau WHERE cadeauid='$cadeauid'");
		$articles = $DB_site->query("SELECT * FROM article_cadeau WHERE cadeauid='$cadeauid'");
		if ($DB_site->num_rows($rayon) != 0){
			$selectedappli1 = "selected";
			$tabCategorie = "";
			while($categorie_offre = $DB_site->fetch_array($rayon)){
				$tabCategorie .= "$categorie_offre[catid],";
			}
		}
			
		
		if ($DB_site->num_rows($articles) > 1){
			$selectedappli3 = "selected";
			$tabArticle = "";
			while($article_cadeau = $DB_site->fetch_array($articles)){
				$tabArticle .= "art$article_cadeau[artid],";
			}
		}
		elseif ($DB_site->num_rows($articles) > 0)
			$selectedappli2 = "selected";
		else
			$selectedappli0 = "selected";
		$marques = $DB_site->query("SELECT * FROM marque INNER JOIN marque_site USING(marqueid) WHERE siteid = '1' ORDER BY libelle");
		while ($marque = $DB_site->fetch_array($marques)) {
			eval(charge_template($langue,$referencepage,"ModificationSelectMarque"));
		}
		$article = $DB_site->query_first("SELECT * FROM article_cadeau INNER JOIN article USING(artid) INNER JOIN article_site USING(artid) WHERE cadeauid = '$cadeauid'");
		if ($article[libelle] != "")
			eval(charge_template($langue,$referencepage,"ModificationArticle"));
		$checked = "checked";
		if ($cadeau[nbrfois] == "0" || $cadeau[nbrfois] == "")
			$checkednbrfois = "checked";
		if ($cadeau[nbrmaxi] == "0" || $cadeau[nbrmaxi] == "")
			$checkednbrmaxi = "checked";
		$cadeau[nbrfois] = ($cadeau[nbrfois] == "0" ? "" : $cadeau[nbrfois]);
		$cadeau[nbrmaxi] = ($cadeau[nbrmaxi] == "0" ? "" : $cadeau[nbrmaxi]);
		$cadeau[datefin] = ($cadeau[datefin] == "0" || $cadeau[datefin] == "" ? "" : date("d/m/Y", $cadeau[datefin]));
		$utilisateur = $DB_site->query_first("SELECT * FROM cadeau INNER JOIN utilisateur USING(userid) WHERE cadeauid = '$cadeauid'");
		$utilisateur[mail] = ($utilisateur[mail] != "" ? " (" . $utilisateur[mail] . ") " : "");
		$utilisateur[raisonsociale] = ($utilisateur[raisonsociale] != "" ? " - " . $utilisateur[raisonsociale] : "");
		if ($utilisateur[userid] != "")
			eval(charge_template($langue,$referencepage,"ModificationUtilisateur"));
		if ($cadeau[conditionUtilisation] == "1")
			$selectedconditionUtilisation = "selected";
		$libNavigSupp = "$multilangue[ajt_bon_reduction]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		eval(charge_template($langue,$referencepage,"Modification"));
		
	}else{
		$checkednbrfois = "checked=\"checked\"";
		$checkednbrmaxi = "checked=\"checked\"";
		$code = substr($multilangue[code_longueur_minimum], strrpos($multilangue[code_longueur_minimum], '5'), -1);
		$sites = $DB_site->query("SELECT * from site ORDER BY siteid");
		while($site = $DB_site->fetch_array($sites)){
			$devise_site_actuel = $tabsites[$site[siteid]][devise_complete];
			if($site[siteid] == '1'){
				$selected = "selected=\"selected\"";
				$display = "";
			} else {
				$selected = "";
				$display = "style=\"display:none;\"";
			}
			eval(charge_template($langue,$referencepage,"ListeSiteBit"));
			eval(charge_template($langue,$referencepage,"DeviseSymboleBit"));
			eval(charge_template($langue,$referencepage,"DeviseSymboleBit2"));
		}
		$modes = $DB_site->query("SELECT * FROM mode_livraison INNER JOIN mode_livraison_site USING(modelivraisonid) WHERE activeV1 = '1' ORDER BY position");
		while ($modeLivraison = $DB_site->fetch_array($modes)) {
			$modelivraisoncadeau = $DB_site->query_first("SELECT * FROM mode_livraison_cadeau WHERE cadeauid = '$cadeauid' AND modelivraisonid = '$modeLivraison[modelivraisonid]'");
			$checked = ($modelivraisoncadeau[modelivraisonid] != "" ? "checked" : "");
			eval(charge_template($langue,$referencepage,"ModificationSelectMode"));
		}
		$marques = $DB_site->query("SELECT * FROM marque INNER JOIN marque_site USING(marqueid) WHERE siteid = '1' ORDER BY libelle");
		while ($marque = $DB_site->fetch_array($marques)) {
			eval(charge_template($langue,$referencepage,"ModificationSelectMarque"));
		}
		if (in_array("5949", $modules) || $mode == "test_modules")
			eval(charge_template($langue,$referencepage,"ModificationSelectBonachat"));
		$libNavigSupp = "$multilangue[ajt_bon_reduction]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		eval(charge_template($langue,$referencepage,"Modification"));
	}
	
}

if (isset($action) && $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM cadeau WHERE cadeauid = '$cadeauid'");
		$DB_site->query("DELETE FROM article_cadeau WHERE cadeauid = '$cadeauid'");
		$DB_site->query("DELETE FROM categorie_cadeau WHERE cadeauid = '$cadeauid'");
		$DB_site->query("DELETE FROM mode_livraison_cadeau WHERE cadeauid = '$cadeauid'");
		header('location: codes_reduction.php');
	}else{
		header('location: codes_reduction.php?erreurdroits=1');	
	}
}

if (!isset($action) or $action == ""){
	if(isset($erreur) && $erreur=="1"){
		$texteErreur = "$multilangue[erreur_import_code_reduction]";
		eval(charge_template($langue,  $referencepage, "Erreur"));
	}
	eval(charge_template($langue,  $referencepage, "Liste"));
	$libNavigSupp = $multilangue[liste_bons_reductions];
	eval(charge_template($langue,  $referencepage, "NavigSupp"));
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