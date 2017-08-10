<?php
include "./includes/header.php";

$referencepage = "fournisseurs";
$pagetitle = "Gestion des fournisseurs - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//MODIFIER UN CONTACT (Enregistrement BDD)
if (isset($action) and $action == "modifiercontact2") {
	if($admin_droit[$scriptcourant][ecriture]){
		$erreur = "";
		$nouveaucontact = 0;
		
		if($fournisseurcontactid == ""){
			$DB_site->query("INSERT INTO fournisseurcontact(fournisseurcontactid) VALUES ('')");
			$fournisseurcontactid = $DB_site->insert_id();
			$nouveaucontact = 1;
		}
		
		$sql = "UPDATE fournisseurcontact SET fournisseurid = '" . securiserSql($_POST['fournisseurid']) . "',
				nom = '" . securiserSql($_POST['nom']) . "',
				prenom = '" . securiserSql($_POST['prenom']) . "',
				fonction = '" . securiserSql($_POST['fonction']) . "',
				mail = '" . securiserSql($_POST['mail']) . "',
				adresse = '" . securiserSql($_POST['adresse']) . "',
				telephone = '" . securiserSql($_POST['telephone']) . "'
				WHERE fournisseurcontactid = '$fournisseurcontactid'";
		
		$DB_site->query($sql);
		
		if($nouveaucontact){
			$texteSuccess =$multilangue[le_contact]."<strong>" . $_POST['nom'] . " " . $_POST['prenom'] . "</strong> ".$multilangue[a_bien_ete_cre];
		}else{
			$texteSuccess =$multilangue[le_contact]."<strong>" . $_POST['nom'] . " " . $_POST['prenom'] . "</strong> ".$multilangue[a_bien_ete_modifie];
		}
		
		eval(charge_template($langue, $referencepage, "Success"));
		header("location: fournisseurs.php?action=modifier&fournisseurid=$fournisseurid");
	}else{
		header('location: fournisseurs.php?erreurdroits=1');	
	}
}

