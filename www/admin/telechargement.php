<?php
include "./includes/header.php";

$referencepage="telechargement";
$pagetitle = "Page de téléchargement - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) && $action == "desactiver"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE parametre SET valeur = '0' WHERE parametre = 'pagedocuments_active'");
		header('location: telechargement.php');
	}else{
		header('location: telechargement.php?erreurdroits=1');	
	}
}

if (isset($action) && $action == "activer"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE parametre SET valeur = '1' WHERE parametre = 'pagedocuments_active'");
		header('location: telechargement.php');
	}else{
		header('location: telechargement.php?erreurdroits=1');	
	}
}

if (isset($action) && $action=="visible"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($pastille == "vert")
			$cacher = 0 ;
		else
			$cacher = 1 ;
		$DB_site->query("UPDATE document_site SET actif = '$cacher' WHERE documentid = '$documentid'");
		header('location: telechargement.php');
	}else{
		header('location: telechargement.php?erreurdroits=1');	
	}
}

//MODIFIER UN DOCUMENT (Enregistrement BDD)
if (isset($action) and $action == "modifier2") {
	if($admin_droit[$scriptcourant][ecriture]){
		$texteErreur = "";
		if ($_FILES['doc']['name'] != "" || $documentid != "") {
				if ($_FILES['doc']['error'] == 1) {
					$texteErreur .= "$multilangue[erreur] : $multilangue[erreur_taille_serveur_fichier]";
				} elseif ($_FILES['doc']['error'] == 2) {
					$texteErreur .= "$multilangue[erreur] : $multilangue[erreur_inconnue]";
				} elseif ($_FILES['doc']['error'] == 3) {
					$texteErreur .= "$multilangue[erreur] : $multilangue[erreur_telecharg_partiel_fichier]";
				} elseif ($_FILES['doc']['error'] == 4 && $documentid == "") {
					$texteErreur .= "$multilangue[erreur] : $multilangue[erreur_aucun_fichier_telecharg]";
				} elseif ($_FILES['doc']['type'] == "application/x-msdos-program" or $_FILES['doc']['type'] == "application/x-msdownload") {
					$texteErreur .= "$multilangue[erreur] : $multilangue[erreur_type_fichier]";
				} else {
					$datedebut = securiserSql($_POST["datedebut"]);
					$datefin = securiserSql($_POST["datefin"]);
					if ($datedebut) {
						list($jour, $mois, $annee) = explode( '/', $datedebut);
						$datedebut = mktime(0, 0, 0, $mois, $jour, $annee);
					} else {
						$datedebut = 0;
					}
					if ($datefin) {
						list($jour, $mois, $annee) = explode( '/', $datefin);
						$datefin = mktime(0, 0, 0, $mois, $jour, $annee);
					} else {
						$datefin = 0;
					}
					
					$nouveaudocument = 0;
					
					if ($documentid == ""){
						$DB_site->query("INSERT INTO document(documentid) VALUES ('')");
						$documentid = $DB_site->insert_id();
						$nouveaudocument = 1;
						$file = define_extention($_FILES['doc']['name']);
					}else{
						$file = $DB_site->query_first("SELECT extension FROM document WHERE documentid = '$documentid'");
						$file = $file[extension];	
					}
					
					$sites = $DB_site->query("SELECT * FROM site");
					while ($site = $DB_site->fetch_array($sites)){
					
						if($nouveaudocument){
							$DB_site->query("INSERT INTO document_site(documentid, siteid) VALUES ('$documentid', '$site[siteid]')");
						}
					
						$existe_site_document = $DB_site->query_first("SELECT * FROM document
								INNER JOIN document_site USING(documentid)
								WHERE documentid = '$documentid'
								AND siteid = '$site[siteid]'");
					
						if($existe_site_document[documentid] == ""){
							$DB_site->query("INSERT INTO document_site (documentid, siteid) VALUES ('$documentid', '$site[siteid]')");
						}
						
						$sql = "UPDATE document SET extension = '$file',
						datedebut = '$datedebut',
						datefin = '$datefin'
						WHERE documentid = '$documentid'";
							
						$DB_site->query($sql);
					
						$sql = "UPDATE document_site SET nom = '" . securiserSql($_POST["nom$site[siteid]"]) . "', actif = '1'
						WHERE documentid = '$documentid' AND siteid = '$site[siteid]'";
					
						$DB_site->query($sql);
					}
					
					if ($_FILES['doc']['name'] != ""){
						$nom_fichier = $rootpath."configurations/$host/images/docs/" . $documentid . "." . $file;
						copier_fichier($nom_fichier, "doc");
					}elseif ($documentid == ""){
						$texteErreur .= "$multilangue[erreur] : $multilangue[erreur_aucun_fichier_telecharg]";
					}
				}
			}elseif ($documentid == ""){
				$texteErreur .= "$multilangue[erreur] : $multilangue[erreur_aucun_fichier_telecharg]";
			}
			
			if ($texteErreur == "")
			{
				if ($nouveaudocument){
					$texteSuccess = $multilangue[le_document]." <strong>" . $_POST['nom1'] . "</strong> ".$multilangue[a_bien_ete_cree];
				}else{
					$texteSuccess = $multilangue[le_document]." <strong>" . $_POST['nom1'] . "</strong> ".$multilangue[a_bien_ete_modifie];
				}
				eval(charge_template($langue, $referencepage, "Success"));
				header('location: telechargement.php');
			}else{
				eval(charge_template($langue, $referencepage, "Erreur"));
				$action = "modifier";
			}
	}else{
		header('location: telechargement.php?erreurdroits=1');	
	}
}

//AJOUTER OU MODIFIER UN DOCUMENT
if (isset($action) and $action == "modifier"){
	if(isset($documentid)){
		$document = $DB_site->query_first("SELECT * FROM document_site
				INNER JOIN document
				ON document_site.documentid = document.documentid
				WHERE siteid = '1' AND document.documentid = '$documentid'");
		$texte_entete = "$multilangue[donnees] <i>".$tabsites[1][libelle]."</i>";
		if ($document[datedebut] != 0)
			$document[datedebut] = date("d/m/Y", $document[datedebut]);
		if ($document[datefin] != 0)
			$document[datefin] = date("d/m/Y", $document[datefin]);
		eval(charge_template($langue,$referencepage,"ModificationDefautBitApercu"));
		eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
		$libNavigSupp = "$multilangue[modif_document] : $document[nom]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{		
		$texte_entete = "$multilangue[donnees] <i>".$tabsites[1][libelle]."</i>";
		eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
		$libNavigSupp = "$multilangue[ajt_document]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}

	$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
	while($site = $DB_site->fetch_array($sites)){
		$documentsite = $DB_site->query_first("SELECT * FROM document
				INNER JOIN document_site USING(documentid)
				WHERE documentid = '$documentid'
				AND siteid = '$site[siteid]'");
		eval(charge_template($langue,$referencepage,"ModificationSiteBit"));
	}
	eval(charge_template($langue, $referencepage, "Modification"));
}

// SUPPRIMER UN DOCUMENT
if (isset($action) and $action == "supprimer") {
	if($admin_droit[$scriptcourant][suppression]){
		$document = $DB_site->query_first("SELECT * FROM document
				INNER JOIN document_site USING(documentid)
				WHERE documentid = '$documentid'
				ORDER BY position");
				if ($document[documentid] != ""){
						$rq_positions_suivantes = $DB_site->query("SELECT documentid, position FROM document WHERE position > $document[position]" );
						while ($rs_positions_suivantes=$DB_site->fetch_array($rq_positions_suivantes)) {
							$position_temp = $rs_positions_suivantes[position] - 1;
						$DB_site->query("UPDATE document SET position = '$position_temp' WHERE documentid = '$rs_positions_suivantes[documentid]'");
						}
						$extension = $DB_site->query_first("SELECT extension FROM document WHERE documentid = '$documentid'") ;
						$path = $rootpath . "configurations/$host/images/docs";
						$dossier = opendir($path);
						while ($fichier = readdir($dossier)) {
							if ($fichier == "$documentid.$extension[0]")
								unlink($path."/".$fichier);
						}
						$DB_site->query("DELETE FROM document WHERE documentid = '$documentid'");
						$DB_site->query("DELETE FROM document_site WHERE documentid = '$documentid'");
						$texteSuccess = $multilangue[le_document]." <strong>$document[nom]</strong> ".$multilangue[a_bien_ete_supprime];
						eval(charge_template($langue,$referencepage,"Success"));
		}else{
			$texteErreur = $multilangue[le_document_n_existe_plus];
			eval(charge_template($langue,$referencepage,"Erreur"));
		}
		header('location: telechargement.php');
	}else{
		header('location: telechargement.php?erreurdroits=1');	
	}
}

if (!isset($action) or $action == ""){
	$parametre = $DB_site->query_first("SELECT * FROM parametre WHERE parametre = 'pagedocuments_active'");
	if ($parametre[valeur] == '1'){
		$texteSuccess = "$multilangue[visibilite_page_telechargement] $multilangue[active]. <a class=\"alert-link\" href=\"telechargement.php?action=desactiver\">$multilangue[desactiver]</a>";
		eval(charge_template($langue, $referencepage, "SuccessFixe"));
	}else{
		$texteErreur = "$multilangue[visibilite_page_telechargement] $multilangue[inactive]. <a class=\"alert-link\" href=\"telechargement.php?action=activer\">$multilangue[activer]</a>";
		eval(charge_template($langue, $referencepage, "ErreurFixe"));
	}
	$documents = $DB_site->query("SELECT * FROM document_site
										INNER JOIN document
										ON document_site.documentid = document.documentid
										WHERE siteid = '1' ORDER BY document.position");

	while ($document = $DB_site->fetch_array($documents)){
		if ($document[datedebut] == "0")
			$document[datedebut] = $multilangue[aucune];
		else
			$document[datedebut] = date("d/m/Y", $document[datedebut]);
		if ($document[datefin] == "0")
			$document[datefin] = $multilangue[aucune];
		else
			$document[datefin] = date("d/m/Y", $document[datefin]);
		if ($document[actif] == 1){
			$color_aff = "vert";
			$color2_aff = "green";
			$ico_aff = "fa-check-square-o";
			$tooltip_visible=$multilangue[passer_invisible];
		}else{
			$color_aff = "rouge";
			$color2_aff = "red";
			$ico_aff = "fa-square-o";
			$tooltip_visible=$multilangue[passer_visible];
		}		
		eval(charge_template($langue, $referencepage, "ListeBit"));
	}
	eval(charge_template($langue, $referencepage, "Liste"));
	$libNavigSupp = "$multilangue[liste_documents]";
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