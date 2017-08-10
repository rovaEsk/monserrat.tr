<?php
include "./includes/header.php";

$referencepage="commandes_creation";
$pagetitle = "Commandes création - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

$scriptcourant = "commandes.php";

if(!$admin_droit[$scriptcourant][ecriture]){
	header('location: commandes.php?erreurdroits=1');	
}

//$mode = "test_modules";

if (isset($action) && $action == "creationFacture") {
	if ($user && $moyenid && $modelivraisonid) {
		$utilisateur = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$user'");
		$devise = $DB_site->query_first("SELECT deviseid FROM site WHERE siteid = '$utilisateur[siteid]'");
		// TVA des frais de port
		$tauxtvaport = 0;
		$pays = $DB_site->query_first("SELECT TVAtauxnormal,TVAporttauxnormal, port FROM pays WHERE paysid = '$utilisateur[paysid]'");
		if ($pays[port] == "1")  {
			$tauxtvaport = $pays[TVAporttauxnormal] ;
		}

		$DB_site->query("INSERT INTO facture (factureid) VALUES ('')");
		$factureid = $DB_site->insert_id() ;
		$sqlFactureUpdate = "UPDATE facture SET";
		$sqlFactureUpdate .= " userid = '$utilisateur[userid]',";
		$sqlFactureUpdate .= " datefacture = '".date('Y-m-d H:i:s')."',";
		$sqlFactureUpdate .= " moyenid = '$moyenid',";
		$sqlFactureUpdate .= " etatid = '0',";
		$sqlFactureUpdate .= " siteid = '$utilisateur[siteid]',";
		$sqlFactureUpdate .= " deviseid = '$devise[deviseid]',";
		$sqlFactureUpdate .= " modelivraisonid = '$modelivraisonid',";
		$sqlFactureUpdate .= " prefixe_facture = '$params[codefacture]',";
		$sqlFactureUpdate .= " lcivilite = '$utilisateur[civilite]',";
		$sqlFactureUpdate .= " lnom = '".addslashes($utilisateur[nom])."',";
		$sqlFactureUpdate .= " lprenom = '".addslashes($utilisateur[prenom])."',";
		$sqlFactureUpdate .= " lraison_sociale = '".addslashes($utilisateur[raisonsociale])."',";
		$sqlFactureUpdate .= " ladresse = '".addslashes($utilisateur[adresse])."',";
		$sqlFactureUpdate .= " ladresse2 = '".addslashes($utilisateur[adresse2])."',";
		$sqlFactureUpdate .= " lcodepostal = '$utilisateur[codepostal]',";
		$sqlFactureUpdate .= " lville = '".addslashes($utilisateur[ville])."',";
		$sqlFactureUpdate .= " lpaysid = '$utilisateur[paysid]',";
		$sqlFactureUpdate .= " ltelephone = '$utilisateur[telephone]',";
		$sqlFactureUpdate .= " lcommentaire = '".addslashes($utilisateur[commentaire])."',";
		/*if(in_array(110, $modules)) { // Fianet 
			$modelivraison = $DB_site->query_first("SELECT fianet_transportid FROM mode_livraison WHERE modelivraisonid = '$modelivraisonid'");
			$fianet_transportid = $modelivraison[fianet_transportid];
		}
		if(in_array(110, $modules)) {
			if ($moyenid == 7) {
				$fianet_transportid = 1;
			}
			$sqlFactureUpdate .= " fianet_transportid = '$fianet_transportid',";
		}*/
		$sqlFactureUpdate .= " ip = '".iptochaine($_SERVER['REMOTE_ADDR'])."',";
		$sqlFactureUpdate .= " timestamp = '".date('Y-m-d H:i:s')."',";
		$sqlFactureUpdate .= " timestamp2 = '".time()."',";
		$sqlFactureUpdate .= " mail = '$utilisateur[mail]',";
		$sqlFactureUpdate .= " civilite = '$utilisateur[civilite]',";
		$sqlFactureUpdate .= " nom = '".addslashes($utilisateur[nom])."',";
		$sqlFactureUpdate .= " prenom = '".addslashes($utilisateur[prenom])."',";
		$sqlFactureUpdate .= " raison_sociale = '".addslashes($utilisateur[raisonsociale])."',";
		$sqlFactureUpdate .= " adresse = '".addslashes($utilisateur[adresse])."',";
		$sqlFactureUpdate .= " adresse2 = '".addslashes($utilisateur[adresse2])."',";
		$sqlFactureUpdate .= " codepostal = '$utilisateur[codepostal]',";
		$sqlFactureUpdate .= " ville = '".addslashes($utilisateur[ville])."',";
		$sqlFactureUpdate .= " paysid = '$utilisateur[paysid]',";
		$sqlFactureUpdate .= " telephone = '$utilisateur[telephone]',";
		$sqlFactureUpdate .= " telephone2 = '$utilisateur[telephone2]',";
		$sqlFactureUpdate .= " siret = '$utilisateur[siret]',";
		$sqlFactureUpdate .= " tva = '$utilisateur[tva]',";
		$sqlFactureUpdate .= " tvaport = '$tauxtvaport',";

		if(in_array(122, $modules) && $utilisateur[pro]) { /* Espace pro */
			$sqlFactureUpdate .= " pro = '1',";
		}
		$sqlFactureUpdate .= " commentaire = 'Commande créée via le panneau d\'administration'";
		//$sqlFactureUpdate = substr($sqlFactureUpdate, 0, -1);
		$sqlFactureUpdate .= " WHERE factureid = '$factureid'";
		$DB_site->query($sqlFactureUpdate);
	} 
	header("location: commandes.php?action=modifier&factureid=$factureid");
}