//AJOUTER OU MODIFIER UN CONTACT
if (isset($action) and $action == "modifiercontact") {
	if (isset($fournisseurid)){
		if(isset($fournisseurcontactid)){
			$ct = $DB_site->query_first("SELECT * FROM fournisseurcontact
										WHERE fournisseurcontactid = '$fournisseurcontactid'");
			$texte_entete="$multilangue[modif_contact] : $ct[nom] $ct[prenom]";			
		}else{
			$texte_entete = "$multilangue[ajt_contact]";
		}
		eval(charge_template($langue, $referencepage, "ModificationContactDefautBit"));
		eval(charge_template($langue, $referencepage, "ContactModification"));
	}
}

// SUPPRIMER UN CONTACT
if (isset($action) and $action == "supprimercontact") {
	if($admin_droit[$scriptcourant][suppression]){
		$contact = $DB_site->query_first("SELECT * FROM fournisseurcontact WHERE fournisseurcontactid = '$fournisseurcontactid'");
		if ($contact[fournisseurcontactid]){
			$DB_site->query("DELETE FROM fournisseurcontact WHERE fournisseurcontactid = '$fournisseurcontactid'");
			$texteSuccess = $multilangue[le_contact]." <strong>$contact[nom] $contact[prenom]</strong> ".$multilangue[a_bien_ete_supprime];
			eval(charge_template($langue, $referencepage, "Success"));
		}else{
			$texteErreur = $multilangue[le_contact_n_existe_plus];
			eval(charge_template($langue, $referencepage, "Erreur"));
		}
		header("location: fournisseurs.php?action=modifier&fournisseurid=$fournisseurid");
	}else{
		header('location: fournisseurs.php?erreurdroits=1');	
	}
}

// MODIFIER UN FOURNISSEUR (Enregistrement BDD)
if (isset($action) and $action == "modifier2") {
	if($admin_droit[$scriptcourant][ecriture]){
		$erreur = "";
		$nouveaufournisseur = 0;
		if($fournisseurid == ""){
			$DB_site->query("INSERT INTO fournisseur(fournisseurid) VALUES ('')");
			$fournisseurid = $DB_site->insert_id();
			$nouveaufournisseur = 1;
		}
		$sql = "UPDATE fournisseur SET libelle = '" . securiserSql($_POST['libelle']) . "',
				raisonsociale = '" . securiserSql($_POST['raisonsociale']) . "',
				contact = '" . securiserSql($_POST['contact']) . "',
				adresse = '" . securiserSql($_POST['adresse']) . "',
				codepostal = '" . securiserSql($_POST['codepostal']) . "',
				ville = '" . securiserSql($_POST['ville']) . "',
				telephone = '" . securiserSql($_POST['telephone']) . "',
				mail = '" . securiserSql($_POST['mail']) . "',
				mail2 = '" . securiserSql($_POST['mail2']) . "',
				marque = '" . securiserSql($_POST['marque']) . "',
				commentaire = '" . securiserSql($_POST['commentaire']) . "',
				conditions = '" . securiserSql($_POST['conditions']) . "',
				delais = '" . securiserSql($_POST['delais']) . "'
				WHERE fournisseurid = '$fournisseurid'";
		$DB_site->query($sql);
		if($nouveaufournisseur){
			$texteSuccess = $multilangue[le_fournisseur]." <strong>" . $_POST['libelle'] . "</strong> ".$multilangue[a_bien_ete_cre];
		}else{
			$texteSuccess = $multilangue[le_fournisseur]." <strong>" . $_POST['libelle'] . "</strong> ".$multilangue[a_bien_ete_modifie];
		}
		eval(charge_template($langue, $referencepage, "Success"));
		header('location: fournisseurs.php');
	}else{
		header('location: fournisseurs.php?erreurdroits=1');	
	}
}

//AJOUTER OU MOFIFIER UN FOURNISSEUR
if (isset($action) and $action == "modifier") {

	if(isset($fournisseurid)){
		$fd = $DB_site->query_first("SELECT * FROM fournisseur
				WHERE fournisseurid = '$fournisseurid'");
		$texte_entete = "$multilangue[modif_fournisseur] : $fd[libelle]";
		eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
		$libNavigSupp = "$multilangue[modif_fournisseur] : $fd[libelle]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{
	$texte_entete = "$multilangue[ajt_fournisseur]";
	eval(charge_template($langue, $referencepage, "ModificationDefautBit"));
	$libNavigSupp = "$multilangue[ajt_fournisseur]";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	}

	if ($action2 == "supprimage")
		$fd=$DB_site->query_first("SELECT * FROM fournisseur WHERE fournisseurid = '$fournisseurid'");
	
	$contacts = $DB_site->query("SELECT * FROM fournisseurcontact 
								WHERE fournisseurcontact.fournisseurid = '$fournisseurid' ORDER BY nom");
	$contact_i = 0;
	while ($contact = $DB_site->fetch_array($contacts))
	{
		$contact_i++;
		eval(charge_template($langue, $referencepage, "ListeContactBit"));
	}	
	if ($contact_i)
		eval(charge_template($langue, $referencepage, "ListeContact"));
	if(isset($fournisseurid))
		eval(charge_template($langue,$referencepage,"Contact"));
	eval(charge_template($langue, $referencepage, "Modification"));
}

// SUPPRIMER UN FOURNISSEUR
if (isset($action) and $action == "supprimer2") {
	if($admin_droit[$scriptcourant][suppression]){
		$fournisseur = $DB_site->query_first("SELECT * FROM fournisseur WHERE fournisseurid = '$fournisseurid'");
		if ($fournisseur[fournisseurid]){
			$DB_site->query("DELETE FROM fournisseur WHERE fournisseurid = '$fournisseurid'");
			$DB_site->query("DELETE FROM fournisseurcontact WHERE fournisseurid = '$fournisseurid'");
			$texteSuccess = $multilangue[le_fournisseur]." <strong>$fournisseur[libelle]</strong> ".$multilangue[a_bien_ete_supprime];
			eval(charge_template($langue, $referencepage, "Success"));
		}else{
			$texteErreur =$multilangue[le_fournisseur_n_existe_plus];
			eval(charge_template($langue, $referencepage, "Erreur"));
		}
		header('location: fournisseurs.php');
	}else{
		header('location: fournisseurs.php?erreurdroits=1');	
	}
}

//LISTER LES FOURNISSEURS
if (!isset($action) or $action == ""){
	$fournisseurs=$DB_site->query("SELECT * FROM fournisseur
								ORDER BY fournisseur.libelle");
	$nb_fournisseurs = $DB_site->num_rows($fournisseurs);
	while ($fournisseur = $DB_site->fetch_array($fournisseurs))
	{
		eval(charge_template($langue, $referencepage, "ListeBit"));
	}
	eval(charge_template($langue, $referencepage, "Liste"));
	$libNavigSupp = "$multilangue[liste_fournisseurs]";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex = "Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>