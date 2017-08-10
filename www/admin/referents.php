<?php
include "./includes/header.php";

$referencepage="referents";
$pagetitle = "Référents - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}



// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) and $action == "exportcsv"){
	$path = './export/referent.csv';
	@unlink($path);
	$fd = fopen($path, 'a');
	
	// UTF8 pour csv
	fputs($fd, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));
	
	if (isset($referent) && !empty($referent)){
		$moteursrecherche = $DB_site->query("SELECT *, SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) as totalmontantcommandes
											FROM referents WHERE motcle != '' AND referent = '" . addslashes($referent) . "' AND dateline >= '$datedebut' AND dateline <= '$datefin' GROUP BY motcle");
		$exportmotcle = 1;
	}else{
		$moteursrecherche = $DB_site->query("SELECT referentid,r.groupe_referentid,libelle,referent,SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) totalmontantcommandes, prix_clic prixclic, SUM(marge_ht) marge, SUM(prix_clic * nb_commande) totalprixclic
											FROM referents r LEFT OUTER JOIN groupe_referents gr USING(groupe_referentid) 
											WHERE motcle = '' AND r.groupe_referentid > 0 AND dateline >= '$datedebut' AND dateline <= '$datefin'
											GROUP BY r.groupe_referentid UNION
											SELECT referentid,r.groupe_referentid,libelle,referent,SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) totalmontantcommandes, prix_clic prixclic, SUM(marge_ht) as marge, SUM(prix_clic * nb_commande) AS totalprixclic
											from referents r LEFT OUTER JOIN groupe_referents gr USING(groupe_referentid) 
											WHERE motcle = '' AND r.groupe_referentid = 0 AND dateline >= '$datedebut' AND dateline <= '$datefin' GROUP BY r.referent");
		$exportmotcle = 0;
	}
	if ($exportmotcle){
		$contenu = $multilangue[referents].";$multilangue[mots_cles];$multilangue[nb_clics];$multilangue[nb_cdes];$multilangue[total] $multilangue[ttc]\n";
	}else{
		$contenu = $multilangue[referents].";$multilangue[nb_clics];$multilangue[nb_cdes];$multilangue[total] $multilangue[ttc]\n";
	}
	while($moteurrecherche = $DB_site->fetch_array($moteursrecherche)){
		$moteurrecherche[referent] = str_replace(";", "", str_replace("\r", "", str_replace("\n", "", $moteurrecherche[referent])));
		$moteurrecherche[motcle] = str_replace(";", "", str_replace("\r", "", str_replace("\n", "", $moteurrecherche[motcle])));
		$contenu .= secure_chaine_csv($moteurrecherche[referent]).";";
		if($exportmotcle){
			$contenu .= secure_chaine_csv($moteurrecherche[motcle]).";";
		}
		$contenu .= secure_chaine_csv($moteurrecherche[totalclics]).";";
		$contenu .= secure_chaine_csv($moteurrecherche[totalcommandes]).";";
		$contenu .= secure_chaine_csv($moteurrecherche[totalmontantcommandes]).";";
		$contenu .= "\n";
	}
	fwrite($fd, $contenu);
	fclose($fd);
	header("Location: $_SERVER[REQUEST_URI]");
	header('Location: ./export/referent.csv');
}