if(isset($action) && $action == "creationClient"){
	if(!isset($user) || $user == ""){
		$langue = $DB_site->query_first("SELECT langueid FROM site WHERE siteid = '$Fsite'");
		if ($Fnewsletter) {
			$Fnewsletter = 1;
		} else {
			$Fnewsletter = 0;
		}
		// On crée le compte client
		$sqlUtilisateurInsert = "INSERT INTO utilisateur (";
		$sqlUtilisateurInsert .= "mail,";
		$sqlUtilisateurInsert .= "password,";
		$sqlUtilisateurInsert .= "civilite,";
		$sqlUtilisateurInsert .= "nom,";
		$sqlUtilisateurInsert .= "prenom,";
		$sqlUtilisateurInsert .= "adresse,";
		$sqlUtilisateurInsert .= "adresse2,";
		$sqlUtilisateurInsert .= "codepostal,";
		$sqlUtilisateurInsert .= "ville,";
		$sqlUtilisateurInsert .= "telephone,";
		$sqlUtilisateurInsert .= "telephone2,";
		$sqlUtilisateurInsert .= "raisonsociale,";
		$sqlUtilisateurInsert .= "dateinscription,";
		$sqlUtilisateurInsert .= "paysid,";
		$sqlUtilisateurInsert .= "datenaissance,";
		$sqlUtilisateurInsert .= "siteid,";
		$sqlUtilisateurInsert .= "langueid,";
		$sqlUtilisateurInsert .= "recevoir,";
		$sqlUtilisateurInsert .= "siret,";
		$sqlUtilisateurInsert .= "tva,";
		$sqlUtilisateurInsert .= "pro";
		$sqlUtilisateurInsert .= ") VALUES (";
		$sqlUtilisateurInsert .= "'$Fmail',";
		$sqlUtilisateurInsert .= "MD5('$Fpass'),";
		$sqlUtilisateurInsert .= "'$Fcivilite',";
		$sqlUtilisateurInsert .= "'".addslashes($Fnom)."',";
		$sqlUtilisateurInsert .= "'".addslashes($Fprenom)."',";
		$sqlUtilisateurInsert .= "'".addslashes($Fadresse)."',";
		$sqlUtilisateurInsert .= "'".addslashes($Fcomp)."',";
		$sqlUtilisateurInsert .= "'$Fcp',";
		$sqlUtilisateurInsert .= "'".addslashes($Fville)."',";
		$sqlUtilisateurInsert .= "'$Ftel1',";
		$sqlUtilisateurInsert .= "'$Ftel2',";
		$sqlUtilisateurInsert .= "'".addslashes($Fste)."',";
		$sqlUtilisateurInsert .= "NOW(),";
		$sqlUtilisateurInsert .= "'$Fpays',";
		$sqlUtilisateurInsert .= "'$Fannee-$Fmois-$Fjour',";
		$sqlUtilisateurInsert .= "'$Fsite',";
		$sqlUtilisateurInsert .= "'$langue[langueid]',";
		$sqlUtilisateurInsert .= "'$Fnewsletter',";
		$sqlUtilisateurInsert .= "'$Fsiret',";
		$sqlUtilisateurInsert .= "'$Ftva',";
		$sqlUtilisateurInsert .= "'$Fpro'";
		$sqlUtilisateurInsert .= ")";
		$DB_site->query($sqlUtilisateurInsert);
		$user = $DB_site->insert_id();
	}
	
	header("location: commandes_creation.php?action=choixDetails&utilisateurid=$user");
}

if(isset($action) && $action == "choixDetails"){
	$utilisateur = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$utilisateurid'");
	$civilite = retournerCivilite($utilisateur[civilite]);
	$pays = retournerLibellePays($DB_site, $utilisateur[paysid]);

	$moyens = $DB_site->query("SELECT * FROM moyenpaiement INNER JOIN moyenpaiement_site USING (moyenid) WHERE siteid = '1' AND (online = '0' OR online = '') AND (activeV1 = '1' OR activeV2 = '1')");
	while($moyen = $DB_site->fetch_array($moyens)){
		eval(charge_template($langue, $referencepage, "ChoixDetailsPaiementBit"));
	}

	$livraisons = $DB_site->query("SELECT * FROM mode_livraison INNER JOIN mode_livraison_site USING (modelivraisonid) WHERE siteid = '1' AND (activeV1 = '1' OR activeV2 = '1') AND modelivraisonid IN (SELECT distinct(fp.modelivraisonid) FROM fraisport fp WHERE paysid = '$utilisateur[paysid]' AND prix >= 0 AND debut > 0 AND fin > 0) ORDER BY position");
	while($livraison = $DB_site->fetch_array($livraisons)){
		eval(charge_template($langue, $referencepage, "ChoixDetailsLivraisonBit"));
	}

	eval(charge_template($langue, $referencepage, "ChoixDetails"));
}

if(!isset($action) || $action == ""){
	$sites = $DB_site->query("SELECT * FROM site");
	while($site = $DB_site->fetch_array($sites)){
		eval(charge_template($langue, $referencepage, "SelectionClientSiteBit"));
	}
	
	$listePays = retournerListePays($DB_site);
	$listeJours = retournerListeJours();
	$listeMois = retournerListeMois();
	$listeAnnees = retournerListeAnnees();
	eval(charge_template($langue, $referencepage, "SelectionClient"));
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