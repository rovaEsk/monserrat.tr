<?php
include "./includes/header.php";

$referencepage="formulaires";
$pagetitle = "Gestion des formulaires - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) && $action == "exportCsv") {
	$formulaire = $DB_site->query_first("SELECT * FROM formulaire INNER JOIN formulaire_site USING(formulaireid) WHERE formulaire.formulaireid = '$formulaireid' AND siteid = '1'");
	$path = "export/reponsesFormulaires/formulaire" . $formulaire[nom] . date("dmY") . ".csv";
	@unlink($path);
	$fichier = fopen($path, "a");
	fseek($fichier, 0);
	$contenu = "Date;Heure";
	$champs = $DB_site->query("SELECT * FROM formulaire_champ_site AS fcs
								INNER JOIN formulaire_champ AS fc USING(formulairechampid)
								WHERE fc.formulaireid = '$formulaireid' 
								ORDER BY fc.position");
	while ($champ = $DB_site->fetch_array($champs)){
		$contenu .= "$champ[libelle];";
	}
	$contenu .= "\n";
	$reponses = $DB_site->query("SELECT * FROM formulaire_reponse WHERE formulaireid = '$formulaireid' ORDER BY formulairereponseid");
	while ($reponse = $DB_site->fetch_array($reponses)){
		$contenu .= convertirDateEnChaine($reponse[date]).";$reponse[heure];";
		$chpreponses = $DB_site->query("SELECT * FROM  formulaire_reponse_champ WHERE formulairereponseid = '$reponse[formulairereponseid]' ORDER BY formulairereponsechampid");
		while ($chpreponse = $DB_site->fetch_array($chpreponses)){
			$chpreponse[valeur]=str_replace(";","",$chpreponse[valeur]);
			$chpreponse[valeur]=str_replace("\n","",$chpreponse[valeur]);
			$chpreponse[valeur]=str_replace("\r","",$chpreponse[valeur]);
			$contenu .= $chpreponse[valeur].";";
		}
		$contenu .= "\n";
	}
	fputs($fichier, $contenu);
	fclose($fichier);
	header("Location: ./$path");  
	exit(); 
}