if (isset($action) and $action == "infomotcle"){
	$datedebut = (isset($datedebut) ? date("Y-m-d", strtotime(str_replace('/', '-', $datedebut))) : date("Y-m-01"));
	$inputdatedebut = date("d/m/Y", strtotime($datedebut));
	$datefin = (isset($datefin) ? date("Y-m-d", strtotime(str_replace('/', '-', $datefin))) : date("Y-m-d"));
	$inputdatefin = date("d/m/Y", strtotime($datefin));
	$date = strtotime($datedebut);
	while ($date <= strtotime($datefin)){
		$motcleinfo = $DB_site->query_first("SELECT *, SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) as totalmontantcommandes
											FROM referents WHERE motcle = '" . addslashes($motcle) . "' AND dateline = '" . date("Y-m-d", $date) . "' GROUP BY dateline");
		$motcleinfo[totalclics] = ($motcleinfo[totalclics] == "" ? "0" : $motcleinfo[totalclics]);
		$motcleinfo[totalcommandes] = ($motcleinfo[totalcommandes] == "" ? "0" : $motcleinfo[totalcommandes]);
		$date *= 1000;
		eval(charge_template($langue,$referencepage,"InfoMotCleClics"));
		eval(charge_template($langue,$referencepage,"InfoMotCleCommandes"));
		$date /= 1000;
		$date += 86400;
	}
	$motcleinfos = $DB_site->query("SELECT *, SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) as totalmontantcommandes
									FROM referents WHERE motcle = '" . addslashes($motcle) . "' AND dateline >= '$datedebut' AND dateline <= '$datefin' GROUP BY dateline ORDER BY dateline");
	while ($motcleinfo = $DB_site->fetch_array($motcleinfos)){
		$motcleinfo[dateline] = date("d/m/Y", strtotime($motcleinfo[dateline]));
		$motcleinfo[totalmontantcommandes] = formaterPrix($motcleinfo[totalmontantcommandes], 2, '.');
		$tauxconversionglobal = ($motcleinfo[totalclics] ? formaterPrix($motcleinfo[totalcommandes] / $motcleinfo[totalclics] * 100, 2, '.') : "0,00");
		eval(charge_template($langue,$referencepage,"InfoMotCleBit"));
	}
	$libNavigSupp = "$multilangue[informations] $multilangue[mots_cles] : $motcle";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue,$referencepage,"InfoMotCle"));
}

if (isset($action) and $action == "infolienentrant"){
	$datedebut = (isset($datedebut) ? date("Y-m-d", strtotime(str_replace('/', '-', $datedebut))) : date("Y-m-01"));
	$inputdatedebut = date("d/m/Y", strtotime($datedebut));
	$datefin = (isset($datefin) ? date("Y-m-d", strtotime(str_replace('/', '-', $datefin))) : date("Y-m-d"));
	$inputdatefin = date("d/m/Y", strtotime($datefin));
	$date = strtotime($datedebut);
	while ($date <= strtotime($datefin)){
		$lienentrantinfo = $DB_site->query_first("SELECT *, SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) as totalmontantcommandes
												 FROM referents LEFT JOIN groupe_referents USING(groupe_referentid) WHERE (referent = '" . addslashes($lienentrant) . "' OR libelle = '" . addslashes($lienentrant) . "')
												 AND dateline = '" . date("Y-m-d", $date) . "'
												 GROUP BY dateline");
		$lienentrantinfo[totalclics] = ($lienentrantinfo[totalclics] == "" ? "0" : $lienentrantinfo[totalclics]);
		$lienentrantinfo[totalcommandes] = ($lienentrantinfo[totalcommandes] == "" ? "0" : $lienentrantinfo[totalcommandes]);
		$date *= 1000;
		eval(charge_template($langue,$referencepage,"InfoLienEntrantClics"));
		eval(charge_template($langue,$referencepage,"InfoLienEntrantCommandes"));
		$date /= 1000;
		$date += 86400;
	}
	$lienentrantinfos = $DB_site->query("SELECT *, SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) as totalmontantcommandes
										FROM referents LEFT JOIN groupe_referents USING(groupe_referentid) WHERE (referent = '" . addslashes($lienentrant) . "' OR libelle = '" . addslashes($lienentrant) . "')
			 							AND dateline >= '$datedebut' AND dateline <= '$datefin'
										GROUP BY dateline ORDER BY dateline");
	while ($lienentrantinfo = $DB_site->fetch_array($lienentrantinfos)){
		$lienentrantinfo[dateline] = date("d/m/Y", strtotime($lienentrantinfo[dateline]));
		$lienentrantinfo[totalmontantcommandes] = formaterPrix($lienentrantinfo[totalmontantcommandes], 2, '.');
		$tauxconversionglobal = ($lienentrantinfo[totalclics] ? formaterPrix($lienentrantinfo[totalcommandes] / $lienentrantinfo[totalclics] * 100, 2, '.') : "0,00");
		eval(charge_template($langue,$referencepage,"InfoLienEntrantBit"));
	}
	$libNavigSupp = "$multilangue[informations] $multilangue[liens_entrants] : $lienentrant";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue,$referencepage,"InfoLienEntrant"));
}

