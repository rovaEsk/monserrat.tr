<?php
@ini_set('memory_limit','512M');

include "./includes/header.php";

$referencepage="caracteristiques";
$pagetitle = "Gestion des caractéristiques - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($succes)){
	switch ($succes){
		case 1:
			$caractval = $DB_site->query_first("SELECT * FROM caracteristiquevaleur
												INNER JOIN caracteristiquevaleur_site USING(caractvalid)
												WHERE caractvalid = '$caractvalid'
												AND siteid='1'");
			$texteSuccess = "$multilangue[la_valeur] <strong>" . $caractval[libelle] . "</strong> $multilangue[a_bien_ete_cree]";
		break;
		case 2:
			$caractval = $DB_site->query_first("SELECT * FROM caracteristiquevaleur
												INNER JOIN caracteristiquevaleur_site USING(caractvalid)
												WHERE caractvalid = '$caractvalid'
												AND siteid='1'");
			$texteSuccess = "$multilangue[la_valeur] <strong>" . $caractval[libelle] . "$multilangue[a_bien_ete_modifiee]";
		break;
		case 3:
			$texteSuccess = "$multilangue[la_valeur_a_bien_ete_supprimee]";
		break;
		case 4:
			$caract = $DB_site->query_first("SELECT * FROM caracteristique_site
												INNER JOIN caracteristique
												ON caracteristique_site.caractid = caracteristique.caractid
												WHERE siteid = '1' AND caracteristique.caractid = '$caractid'");
			$texteSuccess = "$multilangue[la_caracteristique] <strong>" . $caract[libelle] . "</strong> $multilangue[a_bien_ete_cree]";
		break;
		case 5:
			$caract = $DB_site->query_first("SELECT * FROM caracteristique_site
												INNER JOIN caracteristique
												ON caracteristique_site.caractid = caracteristique.caractid
												WHERE siteid = '1' AND caracteristique.caractid = '$caractid'");
			$texteSuccess = "$multilangue[la_caracteristique] <strong>" . $caract[libelle] . "</strong> $multilangue[a_bien_ete_modifiee]";
		break;
		case 6:
			$texteSuccess = "$multilangue[la_caracteristique_supprimee]";
		break;
		
	}
	eval(charge_template($langue, $referencepage, "Success"));
}

if(isset($erreur)){
	switch ($erreur){
		case 1:
			$texteErreur = "$multilangue[caracteristique_existe_plus]";
		break;
		case 2:
			$texteErreur = "";
		break;
		case 3:
			$texteErreur = "";
		break;
	}
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//MODIFIER UNE VALEUR (Enregistrement BDD)
if (isset($action) and $action == "modifiervaleur2") {
	if($admin_droit[$scriptcourant][ecriture]){
		$erreur = "";
		$nouvellevaleur = 0;
		
		if($caractid == ""){
			$lastPosition = $DB_site->query_first("SELECT MAX(position) FROM caracteristique");
			$position = $lastPosition[0] + 1;
			$DB_site->query("INSERT INTO caracteristique(caractid, position) VALUES ('', '$position')");
			$caractid = $DB_site->insert_id();
			$nouvellecaracteristique = 1;
		}
		
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
		
			if($nouvellevaleur){
				$DB_site->query("INSERT INTO caracteristiquevaleur_site (caractvalid, siteid) VALUES ('$caractvalid', '$site[siteid]')");
			}
		
			$existe_site_caracteristique = $DB_site->query_first("SELECT * FROM caracteristiquevaleur
					INNER JOIN caracteristiquevaleur_site USING(caractvalid)
					WHERE caractvalid = '$caractvalid'
					AND siteid='$site[siteid]'");
		
			if($existe_site_caracteristique[caractvalid] == ""){
				$DB_site->query("INSERT INTO caracteristiquevaleur_site (caractvalid, siteid) VALUES ('$caractvalid', '$site[siteid]')");			
			}	
			$sql = "UPDATE caracteristiquevaleur_site SET libelle = '" . securiserSql($_POST["libelle$site[siteid]"]) . "'
					WHERE caractvalid = '$caractvalid' AND siteid = '$site[siteid]'";
			$DB_site->query($sql);
		}
		
		if (in_array("5909" , $modules) || $mode == "test_modules"){ //neteven
			$sql = "UPDATE caracteristiquevaleur SET 
						neteven_caracteristiqueid = '$neteven_caracteristiqueid', 
						neteven_caracteristiquevaleurid = '$neteven_caracteristiquevaleurid' 
						WHERE caractvalid='$caractvalid'";
			$DB_site->query($sql);
		}
		
		// Photos par caractéristique
		if (in_array($caractid , $photocaractid) || $mode == "test_modules"){		
			if($_FILES['image']['name'] != "") {	
				$type_fichier=define_extention($_FILES['image']['name']);							
				$nom_fic = $rootpath."configurations/$host/images/caractvals/".$caractvalid.".".$type_fichier;
				copier_image($nom_fic,"image");
				$destination=$rootpath."configurations/$host/images/caractvals/br/".$caractvalid.".".$type_fichier;
				redimentionner_image($nom_fic, $destination, $caractval_largeur, $caractval_hauteur);
				$DB_site->query("UPDATE caracteristiquevaleur SET image = '$type_fichier' WHERE caractvalid = '$caractvalid'");
			}
			$DB_site->query("UPDATE caracteristiquevaleur SET couleur='".addslashes($couleur)."' WHERE caractvalid = '$caractvalid'");
		}
		
		if (in_array("5947" , $modules) || $mode == "test_modules"){ // Recherche par facette	
			if($_FILES['facette']['name'] != "") {
				$type_fichier=define_extention($_FILES['facette']['name']);
				$nom_fic = $rootpath."configurations/$host/images/facettes/".$caractvalid.".".$type_fichier;
				copier_image($nom_fic,"facette");
				$DB_site->query("UPDATE caracteristiquevaleur SET facette = '$type_fichier' WHERE caractvalid = '$caractvalid'");
			}
		}

		if($nouvellevaleur){
			$texteSuccess = "La valeur <strong>" . $_POST["libelle$site[siteid]"] . "</strong> a bien été créée";
			header("location: caracteristiques.php?action=modifier&caractid=$caractid&succes=1&caractvalid=$caractvalid");
		}else{
			$texteSuccess = "La valeur <strong>" . $_POST["libelle$site[siteid]"] . "</strong> a bien été modifiée";
			header("location: caracteristiques.php?action=modifier&caractid=$caractid&succes=2&caractvalid=$caractvalid");
		}
	}else{
		header('location: caracteristiques.php?erreurdroits=1');	
	}
}

// AJOUTER OU MODIFIER UNE VALEUR
if (isset($action) and $action == "modifiervaleur"){
	if (isset($caractid)){
		$caract = $DB_site->query_first("SELECT * FROM caracteristique_site
				INNER JOIN caracteristique
				ON caracteristique_site.caractid = caracteristique.caractid
				WHERE siteid = '1' AND caracteristique.caractid = '$caractid'");
		
		if ($caractvalid != ""){
			$ct = $DB_site->query_first("SELECT * from caracteristiquevaleur
											INNER JOIN caracteristiquevaleur_site USING(caractvalid)
											WHERE caractvalid = '$caractvalid'
											AND siteid = '1'");

			$texte_entete="$multilangue[donnees] <i>".$tabsites[1][libelle]."</i>";
			$libNavigSupp = "<a href=\"caracteristiques.php?action=modifier&caractid=$caractid\">$caract[libelle]</a> <i class=\"fa fa-angle-right\"></i> $multilangue[modif_valeur]";
			eval(charge_template($langue,$referencepage,"NavigSupp"));
		}else{			
			$texte_entete = "$multilangue[donnees] <i>".$tabsites[1][libelle]."</i>";
			$libNavigSupp = "Ajout d'une nouvelle valeur pour la caractéristique <b>\"$caract[libelle]\"</b>";
			eval(charge_template($langue,$referencepage,"NavigSupp"));
		}
		
		eval(charge_template($langue, $referencepage, "ValeurModificationBit"));
		$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
		while($site = $DB_site->fetch_array($sites)){
			$ctsite = $DB_site->query_first("SELECT * from caracteristiquevaleur
					INNER JOIN caracteristiquevaleur_site USING(caractvalid)
					WHERE caractvalid = '$caractvalid'
					AND siteid = '$site[siteid]'");
			eval(charge_template($langue,$referencepage,"ValeurModificationSiteBit"));
		}
		
		if (in_array("5909" , $modules) || $mode == "test_modules"){ //neteven
			$neteven_caracteristiquevaleurid = "";
			$neteven_caracteristiqueid = "";
			if($ct[neteven_caracteristiquevaleurid] != 0) {
				$neteven_caracteristiquevaleurid = $ct[neteven_caracteristiquevaleurid];
			}
			if($ct[neteven_caracteristiqueid] != 0) {
				$neteven_caracteristiqueid = $ct[neteven_caracteristiqueid];
			}
					
			$contSelectNeteven = "<div id='contSelectNeteven'>";
			ob_start();
			include "placedemarche_neteven_select.php";
			$contSelectNeteven .=  ob_get_contents();
			ob_end_clean();
			$contSelectNeteven.= "</div>";
						
			eval(charge_template($langue, $referencepage, "ValeurModificationNeteven"));
		}		
		
		if (in_array($ct[caractid] , $photocaractid) || $mode == "test_modules"){ //Photo caractéristique	
			if ($ct[image]){
				$image_caractval_src="http://$host/configurations/$host/images/caractvals/".$ct[caractvalid].".".$ct[image];
			}					
			eval(charge_template($langue, $referencepage, "ValeurModificationPhoto"));
		}
		
		if (in_array("5947" , $modules) || $mode == "test_modules"){ //Recherche par facette			
			if ($ct[facette]){
				$image_facette_src="http://$host/configurations/$host/images/facettes/".$ct[caractvalid].".".$ct[facette];
			}
			eval(charge_template($langue, $referencepage, "ValeurModificationFacette"));
		}		
		eval(charge_template($langue, $referencepage, "ValeurModification"));
	}
}

// SUPPRIMER UNE VALEUR
if (isset($action) and $action == "supprimervaleur"){
	$caractval = $DB_site->query_first("SELECT * FROM caracteristiquevaleur_site WHERE caractvalid = '$caractvalid'");

	if ($caractval[caractvalid]){
		$DB_site->query("DELETE FROM caracteristiquevaleur WHERE caractvalid = '$caractvalid'");
		$DB_site->query("DELETE FROM caracteristiquevaleur_site WHERE caractvalid = '$caractvalid'");
		$texteSuccess = "La valeur <strong>$caractval[libelle]</strong> a bien été supprimée";
		eval(charge_template($langue, $referencepage, "Success"));
	}else{
		$texteErreur = "La valeur n'existe plus";
		eval(charge_template($langue, $referencepage, "Erreur"));
	}
	header("location: caracteristiques.php?action=modifier&caractid=$caractid&succes=3");
}

//MODIFIER UNE CARACTERISTIQUE (Enregistrement BDD)
if (isset($action) and $action == "modifier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		$erreur = "";
		$nouvellecaracteristique = 0;

		if($caractid == ""){
			$DB_site->query("INSERT INTO caracteristique(caractid) VALUES ('')");
			$caractid = $DB_site->insert_id();
			$nouvellecaracteristique = 1;
		}
		
		$sites = $DB_site->query("SELECT * FROM site");
		while ($site = $DB_site->fetch_array($sites)){
		
			if($nouvellecaracteristique){
				$DB_site->query("INSERT INTO caracteristique_site(caractid, siteid) VALUES ('$caractid', '$site[siteid]')");
			}
		
			$existe_site_caracteristique = $DB_site->query_first("SELECT * FROM caracteristique
					INNER JOIN caracteristique_site USING(caractid)
					WHERE caractid = '$caractid'
					AND siteid = '$site[siteid]'");
		
			if($existe_site_caracteristique[caractid] == ""){
				$DB_site->query("INSERT INTO caracteristique_site (caractid, siteid) VALUES ('$caractid', '$site[siteid]')");
			}
			
			$sql = "UPDATE caracteristique_site SET libelle = '" . securiserSql($_POST["libelle$site[siteid]"]) . "'
					WHERE caractid = '$caractid' AND siteid = '$site[siteid]'";
		
			$DB_site->query($sql);
		}

		if ($nouvellecaracteristique){
			header("location: caracteristiques.php?succes=4&caractid=$caractid");
		}else{
			header("location: caracteristiques.php?succes=5&caractid=$caractid");
		}
	}else{
		header('location: caracteristiques.php?erreurdroits=1');	
	}
}

//AJOUTER OU MODIFIER UNE CARACTERISTIQUE
if (isset($action) and $action == "modifier"){
	if(isset($caractid)){
		$caract = $DB_site->query_first("SELECT * FROM caracteristique AS c
										LEFT JOIN caracteristique_site AS cs ON cs.caractid = c.caractid 
										WHERE cs.siteid = '1' 
										AND c.caractid = '$caractid'");
		$siteprincipal = $DB_site->query_first("SELECT * FROM site WHERE siteid = '1'");
		$caractvals = $DB_site->query("SELECT * FROM caracteristiquevaleur AS cv 
										LEFT JOIN caracteristiquevaleur_site AS cvs ON cvs.caractvalid = cv.caractvalid
									  	WHERE cv.caractid = '$caractid' 
										AND cvs.siteid = '1'
									  	ORDER BY position");
		$caractval_i = 0;
		$TemplateCaracteristiquesModificationValeurBit = "";
		while ($caractval = $DB_site->fetch_array($caractvals))
		{
			$caractval_i++;
			$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1' ORDER BY siteid");
			$TemplateCaracteristiquesModificationValeurListeSiteBit = "";
			while ($site = $DB_site->fetch_array($sites)){
				$caractvalsite = $DB_site->query_first("SELECT * FROM caracteristiquevaleur AS cv 
														LEFT JOIN caracteristiquevaleur_site AS cvs ON cvs.caractvalid = cv.caractvalid
														WHERE cv.caractvalid = '$caractval[caractvalid]' 
														AND cvs.siteid = '$site[siteid]'
														ORDER BY position");
						
				eval(charge_template($langue,$referencepage,"ModificationValeurListeSiteBit"));
			}
			eval(charge_template($langue, $referencepage, "ModificationValeurListeBit"));
		}
		$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1' ORDER BY siteid");
		while($site = $DB_site->fetch_array($sites)){
			$libellesite = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$site[siteid]'");
			$site[numcol] = $site[siteid]-1;
			eval(charge_template($langue,$referencepage,"ModificationValeurListeSite"));
			eval(charge_template($langue,$referencepage,"ModificationValeurListeSite2"));
		}
		if ($caractval_i){
			eval(charge_template($langue,$referencepage,"ModificationValeurListe"));
		}
		eval(charge_template($langue,$referencepage,"ModificationValeur"));
		
		$texte_entete = "$multilangue[donnees] <i>".$tabsites[1][libelle]."</i>";
		eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
		
		$libNavigSupp = "$multilangue[modif_caracteristique] : $caract[libelle]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{
		$texte_entete = "$multilangue[ajt_caracteristique]";
		eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
		
		$libNavigSupp = "$multilangue[ajt_caracteristique]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}
	$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");
	while ($site = $DB_site->fetch_array($sites)){
		$ctsite = $DB_site->query_first("SELECT * from caracteristique
				INNER JOIN caracteristique_site USING(caractid)
				WHERE caractid = '$caractid'
				AND siteid = '$site[siteid]'");
		eval(charge_template($langue,$referencepage,"ModificationSiteBit"));
	}
	eval(charge_template($langue, $referencepage, "Modification"));
}

//SUPPRIMER UNE CARACTERISTIQUE
if (isset($action) and $action == "supprimer2"){
	$caracteristique = $DB_site->query_first("SELECT * FROM caracteristique
			INNER JOIN caracteristique_site USING(caractid)
			WHERE caractid = '$caractid'
			ORDER BY position");
	if ($caracteristique[caractid] != ""){
		$rq_positions_suivantes = $DB_site->query("SELECT caractid, position FROM caracteristique WHERE position > $caracteristique[position]" );
		while ($rs_positions_suivantes=$DB_site->fetch_array($rq_positions_suivantes)) {
			$position_temp = $rs_positions_suivantes[position] - 1;
		$DB_site->query("UPDATE caracteristique SET position = '$position_temp' WHERE caractid = '$rs_positions_suivantes[caractid]'");
		}
		$DB_site->query("DELETE FROM caracteristique WHERE caractid = '$caractid'");
		$caractvals = $DB_site->query("SELECT * FROM caracteristiquevaleur_site JOIN caracteristiquevaleur
				ON caracteristiquevaleur_site.caractvalid = caracteristiquevaleur.caractvalid
				WHERE caracteristiquevaleur.caractid = '$caractid'
				ORDER BY position");
		while ($caractval = $DB_site->fetch_array($caractvals))
		{
			$DB_site->query("DELETE FROM caracteristiquevaleur_site WHERE caractvalid = '$caractval[caractvalid]'");
		}
		$DB_site->query("DELETE FROM caracteristiquevaleur WHERE caractid = '$caractid'");
		$DB_site->query("DELETE FROM caracteristique_site WHERE caractid = '$caractid'");
		header('location: caracteristiques.php?succes=6');
	}else{
		header('location: caracteristiques.php?erreur=1');
	}
}

if (!isset($action) or $action == ""){
	$caracteristiques = $DB_site->query("SELECT * FROM caracteristique_site
										INNER JOIN caracteristique 
										ON caracteristique_site.caractid = caracteristique.caractid 
										WHERE siteid = '1' ORDER BY caracteristique.position");
	while ($caracteristique = $DB_site->fetch_array($caracteristiques)){
		$caractvals = $DB_site->query("SELECT * FROM caracteristiquevaleur_site JOIN caracteristiquevaleur
									 ON caracteristiquevaleur_site.caractvalid = caracteristiquevaleur.caractvalid
									 WHERE caracteristiquevaleur.caractid = '$caracteristique[caractid]' AND siteid = '1'
									 ORDER BY position");
		$TemplateCaracteristiquesListeValeurs = "";
		$TemplateCaracteristiquesListeValeursBit = "";
		while ($caractval = $DB_site->fetch_array($caractvals)){
			eval(charge_template($langue, $referencepage, "ListeValeursBit"));
		}
		eval(charge_template($langue, $referencepage, "ListeValeurs"));
		eval(charge_template($langue, $referencepage, "ListeBit"));
	}
	eval(charge_template($langue, $referencepage, "Liste"));
	$libNavigSupp = "$multilangue[liste_caracteristiques]";
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