if (isset($action) && $action == "envoyer"){
	if($admin_droit[$scriptcourant][ecriture]){
		if (isset($sujet) and $sujet != "" and isset($message) and $message != "") {
			$user = $DB_site->query_first("SELECT u.mail FROM utilisateur u INNER JOIN formulaire_reponse fr USING(userid) WHERE fr.formulairereponseid = '$formulairereponseid'");
			$date = date('Y-m-d-H-i-s');
			$DB_site->query("INSERT INTO formulaire_reponse_contact(formulairereponseid, datecontact, sujet, contenu) VALUES ('$formulairereponseid', '$date', '".addslashes($sujet)."', '".addslashes($message)."')");
			email($DB_site, $user[mail], $sujet, stripslashes(nl2br($message)), $params[mail_contact]);
		}else{
			$texteErreur = $multilangue[all_champs_obligatoires];
			eval(charge_template($langue,$referencepage,"Erreur"));
		}
		header("location: formulaires.php?action=reponseschamps&formulaireid=$formulaireid&formulairereponseid=$formulairereponseid");
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

if (isset($action) && $action == "reponseschamps"){
	$reponses = $DB_site->query("SELECT * FROM formulaire_reponse_champ WHERE formulairereponseid = '$formulairereponseid'");
	while ($reponse = $DB_site->fetch_array($reponses)){
		// On va chercher le type du champ
		$formulaire_reponse = $DB_site->query_first("SELECT * FROM formulaire_reponse WHERE formulairereponseid = '$formulairereponseid'");
		
		$formulaire_type_champ = $DB_site->query_first("SELECT * FROM formulaire_champ WHERE formulaireid = '$formulaire_reponse[formulaireid]' AND nom='$reponse[champ]'");
		if($formulaire_type_champ[type] == 7){
			$reponse[valeur]="<a href=\"http://$host/$reponse[valeur]\" target=\"_blank\">$reponse[valeur]</a>";
		}
		eval(charge_template($langue, $referencepage, "DetailsReponsesBit"));
		
	}
	$reponsescontacts = $DB_site->query("SELECT * FROM formulaire_reponse_contact WHERE formulairereponseid = '$formulairereponseid'");
	$count = $DB_site->query_first("SELECT COUNT(*) count FROM formulaire_reponse_contact WHERE formulairereponseid = '$formulairereponseid'");
	if ($count[count] > 0){
		while ($reponsecontact = $DB_site->fetch_array($reponsescontacts)){
			$date = explode("-", $reponsecontact[datecontact]);
			eval(charge_template($langue, $referencepage, "DetailsReponsesContactsBit"));
		}
		eval(charge_template($langue, $referencepage, "DetailsReponsesContacts"));
	}
	eval(charge_template($langue, $referencepage, "DetailsReponses"));
	$libNavigSupp = "$multilangue[details_reponse]";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

// SUPPRIMER UN STATUT
if (isset($action) and $action == "supprimerstatut") {
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM formulaire_reponse_statut WHERE statutid = '$statutid'");
		header("location: formulaires.php?action=statuts&formulaireid=$formulaireid");
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

//MODIFIER UN STATUT (Enregistrement BDD)
if (isset($action) and $action == "modifierstatut2") {
	if($admin_droit[$scriptcourant][ecriture]){
		if($statutid == ""){
			$DB_site->query("INSERT INTO formulaire_reponse_statut(statutid)VALUES ('')");
			$statutid = $DB_site->insert_id();
			$nouveaustatut = 1;
		}
		$DB_site->query("UPDATE formulaire_reponse_statut SET libelle = '" . securiserSql($_POST[libelle]) . "' WHERE statutid = '$statutid'");
		if ($nouveaustatut){
			$texteSuccess = $multilangue[le_statut]." <strong>" . securiserSql($_POST[libelle], "html") . "</strong> ".$multilangue[a_bien_ete_cre];
		}else{
			$texteSuccess = $multilangue[le_statut]." <strong>" . securiserSql($_POST[libelle], "html") . "</strong> ".$multilangue[modifie];
		}
		eval(charge_template($langue,$referencepage,"Success"));
		header("location: formulaires.php?action=statuts&formulaireid=$formulaireid");
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

//AJOUTER OU MODIFIER UN STATUT
if (isset($action) and $action == "modifierstatut") {
	$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
	if (isset($statutid)){
		$statut = $DB_site->query_first("SELECT * FROM formulaire_reponse_statut WHERE statutid = '$statutid'");
		$libNavigSupp = "$multilangue[modif_statut] : $statut[libelle]";
	}else{
		$libNavigSupp = "$multilangue[ajt_statut]";
	}
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue, $referencepage, "ModificationStatut"));
}

//LISTER LES STATUTS
if (isset($action) && $action == "statuts"){
	$statuts = $DB_site->query("SELECT * FROM formulaire_reponse_statut ORDER BY statutid");
	while ($statut = $DB_site->fetch_array($statuts)){
		eval(charge_template($langue, $referencepage, "ListeStatutsBit"));
		}
	eval(charge_template($langue, $referencepage, "ListeStatuts"));
	$libNavigSupp = "$multilangue[liste_statuts_reponse]";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

// SUPPRIMER UNE REPONSE
if (isset($action) and $action == "supprimerreponse") {
	if($admin_droit[$scriptcourant][suppression]){
		$formulaire = $DB_site->query_first("SELECT formulaireid FROM formulaire_reponse
										WHERE formulairereponseid = '$formulairereponseid'");
		
		$DB_site->query("DELETE FROM formulaire_reponse WHERE formulairereponseid = '$formulairereponseid'");
		$DB_site->query("DELETE FROM formulaire_reponse_champ WHERE formulairereponseid = '$formulairereponseid'");
		$DB_site->query("DELETE FROM formulaire_reponse_contact WHERE formulairereponseid = '$formulairereponseid'");
		header("location: formulaires.php?action=reponses&formulaireid=$formulaire[formulaireid]");
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

if (isset($action) and $action == "setstatut") {
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE formulaire_reponse SET statutid = '$_POST[statutid]' WHERE formulairereponseid = '$_POST[formulairereponseid]'");
		header("location: formulaires.php?action=reponses&formulaireid=$formulaireid");
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

//LISTER LES REPONSES
if (isset($action) && $action == "reponses"){
	$reponses = $DB_site->query("SELECT * FROM formulaire_reponse
								WHERE formulaireid = '$formulaireid' ORDER BY date DESC, heure DESC LIMIT 60");
	while ($reponse = $DB_site->fetch_array($reponses)){
		$statuts = $DB_site->query("SELECT * FROM formulaire_reponse_statut ORDER BY statutid");
		$TemplateFormulairesListeReponsesStatuts = "";
		$TemplateFormulairesListeReponsesStatutsBit = "";
		while ($statut = $DB_site->fetch_array($statuts)){
			if ($reponse[statutid] == $statut[statutid])
				$selected = "selected";
			else
				$selected = "";
			eval(charge_template($langue, $referencepage, "ListeReponsesStatutsBit"));
		}
		eval(charge_template($langue, $referencepage, "ListeReponsesStatuts"));
		eval(charge_template($langue, $referencepage, "ListeReponsesBit"));
	}
	eval(charge_template($langue, $referencepage, "ListeReponses"));
	$formulaire = $DB_site->query_first("SELECT * FROM formulaire
										INNER JOIN formulaire_site
										ON formulaire.formulaireid = formulaire_site.formulaireid
										WHERE formulaire.formulaireid = '$formulaireid'");
	$libNavigSupp = "$multilangue[reponses_formulaire] : $formulaire[nom]";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

// SUPPRIMER UNE VALEUR
if (isset($action) and $action == "supprimervaleur") {
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM formulaire_champ_valeur WHERE formulairechampid = '$formulairechampid' AND formulairechampvaleurid = '$formulairechampvaleurid'");
		$DB_site->query("DELETE FROM formulaire_champ_valeur_site WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
		$champvaleurs = $DB_site->query("SELECT * FROM formulaire_champ_valeur WHERE formulairechampid = '$formulairechampid' ORDER BY valeur");
		$i = 0;
		while ($champvaleur = $DB_site->fetch_array($champvaleurs)){
			$DB_site->query("UPDATE formulaire_champ_valeur SET valeur = '$i' WHERE formulairechampvaleurid = '$champvaleur[formulairechampvaleurid]'");
			++$i;
		}
		header("location: formulaires.php?action=modifier&formulaireid=$formulaireid");
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

// SUPPRIMER UN CHAMP
if (isset($action) and $action == "supprimerchamp") {
	if($admin_droit[$scriptcourant][suppression]){
		$champ = $DB_site->query_first("SELECT *
										FROM formulaire_champ
										INNER JOIN formulaire_champ_site
										USING(formulairechampid) WHERE formulairechampid = '$formulairechampid'");
		$DB_site->query("DELETE fcvs FROM formulaire_champ_valeur fcv
				INNER JOIN formulaire_champ_valeur_site fcvs
				USING(formulairechampvaleurid)
				WHERE formulairechampid = '$formulairechampid'");
		$DB_site->query("DELETE FROM formulaire_champ_valeur
				WHERE formulairechampid = '$formulairechampid'");
		$DB_site->query("DELETE FROM formulaire_champ_site WHERE formulairechampid = '$formulairechampid'");
		$DB_site->query("DELETE FROM formulaire_champ WHERE formulairechampid = '$formulairechampid'");
		$texteSuccess = $multilangue[le_champ]." <strong>$champ[nom]</strong> ".$multilangue[a_bien_ete_supprime];
		eval(charge_template($langue, $referencepage, "Success"));
		header("location: formulaires.php?action=modifier&formulaireid=$formulaireid");
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

//MODIFIER UN CHAMP (Enregistrement BDD)
if (isset($action) and $action == "modifierchamp2") {
	if($admin_droit[$scriptcourant][ecriture]){
		$nouveauchamp = 0;
		if($formulairechampid == ""){
			$DB_site->query("INSERT INTO formulaire_champ(formulairechampid, formulaireid) VALUES ('', '$formulaireid')");
			$formulairechampid = $DB_site->insert_id();
			$nouveauchamp = 1;
		}
		$champvaleurs = $DB_site->query("SELECT * FROM formulaire_champ_valeur WHERE formulairechampid = '$formulairechampid'");
		while ($champvaleur = $DB_site->fetch_array($champvaleurs)){
			$DB_site->query("DELETE FROM formulaire_champ_valeur_site WHERE formulairechampvaleurid	= '$formulairechampvaleurid'");
		}
		$DB_site->query("DELETE FROM formulaire_champ_valeur WHERE formulairechampid = '$formulairechampid'");
		switch ($_POST[type]){
			case "1":
					$DB_site->query("INSERT INTO formulaire_champ_valeur(formulairechampvaleurid, formulairechampid) VALUES ('', '$formulairechampid')");
					$formulairechampvaleurid = $DB_site->insert_id();
					$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid) VALUES ('$formulairechampvaleurid')");
					$cols = intval($_POST[cols]);
					$DB_site->query("UPDATE formulaire_champ_valeur SET libelle = 'cols',
									valeur = '$cols' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
					$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '1',
									description = 'Largeur' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
					$DB_site->query("INSERT INTO formulaire_champ_valeur(formulairechampvaleurid, formulairechampid) VALUES ('', '$formulairechampid')");
					$formulairechampvaleurid = $DB_site->insert_id();
					$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid) VALUES ('$formulairechampvaleurid')");
					$rows = intval($_POST[rows]);
					$DB_site->query("UPDATE formulaire_champ_valeur SET libelle = 'rows',
									valeur = '$rows' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
					$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '1',
									description = 'Hauteur' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
				break;
			case "2":
				$i = 0;
				$name = "valueboutonsRadio" . $i;
				while ($_POST[$name])
				{
					$DB_site->query("INSERT INTO formulaire_champ_valeur(formulairechampvaleurid, formulairechampid) VALUES ('', '$formulairechampid')");
					$formulairechampvaleurid = $DB_site->insert_id();
					$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid, siteid) VALUES ('$formulairechampvaleurid', '1')");
					$DB_site->query("UPDATE formulaire_champ_valeur SET libelle = 'value',
									valeur = '$i' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
					$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '1',
									description = '" . securiserSql($_POST[$name]) . "' WHERE formulairechampvaleurid = '$formulairechampvaleurid' AND siteid = '1'");
					$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
					while ($site = $DB_site->fetch_array($sites)){
						$name = "valueboutonsRadio" . $i . "site" . $site[siteid];
						$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid, siteid) VALUES ('$formulairechampvaleurid', '$site[siteid]')");
						$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '$site[siteid]',
										description = '" . securiserSql($_POST[$name]) . "' WHERE formulairechampvaleurid = '$formulairechampvaleurid' AND siteid = '$site[siteid]'");
						$name = "valueboutonsRadio" . $i . "site" . $site[siteid];
					}
					++$i;
					$name = "valueboutonsRadio" . $i;
				}
				break;
			case "3":
				$i = 0;
				$name = "valuecasesACocher" . $i;
				while ($_POST[$name])
				{
					$DB_site->query("INSERT INTO formulaire_champ_valeur(formulairechampvaleurid, formulairechampid) VALUES ('', '$formulairechampid')");
					$formulairechampvaleurid = $DB_site->insert_id();
					$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid, siteid) VALUES ('$formulairechampvaleurid', '1')");
					$DB_site->query("UPDATE formulaire_champ_valeur SET libelle = 'value',
									valeur = '$i' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
					$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '1',
									description = '" . securiserSql($_POST[$name]) . "' WHERE formulairechampvaleurid = '$formulairechampvaleurid' AND siteid = '1'");
					$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
					while ($site = $DB_site->fetch_array($sites)){
						$name = "valuecasesACocher" . $i . "site" . $site[siteid];
						$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid, siteid) VALUES ('$formulairechampvaleurid', '$site[siteid]')");
						$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '$site[siteid]',
										description = '" . securiserSql($_POST[$name]) . "' WHERE formulairechampvaleurid = '$formulairechampvaleurid' AND siteid = '$site[siteid]'");
						$name = "valuecasesACocher" . $i . "site" . $site[siteid];
					}
					++$i;
					$name = "valuecasesACocher" . $i;
				}
				break;
			case "4":
					$DB_site->query("INSERT INTO formulaire_champ_valeur(formulairechampvaleurid, formulairechampid) VALUES ('', '$formulairechampid')");
					$formulairechampvaleurid = $DB_site->insert_id();
					$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid) VALUES ('$formulairechampvaleurid')");
					$maxlength = intval($_POST[maxlength]);
					$DB_site->query("UPDATE formulaire_champ_valeur SET libelle = 'maxlength',
									valeur = '$maxlength' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
					$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '1',
									description = 'Longueur' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
					$DB_site->query("INSERT INTO formulaire_champ_valeur(formulairechampvaleurid, formulairechampid) VALUES ('', '$formulairechampid')");
					$formulairechampvaleurid = $DB_site->insert_id();
					$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid) VALUES ('$formulairechampvaleurid')");
					$size = intval($_POST[size]);
					$DB_site->query("UPDATE formulaire_champ_valeur SET libelle = 'size',
									valeur = '$size' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
					$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '1',
									description = 'Taille' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
				break;
			case "5":
				$i = 0;
				$name = "valuelisteASelectionUnique" . $i;
				while ($_POST[$name])
				{
					$DB_site->query("INSERT INTO formulaire_champ_valeur(formulairechampvaleurid, formulairechampid) VALUES ('', '$formulairechampid')");
					$formulairechampvaleurid = $DB_site->insert_id();
					$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid, siteid) VALUES ('$formulairechampvaleurid', '1')");
					$DB_site->query("UPDATE formulaire_champ_valeur SET libelle = 'value',
									valeur = '$i' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
					$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '1',
									description = '" . securiserSql($_POST[$name]) . "' WHERE formulairechampvaleurid = '$formulairechampvaleurid' AND siteid = '1'");
					$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
					while ($site = $DB_site->fetch_array($sites)){
						$name = "valuelisteASelectionUnique" . $i . "site" . $site[siteid];
						$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid, siteid) VALUES ('$formulairechampvaleurid', '$site[siteid]')");
						$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '$site[siteid]',
								description = '" . securiserSql($_POST[$name]) . "' WHERE formulairechampvaleurid = '$formulairechampvaleurid' AND siteid = '$site[siteid]'");
						$name = "valuelisteASelectionUnique" . $i . "site" . $site[siteid];
					}
					++$i;
					$name = "valuelisteASelectionUnique" . $i;
				}
				break;
			case "6":
				$i = 0;
				$name = "valuelisteASelectionsMultiples" . $i;
				while ($_POST[$name])
				{
					$DB_site->query("INSERT INTO formulaire_champ_valeur(formulairechampvaleurid, formulairechampid) VALUES ('', '$formulairechampid')");
					$formulairechampvaleurid = $DB_site->insert_id();
					$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid, siteid) VALUES ('$formulairechampvaleurid', '1')");
					$DB_site->query("UPDATE formulaire_champ_valeur SET libelle = 'value',
									valeur = '$i' WHERE formulairechampvaleurid = '$formulairechampvaleurid'");
					$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '1',
									description = '" . securiserSql($_POST[$name]) . "' WHERE formulairechampvaleurid = '$formulairechampvaleurid' AND siteid = '1'");
					$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
					while ($site = $DB_site->fetch_array($sites)){
						$name = "valuelisteASelectionsMultiples" . $i . "site" . $site[siteid];
						$DB_site->query("INSERT INTO formulaire_champ_valeur_site(formulairechampvaleurid, siteid) VALUES ('$formulairechampvaleurid', '$site[siteid]')");
						$DB_site->query("UPDATE formulaire_champ_valeur_site SET siteid = '$site[siteid]',
										description = '" . securiserSql($_POST[$name]) . "' WHERE formulairechampvaleurid = '$formulairechampvaleurid' AND siteid = '$site[siteid]'");
						$name = "valuelisteASelectionsMultiples" . $i . "site" . $site[siteid];
					}
					++$i;
					$name = "valuelisteASelectionsMultiples" . $i;
				}
				break;
		}
		if ($_POST[obligatoire] == 1)
			$obligatoire = 1;
		else
			$obligatoire = 0;
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			if($nouveauchamp){
				$DB_site->query("INSERT INTO formulaire_champ_site(formulairechampid, siteid) VALUES ('$formulairechampid', '$site[siteid]')");
			}
			$existe_site_champ_formulaire = $DB_site->query_first("SELECT * FROM formulaire_champ
															INNER JOIN formulaire_champ_site USING(formulairechampid)
															WHERE formulairechampid = '$formulairechampid'
															AND siteid = '$site[siteid]'");
			if($existe_site_champ_formulaire[formulairechampid] == ""){
				$DB_site->query("INSERT INTO formulaire_champ_site(formulairechampid, siteid) VALUES ('$formulairechampid','$site[siteid]')");
			}
			$libellesite = "libelle$site[siteid]";
			$DB_site->query("UPDATE formulaire_champ_site
							SET libelle = '" . addslashes($_POST[$libellesite]) . "'
							WHERE formulairechampid = '$formulairechampid'
							AND siteid = '$site[siteid]'");
		}
		$DB_site->query("UPDATE formulaire_champ SET nom = '" . addslashes($_POST[nom]) . "',
				type = '" . addslashes($_POST[type]) . "',
				obligatoire = '$obligatoire'
				WHERE formulairechampid = '$formulairechampid'");
		if($nouveauchamp){
			$texteSuccess = $multilangue[le_champ]." <strong>" . $_POST['nom'] . "</strong> ".$multilangue[a_bien_ete_cre];
		}else{
			$texteSuccess =$multilangue[le_champ]." <strong>" . $_POST['nom'] . "</strong> ".$multilangue[a_bien_ete_modifie];
		}
		eval(charge_template($langue, $referencepage, "Success"));
		header("location: formulaires.php?action=modifier&formulaireid=$formulaireid");
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

//AJOUTER OU MODIFIER UN CHAMP
if (isset($action) and $action == "modifierchamp") {
	if (isset($formulaireid)){
		$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
		if($formulairechampid != ""){
			$champ = $DB_site->query_first("SELECT *
											FROM formulaire_champ
											INNER JOIN formulaire_champ_site
											USING(formulairechampid) WHERE formulairechampid = '$formulairechampid' AND siteid = '1'");
			if ($champ[obligatoire] == 1)
				$checked = "checked";
			else
				$checked = "";
			$libNavigSupp = "$multilangue[modif_champ] : $champ[nom]";
		}else{
			$libNavigSupp = "$multilangue[ajt_champ]";
			$checked = "checked";
			$champ[type] = "1";
		}
		$largeur = "40";
		$hauteur = "6";
		$longueur = "30";
		$taille = "100";
		$types = $DB_site->query("SELECT * FROM formulaire_type_champ");
		$name = "";
		while ($type = $DB_site->fetch_array($types)){
			if ($champ[type] == $type[formulairetypechampid]){
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
					$values = $DB_site->query("SELECT * FROM formulaire_champ_valeur
												WHERE libelle = 'value' AND formulairechampid = '$formulairechampid'");
					$description = $DB_site->query_first("SELECT * FROM formulaire_champ_valeur
														INNER JOIN formulaire_champ_valeur_site USING(formulairechampvaleurid)
														WHERE libelle = 'value' AND valeur = '0'
														AND formulairechampid = '$formulairechampid' AND siteid = '1'");
					$first = $description[description];
					$DB_site->fetch_array($values);
					
					/////								
					/*$nom_template="Template".$referencepage."ModificationChampDefautBitAddvalue$name";
					echo $nom_template."<br>";
					${$nom_template}="";*/
					/////
					while ($DB_site->fetch_array($values))
					{
						$description = $DB_site->query_first("SELECT * FROM formulaire_champ_valeur
															INNER JOIN formulaire_champ_valeur_site USING(formulairechampvaleurid)
															WHERE libelle = 'value' AND valeur = '$i'
															AND formulairechampid = '$formulairechampid' AND siteid = '1'");
						eval(charge_template($langue, $referencepage, "ModificationChampDefautBitAddvalue$name"));
						++$i;	
					}
				}
			}else{
				$selected = "";
			}
			eval(charge_template($langue, $referencepage, "ListeTypesBit"));
		}
		eval(charge_template($langue, $referencepage, "ListeTypes"));
		$valeurs = $DB_site->query("SELECT *
									FROM formulaire_champ_valeur
									INNER JOIN formulaire_champ_valeur_site
									USING(formulairechampvaleurid) WHERE formulairechampid = '$formulairechampid' AND siteid = '1'");
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
		eval(charge_template($langue, $referencepage, "ModificationChampDefautBit"));
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");	
			
		while ($site = $DB_site->fetch_array($sites)){			
									
			$formulairechampsite = $DB_site->query_first("SELECT * from formulaire_champ
														INNER JOIN formulaire_champ_site USING(formulairechampid)
														WHERE formulairechampid = '$formulairechampid'
														AND siteid = '$site[siteid]'");
			if (in_array($champ[type], array("2", "3", "5", "6"))){
				$name = $tabid[$champ[type]] . "Site";

				switch($name){
					case "boutonsRadioSite":
						$TemplateFormulairesModificationChampDefautBitAddvalueboutonsRadioSite = "";
						break;
					case "casesACocherSite":
						$TemplateFormulairesModificationChampDefautBitAddvaluecasesACocherSite = "";
						break;
					case "listeASelectionUniqueSite":
						$TemplateFormulairesModificationChampDefautBitAddvaluelisteASelectionUniqueSite = "";
						break;
					case "listeASelectionsMultiplesSite":
						$TemplateFormulairesModificationChampDefautBitAddvaluelisteASelectionsMultiplesSite = "";
						break;
				}
				$i = 1;
				$values = $DB_site->query("SELECT * FROM formulaire_champ_valeur
										 WHERE libelle = 'value' AND formulairechampid = '$formulairechampid'");
				$description = $DB_site->query_first("SELECT * FROM formulaire_champ_valeur
													INNER JOIN formulaire_champ_valeur_site USING(formulairechampvaleurid)
													WHERE libelle = 'value' AND valeur = '0'
													AND formulairechampid = '$formulairechampid' AND siteid = '$site[siteid]'");
				$first = $description[description];
				$DB_site->fetch_array($values);
				while ($DB_site->fetch_array($values))
				{
					$description = $DB_site->query_first("SELECT * FROM formulaire_champ_valeur
														INNER JOIN formulaire_champ_valeur_site USING(formulairechampvaleurid)
														WHERE libelle = 'value' AND valeur = '$i'
														AND formulairechampid = '$formulairechampid' AND siteid = '$site[siteid]'");
					eval(charge_template($langue, $referencepage, "ModificationChampDefautBitAddvalue$name"));
					++$i;
				}
			}
			eval(charge_template($langue,$referencepage,"ModificationChampSiteBit"));
		}
		eval(charge_template($langue, $referencepage, "ModificationChamp"));
	}
}

if (isset($action) and $action == "copier") {
	if($admin_droit[$scriptcourant][ecriture]){
		$formulaire = $DB_site->query_first("SELECT * FROM formulaire 
											INNER JOIN formulaire_site 
											USING(formulaireid) 
											WHERE formulaireid = '$formulaireid'");
		if ($formulaire[formulaireid]) {
			$DB_site->query("INSERT INTO formulaire (actif, antiforcebrute)
							VALUES ('$formulaire[actif]', '$formulaire[antiforcebrute]')");
			$formulaireidNew = $DB_site->insert_id();
			$sites = $DB_site->query("SELECT * FROM site");
			while ($site = $DB_site->fetch_array($sites)){
				$formulairesite = $DB_site->query_first("SELECT * FROM formulaire 
														INNER JOIN formulaire_site 
														USING(formulaireid) 
														WHERE formulaireid = '$formulaireid' AND siteid = '$site[siteid]'");
				if ($formulairesite[siteid]){
					$DB_site->query("INSERT INTO formulaire_site (formulaireid, nom, siteid)
									VALUES ('$formulaireidNew', '$formulairesite[nom] (Copie)', '$formulairesite[siteid]')");
				}
			}
			$champs = $DB_site->query("SELECT DISTINCT(formulairechampid), formulaire_champ.* 
									  FROM formulaire_champ 
									  INNER JOIN formulaire_champ_site 
									  USING(formulairechampid) WHERE formulaireid = '$formulaireid'");
			while ($champ = $DB_site->fetch_array($champs)) {
				$DB_site->query("INSERT INTO formulaire_champ (formulaireid, nom, type, obligatoire, position)
								VALUES ($formulaireidNew, '$champ[nom]', '$champ[type]', '$champ[obligatoire]', '$champ[position]')");
				$formulairechampidNew = $DB_site->insert_id();
				$sites = $DB_site->query("SELECT * FROM site");
				while ($site = $DB_site->fetch_array($sites)){
					$champsite = $DB_site->query_first("SELECT * FROM formulaire_champ 
														INNER JOIN formulaire_champ_site 
														USING(formulairechampid) 
														WHERE formulaireid = '$formulaireid' 
														AND siteid = '$site[siteid]' 
														AND formulairechampid = '$champ[formulairechampid]'");
					if ($champsite[siteid]){
						$DB_site->query("INSERT INTO formulaire_champ_site (formulairechampid, libelle, siteid)
										VALUES ('$formulairechampidNew', '" . addslashes($champsite[libelle]) . "', '$champsite[siteid]')");
					}
				}
				$valeurs = $DB_site->query("SELECT DISTINCT(formulairechampvaleurid), formulaire_champ_valeur.* FROM formulaire_champ_valeur 
											INNER JOIN formulaire_champ_valeur_site 
											USING(formulairechampvaleurid) 
											WHERE formulairechampid = '$champ[formulairechampid]'");
				while ($valeur = $DB_site->fetch_array($valeurs)) {
					$DB_site->query("INSERT INTO formulaire_champ_valeur (formulairechampid, libelle, valeur)
									VALUES ('$formulairechampidNew', '".addslashes($valeur[libelle])."', '$valeur[valeur]')");
					$formulairechampvaleuridNew = $DB_site->insert_id();
					$sites = $DB_site->query("SELECT * FROM site");
					while ($site = $DB_site->fetch_array($sites)){
						$champvaleursite = $DB_site->query_first("SELECT * FROM formulaire_champ_valeur
																INNER JOIN formulaire_champ_valeur_site
																USING(formulairechampvaleurid)
																WHERE siteid = '$site[siteid]' 
																AND formulairechampid = '$champ[formulairechampid]' 
																AND formulairechampvaleurid = '$valeur[formulairechampvaleurid]'");
						if ($champvaleursite[siteid]) {
							$DB_site->query("INSERT INTO formulaire_champ_valeur_site (formulairechampvaleurid, description, siteid)
											VALUES ('$formulairechampvaleuridNew', '".addslashes($champvaleursite[description])."', '$champvaleursite[siteid]')");
						}
					}
				}
			}
		}
		header('location: formulaires.php');
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

// MODIFIER UN FORMULAIRE (Enregistrement BDD)
if (isset($action) and $action == "modifier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($formulaireid == ""){
			$DB_site->query("INSERT INTO formulaire(formulaireid, actif, antiforcebrute) VALUES ('', '1', '1')");
			$formulaireid = $DB_site->insert_id();
			$nouveauformulaire = 1;
		}else{
			if ($_POST[antiforcebrute] == 1)
				$DB_site->query("UPDATE formulaire SET antiforcebrute = '1' WHERE formulaireid = '$formulaireid'");
			else
				$DB_site->query("UPDATE formulaire SET antiforcebrute = '0' WHERE formulaireid = '$formulaireid'");
		}
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			if($nouveauformulaire){
				$DB_site->query("INSERT INTO formulaire_site(formulaireid, siteid) VALUES ('$formulaireid', '$site[siteid]')");
			}
			$existe_site_formulaire = $DB_site->query_first("SELECT * FROM formulaire
															INNER JOIN formulaire_site USING(formulaireid)
															WHERE formulaireid = '$formulaireid'
															AND siteid='$site[siteid]'");
			if($existe_site_formulaire[formulaireid] == ""){
				$DB_site->query("INSERT INTO formulaire_site (formulaireid,siteid) VALUES ('$formulaireid','$site[siteid]')");
			}
			$nomsite = "nom$site[siteid]";
			$DB_site->query("UPDATE formulaire_site SET nom = '" . addslashes($_POST[$nomsite]) . "'
							WHERE formulaireid = '$formulaireid'
							AND siteid = '$site[siteid]'");
		}
		if($nouveauformulaire){
			$texteSuccess = ".$multilangue[le_formulaire]<strong>" . securiserSql($_POST[nom1], "html") . "</strong> ".$multilangue[a_bien_ete_cre];
		}else{
			$texteSuccess = ".$multilangue[le_formulaire]<strong>" . securiserSql($_POST[nom1], "html") . "</strong> ".$multilangue[a_bien_ete_modifie];
		}
		eval(charge_template($langue,$referencepage,"Success"));
		header('location: formulaires.php');
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

// SUPPRIMER UN FORMULAIRE
if (isset($action) and $action == "supprimer") {
	if($admin_droit[$scriptcourant][suppression]){
		$formulaire = $DB_site->query_first("SELECT * FROM formulaire
											INNER JOIN formulaire_site 
											USING(formulaireid) 
											WHERE formulaireid = '$formulaireid'");
		if ($formulaire[formulaireid]){
			$DB_site->query("DELETE FROM formulaire WHERE formulaireid = '$formulaireid'");
			$DB_site->query("DELETE FROM formulaire_site WHERE formulaireid = '$formulaireid'");
			$champs = $DB_site->query("SELECT *
									FROM formulaire_champ
									INNER JOIN formulaire_champ_site
									USING(formulairechampid) WHERE formulaireid = '$formulaireid'");
			while ($champ = $DB_site->fetch_array($champs)) {
				$DB_site->query("DELETE fcvs FROM formulaire_champ_valeur fcv
								INNER JOIN formulaire_champ_valeur_site fcvs
								USING(formulairechampvaleurid)
								WHERE formulairechampid = '$champ[formulairechampid]'");
				$DB_site->query("DELETE FROM formulaire_champ_valeur
								WHERE formulairechampid = '$champ[formulairechampid]'");
			}
			$DB_site->query("DELETE fcs FROM formulaire_champ fc
							INNER JOIN formulaire_champ_site fcs USING(formulairechampid) WHERE formulaireid = '$formulaireid'");
			$DB_site->query("DELETE FROM formulaire_champ WHERE formulaireid = '$formulaireid'");
			$texteSuccess = $multilangue[le_statut]." <strong>$formulaire[nom]</strong> ".$multilangue[a_bien_ete_supprime];
			eval(charge_template($langue, $referencepage, "Success"));
		}else{
			$texteErreur = $multilangue[form_existe_plus];
			eval(charge_template($langue, $referencepage, "Erreur"));
		}
		header('location: formulaires.php');
		exit();
	}else{
		header('location: formulaires.php?erreurdroits=1');	
		exit();
	}
}

// AJOUTER OU MODIFIER UN FORMULAIRE
if (isset($action) and $action == "modifier") {
	$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
	if(isset($formulaireid)){
		$formulaire = $DB_site->query_first("SELECT * from formulaire
											INNER JOIN formulaire_site USING(formulaireid)
											WHERE formulaireid = '$formulaireid'
											AND siteid = '1'");
				if ($formulaire[antiforcebrute] == 1)
					$checked = "checked";
				else
					$checked = "";
				eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
				$formulairechamps = $DB_site->query("SELECT *
													FROM formulaire_champ
													INNER JOIN formulaire_champ_site
													USING(formulairechampid) WHERE formulaireid = '$formulaireid' 
													AND siteid = '1'
													ORDER BY position");
				while ($formulairechamp = $DB_site->fetch_array($formulairechamps)){
					$typechamp = $DB_site->query_first("SELECT * FROM formulaire_type_champ WHERE formulairetypechampid = '$formulairechamp[type]'");
					if ($formulairechamp[obligatoire] == 1)
						$obligatoire = $multilangue[oui];
					else
						$obligatoire = $multilangue[non];
					$TemplateFormulairesDetailChampTitre = "";
					$TemplateFormulairesDetailChampTitreSupprimer = "";
					$TemplateFormulairesDetailChamp = "";
					$TemplateFormulairesDetailChampSupprimer = "";
					$champvaleurs = $DB_site->query("SELECT * FROM formulaire_champ_valeur
													INNER JOIN formulaire_champ_valeur_site
													USING(formulairechampvaleurid)
													WHERE formulairechampid = '$formulairechamp[formulairechampid]' AND siteid = '1' ORDER BY libelle, valeur");
					$count = $DB_site->query_first("SELECT COUNT(*) count FROM formulaire_champ_valeur WHERE formulairechampid = '$formulairechamp[formulairechampid]'");
					if (!in_array($formulairechamp[type], array("2", "3", "5", "6")) && $count[count] <= 2)
						eval(charge_template($langue,$referencepage,"DetailChampTitre"));
					else
						eval(charge_template($langue,$referencepage,"DetailChampTitreSupprimer"));
					while ($champvaleur = $DB_site->fetch_array($champvaleurs)){
						if (!in_array($formulairechamp[type], array("2", "3", "5", "6")) && $count[count] <= 2)
							eval(charge_template($langue,$referencepage,"DetailChamp"));
						else
							eval(charge_template($langue,$referencepage,"DetailChampSupprimer"));
					}
					eval(charge_template($langue,$referencepage,"ListeChampsBit"));
				}
				$champ = $DB_site->query_first("SELECT count(*)
											FROM formulaire_champ WHERE formulaireid = '$formulaireid'");
				if ($champ[0] > 0)				
					eval(charge_template($langue,$referencepage,"ListeChamps"));
				eval(charge_template($langue,$referencepage,"Champs"));
				$libNavigSupp = "$multilangue[modif_formulaire] : $formulaire[nom]";
				eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{
		$checked = "checked";
		eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
		$libNavigSupp="$multilangue[nouveau_formulaire]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}
	$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
	while ($site = $DB_site->fetch_array($sites)){
	$formulairesite = $DB_site->query_first("SELECT * from formulaire
			INNER JOIN formulaire_site USING(formulaireid)
			WHERE formulaireid = '$formulaireid'
			AND siteid = '$site[siteid]'");
			eval(charge_template($langue,$referencepage,"ModificationSiteBit"));
	}
	eval(charge_template($langue,$referencepage,"Modification"));
}

//LISTER LES FORMULAIRES
if (!isset($action) or $action == ""){
	$formulaires = $DB_site->query("SELECT * FROM formulaire
								INNER JOIN formulaire_site
								ON formulaire.formulaireid = formulaire_site.formulaireid
								WHERE siteid = '1' ORDER BY nom");
	while ($formulaire = $DB_site->fetch_array($formulaires))
	{
		$TemplateFormulairesListeBitModifier = "";
		$TemplateFormulairesListeBitCopier = "";
		$TemplateFormulairesListeBitSupprimer = "";
		$TemplateFormulairesListeBitReponses = "";
		if ($formulaire[actif] == 1){
			$color_aff = "vert";
			$color2_aff = "green";
			$ico_aff = "fa-check-square-o";
			$tooltip_visible = $multilangue[desactiver];
		}else{
			$color_aff = "rouge";
			$color2_aff = "red";
			$ico_aff = "fa-square-o";
			$tooltip_visible = $multilangue[activer];
		}
		$count = $DB_site->query_first("SELECT COUNT(*) count FROM formulaire_reponse WHERE formulaireid = '$formulaire[formulaireid]'");
		if (!$count[count]){
			eval(charge_template($langue, $referencepage, "ListeBitModifier"));
			eval(charge_template($langue, $referencepage, "ListeBitCopier"));
			eval(charge_template($langue, $referencepage, "ListeBitSupprimer"));
		}else{
			eval(charge_template($langue, $referencepage, "ListeBitCopier"));
			eval(charge_template($langue, $referencepage, "ListeBitReponses"));
		}
		eval(charge_template($langue, $referencepage, "ListeBit"));
	}
	eval(charge_template($langue, $referencepage, "Liste"));
	$libNavigSupp = "$multilangue[liste_formulaires]";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>