if (isset($action) and $action == "infomoteurrecherche"){
	$datedebut = (isset($datedebut) ? date("Y-m-d", strtotime(str_replace('/', '-', $datedebut))) : date("Y-m-01"));
	$inputdatedebut = date("d/m/Y", strtotime($datedebut));
	$datefin = (isset($datefin) ? date("Y-m-d", strtotime(str_replace('/', '-', $datefin))) : date("Y-m-d"));
	$inputdatefin = date("d/m/Y", strtotime($datefin));
	$date = strtotime($datedebut);
	while ($date <= strtotime($datefin)){
		$moteurrechercheinfo = $DB_site->query_first("SELECT *, SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) as totalmontantcommandes
													 FROM referents WHERE motcle != '' AND referent = '" . addslashes($moteurrecherche) . "' AND dateline = '" . date("Y-m-d", $date) . "' GROUP BY dateline");
		$moteurrechercheinfo[totalclics] = ($moteurrechercheinfo[totalclics] == "" ? "0" : $moteurrechercheinfo[totalclics]);
		$moteurrechercheinfo[totalcommandes] = ($moteurrechercheinfo[totalcommandes] == "" ? "0" : $moteurrechercheinfo[totalcommandes]);
		$date *= 1000;
		eval(charge_template($langue,$referencepage,"InfoMoteurRechercheClics"));
		eval(charge_template($langue,$referencepage,"InfoMoteurRechercheCommandes"));
		$date /= 1000;
		$date += 86400;
	}
	$moteurrechercheinfos = $DB_site->query("SELECT *, SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) as totalmontantcommandes
											FROM referents WHERE motcle != '' AND referent = '" . addslashes($moteurrecherche) . "' AND dateline >= '$datedebut' AND dateline <= '$datefin' GROUP BY motcle");
	while ($moteurrechercheinfo = $DB_site->fetch_array($moteurrechercheinfos)){
		$moteurrechercheinfo[totalmontantcommandes] = formaterPrix($moteurrechercheinfo[totalmontantcommandes], 2, '.');
		$tauxconversionglobal = ($moteurrechercheinfo[totalclics] ? formaterPrix($moteurrechercheinfo[totalcommandes] / $moteurrechercheinfo[totalclics] * 100, 2, '.') : "0,00");
		eval(charge_template($langue,$referencepage,"InfoMoteurRechercheBit"));
	}
	$libNavigSupp = "$multilangue[informations] $multilangue[moteurs_recherche] : $moteurrecherche";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue,$referencepage,"InfoMoteurRecherche"));
}

if (isset($action) and $action == "supprimerreferentgroupe"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("UPDATE referents SET groupe_referentid = '0', prix_clic = '0' WHERE referent = '$referent'");
		header("location: referents.php?action=gestiongroupes");
	}else{
		header('location: referents.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "supprimergroupe"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM groupe_referents WHERE groupe_referentid = '$groupe_referentid'");
		$DB_site->query("UPDATE referents SET groupe_referentid = '0' WHERE groupe_referentid = '$groupe_referentid'");
		header("location: referents.php?action=gestiongroupes");
	}else{
		header('location: referents.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "modifiergroupe2"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($groupe_referentid == ""){
			$DB_site->query("INSERT INTO groupe_referents(groupe_referentid) VALUES ('')");
			$groupe_referentid = $DB_site->insert_id();
		}
		$DB_site->query("UPDATE groupe_referents SET libelle = '" . securiserSql($_POST[libelle]) . "' WHERE groupe_referentid = '$groupe_referentid'");
		$DB_site->query("UPDATE referents SET prix_clic = '" . securiserSql($_POST[prix_clic]) . "', marge_ht=((montant_commande/1.2)-".securiserSql($_POST[prix_clic]).") WHERE groupe_referentid = '$groupe_referentid'");
		header("location: referents.php?action=gestiongroupes");
	}else{
		header('location: referents.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "modifiergroupe"){
	if ($groupe_referentid != ""){
		$groupe = $DB_site->query_first("SELECT * FROM groupe_referents WHERE groupe_referentid = '$groupe_referentid'");
		$texte_entete = "$multilangue[modification] : $groupe[libelle]";
		eval(charge_template($langue,$referencepage,"ModificationGroupeBit"));
		eval(charge_template($langue,$referencepage,"ModificationGroupe"));
		$libNavigSupp = "$multilangue[modification] : $groupe[libelle]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{
		$texte_entete = "$multilangue[ajouter]";
		eval(charge_template($langue,$referencepage,"ModificationGroupe"));
		$libNavigSupp = "$multilangue[ajouter]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}
}

if (isset($action) and $action == "gestiongroupes"){
	$groupes = $DB_site->query("SELECT *, COUNT(DISTINCT(referents.referent)) count FROM groupe_referents
								LEFT OUTER JOIN referents USING(groupe_referentid)
								GROUP BY groupe_referentid
								ORDER BY libelle");
	while ($groupe = $DB_site->fetch_array($groupes)){
		$TemplateReferentsListeGroupeDetail = "";
		$groupedetails = $DB_site->query("SELECT * FROM referents WHERE groupe_referentid = '$groupe[groupe_referentid]' GROUP BY referent");
		while ($groupedetail = $DB_site->fetch_array($groupedetails)){
			$groupedetail[prix_clic] = formaterPrix($groupedetail[prix_clic], 2, '.');
			eval(charge_template($langue, $referencepage, "ListeGroupeDetail"));
		}
		eval(charge_template($langue, $referencepage, "ListeGroupeBit"));
	}
	$libNavigSupp = "$multilangue[gestion_groupes]";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue, $referencepage, "ListeGroupe"));
}

if (isset($action) and $action == "modifier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE referents SET prix_clic = '$_POST[prix_clic]' WHERE referent = '$_POST[lienentrant]'");
		$groupe = $DB_site->query_first("SELECT * FROM referents INNER JOIN groupe_referents USING(groupe_referentid) WHERE libelle = '$_POST[libelle]'");
		$groupe_referentid = $DB_site->query_first("SELECT groupe_referentid FROM groupe_referents WHERE libelle = '$_POST[libelle]'");
		$DB_site->query("UPDATE referents SET groupe_referentid = '$groupe_referentid[groupe_referentid]' WHERE referent = '$_POST[lienentrant]'");
		if ($groupe[prix_clic] != ""){
			$DB_site->query("UPDATE referents SET prix_clic = '$groupe[prix_clic]' WHERE referent = '$_POST[lienentrant]'");
		}
		header("location: referents.php");
	}else{
		header('location: referents.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "modifier"){
	$lienentrant = $DB_site->query_first("SELECT * FROM referents WHERE referent = '$lienentrant'");
	$libNavigSupp = "$multilangue[modification] $multilangue[liens_entrants] : $lienentrant[referent]";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue, $referencepage, "Modification"));
}

if (!isset($action) or $action == ""){
	$datedebut = (isset($datedebut) ? date("Y-m-d", strtotime(str_replace('/', '-', $datedebut))) : date("Y-m-01"));
	$inputdatedebut = date("d/m/Y", strtotime($datedebut));
	$datefin = (isset($datefin) ? date("Y-m-d", strtotime(str_replace('/', '-', $datefin))) : date("Y-m-d"));
	$inputdatefin = date("d/m/Y", strtotime($datefin));
	$motcles = $DB_site->query("SELECT *, SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) as totalmontantcommandes
								FROM referents WHERE motcle != '' AND dateline >= '$datedebut' AND dateline <= '$datefin' GROUP BY motcle");
	while ($motcle = $DB_site->fetch_array($motcles)){
		$motcle[totalmontantcommandes] = formaterPrix($motcle[totalmontantcommandes], 2, '.');
		$tauxconversionglobal = ($motcle[totalclics] ? formaterPrix($motcle[totalcommandes] / $motcle[totalclics] * 100, 2, '.') : "0,00");
		eval(charge_template($langue, $referencepage, "ListeMotsClesBit"));
	}
	eval(charge_template($langue, $referencepage, "ListeMotsCles"));
	$liensentrants = $DB_site->query("SELECT referentid,r.groupe_referentid,libelle,referent,SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) totalmontantcommandes, prix_clic prixclic, SUM(marge_ht) AS marge, SUM(prix_clic * hit) totalprixclic
									FROM referents r LEFT OUTER JOIN groupe_referents gr USING(groupe_referentid) 
									WHERE motcle = '' AND r.groupe_referentid > 0 AND dateline >= '$datedebut' AND dateline <= '$datefin'
									GROUP BY r.groupe_referentid UNION
									SELECT referentid,r.groupe_referentid,libelle,referent,SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) totalmontantcommandes, prix_clic prixclic, SUM(marge_ht) AS marge, SUM(prix_clic * hit) AS totalprixclic
									from referents r LEFT OUTER JOIN groupe_referents gr USING(groupe_referentid) 
									WHERE motcle = '' AND r.groupe_referentid = 0 AND dateline >= '$datedebut' AND dateline <= '$datefin' GROUP BY r.referent");

	while ($liensentrant = $DB_site->fetch_array($liensentrants)){
		$liensentrant[libelle] = (empty($liensentrant[groupe_referentid]) ? $liensentrant[referent] : $liensentrant[libelle]);
		$liensentrant[totalmontantcommandes] = formaterPrix($liensentrant[totalmontantcommandes], 2, '.',"");
		$tauxconversionglobal = ($liensentrant[totalclics] ? formaterPrix($liensentrant[totalcommandes] / $liensentrant[totalclics] * 100, 2, '.',"") : "0,00");
		$liensentrant[prixclic] = formaterPrix($liensentrant[prixclic], 2, '.',"");
		$liensentrant[totalprixclic] = formaterPrix($liensentrant[totalprixclic], 2, '.',"");
		$liensentrant[marge] = formaterPrix(($liensentrant[totalmontantcommandes]/1.2)-$liensentrant[totalprixclic], 2, '.',"");
		
		// Formatage des prix
		$liensentrant[totalmontantcommandes] = formaterPrix($liensentrant[totalmontantcommandes], 2, "."," ");
		$tauxconversionglobal = formaterPrix($tauxconversionglobal, 2, ".","");
		$liensentrant[prixclic] = formaterPrix($liensentrant[prixclic], 2, ".","");
		$liensentrant[totalprixclic] = formaterPrix($liensentrant[totalprixclic], 2, "."," ");
		$liensentrant[marge] = formaterPrix($liensentrant[marge], 2, "."," ");		
		
		eval(charge_template($langue, $referencepage, "ListeLiensEntrantsBit"));
	}
	eval(charge_template($langue, $referencepage, "ListeLiensEntrants"));
	$moteursrecherche = $DB_site->query("SELECT *, SUM(hit) totalclics, SUM(nb_commande) totalcommandes, SUM(montant_commande) as totalmontantcommandes
										FROM referents WHERE motcle != '' AND dateline >= '$datedebut' AND dateline <= '$datefin' GROUP BY referent LIMIT 20");
	while ($moteurrecherche = $DB_site->fetch_array($moteursrecherche)){
		$moteurrecherche[totalmontantcommandes] = formaterPrix($moteurrecherche[totalmontantcommandes], 2, '.');
		$tauxconversionglobal = ($moteurrecherche[totalclics] ? formaterPrix($moteurrecherche[totalcommandes] / $moteurrecherche[totalclics] * 100, 2, '.') : "0,00");
		eval(charge_template($langue, $referencepage, "ListeMoteursRechercheBit"));
	}
	eval(charge_template($langue, $referencepage, "ListeMoteursRecherche"));
	eval(charge_template($langue, $referencepage, "Liste"));
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