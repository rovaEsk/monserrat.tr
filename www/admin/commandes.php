<?php
include "./includes/header.php";

$referencepage="commandes";
$pagetitle = "Commandes - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

//$mode = "test_modules";

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($action) && $action == "importerExpeditor"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($_FILES['import']['error'] == 0){
			$extension_upload = define_extention($_FILES['import']['name']);
			if ($extension_upload == "csv"){
				$name = $_FILES['import']['tmp_name'];
				$monfichier = fopen($name, 'r+');
				$i = 0 ;
				while ($ligne = fgets($monfichier)){
					$i++ ;
					$tabligne = explode(";", $ligne) ;
					if (count($tabligne) != 3 && count($tabligne) != 4) {
						echo "<b>$multilangue[erreur_structure_ligne] $i : $multilangue[erreur_nb_champs] (".count($tabligne).")</b><br>";
					} else {
						if (!is_numeric($tabligne[0])) {
							echo "<b>$multilangue[erreur_structure_ligne] $i : $multilangue[erreur_num_facture]</b><br>";
						} else {						
							$factureid = $tabligne[0];
							$expedierFacture = 0;
							if (strlen($factureid) < 8) {
								$expedierFacture = 1;
							} elseif (strlen($factureid) == 8 && substr($factureid, 0, 1) == $params[prefixe_numcommande]) {
								$factureid = substr($factureid, 1, 7);
								$expedierFacture = 1;
							}
							
							if($expedierFacture){
								$existeFacture = $DB_site->query_first("SELECT factureid, modelivraisonid FROM facture WHERE factureid = '$factureid'");
								$adresse_suivi = $DB_site->query_first("SELECT URL FROM mode_livraison WHERE modelivraisonid = '$existeFacture[modelivraisonid]'");
								if ($existeFacture[factureid]) {
									$transporteur = $existeFacture[modelivraisonid];
									$adresse_suivi = $adresse_suivi[URL];
									$numero_suivi = $tabligne[1] ;
									$etatfacture=$DB_site->query_first("SELECT etatid FROM facture WHERE factureid = '$factureid'");
									if ($etatfacture[etatid] == 1){
										expedierFacture($DB_site, $factureid);
									}else{
										//	$DB_site->query("UPDATE colis SET numero_suivi = '$numero_suivi' WHERE factureid = '$tabligne[0]'");
									}
								}
							}						
						}
					}
				}
				fclose($monfichier);
			} else {
				echo "<b>$multilangue[erreur_type_fichier] $multilangue[autorisations_type_fichier] CSV<br/></b>";
			}
		}
		header('location: commandes.php');
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "suppr_multiple"){
	if($admin_droit[$scriptcourant][suppression]){
		$factureid = explode(",", $ids_facture);
		foreach ($factureid as $value){
			$DB_site->query("UPDATE facture SET deleted = '1' WHERE factureid = '$value'");
		}
		header("location: commandes.php");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "contacter"){
	if($admin_droit[$scriptcourant][ecriture]){
		$facture=$DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
		$date = date('Y-m-d-H-i-s');
		$langueFacture = $facture[langue];
		$DB_site->query("INSERT INTO facture_contact (factureid, datecontact, sujet, contenu) VALUES ('$factureid', '$date', '".addslashes($sujet)."', '".addslashes($message)."')");
		email($DB_site, $facture[mail], "Votre commande n°$factureid sur ${$title}  ".$sujet, stripslashes(nl2br($message)), $params[$facture[siteid]]["mail_commande"]);
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "coordFactForm"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE facture SET mail = '$mail_facture', raison_sociale = '$raisonsocialefacture', civilite = '$civilitefacture', 
						nom = '$nomfacture', prenom = '$prenomfacture', adresse = '$adressefacture', codepostal = '$codepostalfacture', 
						ville = '$villefacture', paysid = '$paysidfacture', telephone = '$telephonefacture', telephone2 = '$telephone2facture', 
						etage = '$etagefacture', escalier = '$escalierfacture', codeacces = '$codeaccesfacture', commentaire = '$commentairefacture' WHERE factureid = '$factureid'");
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "coordFact"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE facture SET commentaire = '$commentairefacture' WHERE factureid = '$factureid'");
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "coordLivrForm"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE facture SET lraison_sociale = '$lraisonsocialefacture', lcivilite = '$lcivilitefacture',
				lnom = '$lnomfacture', lprenom = '$lprenomfacture', ladresse = '$ladressefacture', lcodepostal = '$lcodepostalfacture',
				lville = '$lvillefacture', lpaysid = '$lpaysidfacture', ladresse2 = '$ladresse2facture', letage = '$letagefacture', lescalier = '$lescalierfacture', lcodeacces = '$lcodeaccesfacture',
				ltelephone = '$ltelephonefacture', lcommentaire = '$lcommentairefacture' WHERE factureid = '$factureid'");
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "coordLivr"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE facture SET lcommentaire = '$lcommentairefacture' WHERE factureid = '$factureid'");
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "valider"){
	if($admin_droit[$scriptcourant][ecriture]){
		$etatfacture = $DB_site->query_first("SELECT etatid FROM facture WHERE factureid = '$factureid'");
		if ($etatfacture[etatid] == 0){
			validerFacture($DB_site, $factureid, $moyenid);
			unset($multilangue);
			include($rootpath."admin/includes/multilangue/langue".$admin_langue.".php");
		}
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "refuser"){
	if($admin_droit[$scriptcourant][ecriture]){
		refusPaiementCommercant($DB_site, $factureid);
		unset($multilangue);
		include($rootpath."admin/includes/multilangue/langue".$admin_langue.".php");
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "rembourser"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE facture SET etatid = '6', dateremboursement = '".date('Y-m-d')."' WHERE factureid = '$factureid'");
		if (!$facture[lengow_orderid])
			email_personnalisable($DB_site, $factureid, 6);
		
		decrementerStockFacture($DB_site, $factureid, 6);
		supprimerPdfCommande($factureid);
		header("location: commandes.php?action=modifier&factureid=$factureid");	
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}
	
if(isset($action) && $action == "rembourserFraisPort"){
	if($admin_droit[$scriptcourant][ecriture]){
		$facture = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
		$montantRemboursementPortHT=$montantRemboursementPort/(1+$facture[tvaport]/100);
		$DB_site->query("INSERT INTO lignefacture (factureid, artcode, qte, libelle, prix, prixht, tva) VALUES ('$factureid', '', '1', '".addslashes($multilangue[remboursement_fraisport_facture].' ('.date('d/m/Y').')')."', '".-$montantRemboursementPort."','-$montantRemboursementPortHT', '$facture[tvaport]')");
		$lignefactureid=$DB_site->insert_id();
		$montants = calculerTotalFacture($DB_site, $factureid) ;
		$mtt_fraisport = calculerFraisPort($DB_site, $factureid) ;
		$DB_site->query("UPDATE facture SET montanttotal_ttc = '$montants[totalTTC]', montanttotal_ht = '$montants[totalHT]', montanttotal_horsfraisport_ttc = '$montants[sousTotalTTC]', montanttotal_horsfraisport_ht = '$montants[sousTotalHT]', etatid = '6', dateremboursementfraisdeport = '".date('Y-m-d')."', lignefactureidremboursementfraisdeport='$lignefactureid' WHERE factureid = '$factureid'") ;
		supprimerPdfCommande($factureid);
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}
	
if(isset($action) && $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$facture=$DB_site->query_first("SELECT * FROM facture f INNER JOIN  moyenpaiement mp USING(moyenid) WHERE factureid = '$factureid'");
		$DB_site->query("UPDATE facture SET deleted = '1' WHERE factureid = '$factureid'");
		if ($facture[lengow_orderid])
			$DB_site->query("UPDATE facture SET etatid = '0' WHERE factureid = '$factureid'");
		decrementerStockFacture($DB_site, $factureid, 0);
		if(in_array(5949,$modules) || $mode == "test_modules"){
			$bons = $DB_site->query("SELECT cadeauid FROM bonachat WHERE factureid = '$facture[factureid]'");
			while($bon = $DB_site->fetch_array($bons)){
				$DB_site->query("UPDATE cadeau SET active = '1' WHERE cadeauid = '$bon[cadeauid]'");
				$DB_site->query("UPDATE bonachat SET factureid = '0' WHERE factureid = '$facture[factureid]' AND cadeauid = '$bon[cadeauid]'");
			}
		}
		supprimerPdfCommande($factureid);
		header("location: commandes.php");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "supprcommentaire"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM facture_commentaires WHERE factureid = '$factureid' AND commentaireid = '$commentaireid'" );
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "bloquer"){
	if($admin_droit[$scriptcourant][ecriture]){
		$facture=$DB_site->query_first("SELECT * FROM facture WHERE factureid='$factureid' " );
		$DB_site->query("INSERT INTO ipbloquer SET ipdeb='$facture[ip]' , ipfin='$facture[ip]'  ");
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "autoriser"){
	if($admin_droit[$scriptcourant][ecriture]){
		$facture=$DB_site->query_first("SELECT * FROM facture WHERE factureid='$factureid' " );
		$DB_site->query("DELETE FROM ipbloquer WHERE ipdeb='$facture[ip]' ");
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if (isset($action) && $action == "majStocks") {
	if($admin_droit[$scriptcourant][ecriture]){
		$facture=$DB_site->query_first("SELECT * FROM facture f INNER JOIN moyenpaiement mp USING (moyenid) WHERE factureid = '$factureid'");
		if ($facture[decrementation] == "0") {
			decrementerStockFacture($DB_site, $factureid);
		} else {
			decrementerStockFacture($DB_site, $factureid, 0);
		}
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if (isset($action) && $action == "majStocksDate") {
	if($admin_droit[$scriptcourant][ecriture]){
		$dateIncrementationPrevue = convertirChaineEnDate($_POST[dateStock]);
		$DB_site->query("UPDATE facture SET dateincrementationprevue='".$dateIncrementationPrevue."' WHERE factureid=".securiserSql($_POST[factureid], "int"));
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if (isset($action) && $action == "commentaire") {
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("INSERT INTO facture_commentaires (commentaire, date, factureid, adminid) VALUES ('".addslashes($commentaire)."',".time().", '$factureid', $user_info[userid])") ; 
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "expedierImmat"){
	if($admin_droit[$scriptcourant][ecriture]){
		expedierFacture($DB_site, $factureid);
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "supprcommande"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("UPDATE facture SET deleted = '1' WHERE factureid = '$factureid'");
		header("location: commandes.php");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "supprgroupe"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM groupe_commande WHERE groupeid = '$groupeid'");
		header("location: commandes.php");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "doreexpedier"){
	if($admin_droit[$scriptcourant][ecriture]){
		$qtesExpdiees = 0;
		$erreur = "";
		foreach ($qtes as $qte) {
			$qtesExpdiees += $qte;
		}
		if (!$qtesExpdiees) {
			eval(charge_template($langue, $referencepage, "ReexpedierErreur"));
		}else{
			reexpedierFacture($DB_site, $factureid);
			header("location: commandes.php?action=modifier&factureid=$factureid");
		}
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "rembourserLigne"){
	if($admin_droit[$scriptcourant][ecriture]){
		$lignefacture = $DB_site->query_first("SELECT * FROM lignefacture WHERE lignefactureid = '$lignefactureid'");
		$facture = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$lignefacture[factureid]'");
		$nouveauLibelle = mysql_real_escape_string($lignefacture[libelle])." (Remboursement)";
		$insertid = copier_lignefacture($DB_site, $lignefactureid, "");
		$qteRemb = -1 * $qte;
		$DB_site->query("UPDATE lignefacture SET qte = '$qteRemb', remboursement = '1', date_remboursement = NOW(), lignefactureidremboursement = '$lignefactureid', libelle = '$nouveauLibelle' WHERE lignefactureid = '$insertid'");
		$lignefactureRemb = $DB_site->query_first("SELECT * FROM lignefacture WHERE lignefactureid = '$insertid'");
		$article = $DB_site->query_first("SELECT artid, typearticle FROM article WHERE artid = '$lignefactureRemb[artid]'");
		$tabanciennes = array();
		$anciennes_caractvalids=$DB_site->query("SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid = '$insertid'");
		while ($ancienne_caractvalid=$DB_site->fetch_array($anciennes_caractvalids))
			array_push($tabanciennes, $ancienne_caractvalid[caractvalid]) ;
		sort($tabanciennes) ;
		
		if (in_array(4, $modules) && $article[typearticle] == "1" && $facture[decrementation] == "1") {
			decrementerStock($DB_site, $article[artid], retournerStockid($DB_site, $article[artid], $tabanciennes), $qte);
		}
		
		// PDF
		supprimerPdfCommande($facture[factureid]);
		
		/* On recalcule les totaux */
		$totaux = calculerTotalFacture($DB_site, $facture[factureid]);
		$DB_site->query("UPDATE facture SET montanttotal_ttc = '$totaux[totalTTC]', montanttotal_ht = '$totaux[totalHT]', montanttotal_horsfraisport_ttc = '$totaux[sousTotalTTC]', montanttotal_horsfraisport_ht = '$totaux[sousTotalHT]' WHERE factureid = '$facture[factureid]'");
		header("location: commandes.php?action=modifier&factureid=$factureid");
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "controlerColis"){
	if($admin_droit[$scriptcourant][ecriture]){
		$qtesExpdiees = array();
		$erreur = "";
		if (is_array($qtes)){
			foreach ($qtes as $colisid => $lignes) {
				foreach ($lignes as $lignefactureid => $qte) {
					$qtesExpdiees[$lignefactureid] += $qte;
				}
			}
		}
		$lignefactures = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$factureid'");
		while ($lignefacture = $DB_site->fetch_array($lignefactures)) {
			if ($lignefacture[qte] != $qtesExpdiees[$lignefacture[lignefactureid]]) {
				$texteErreur .= "$multilangue[erreur_pour_article] <b>$lignefacture[libelle] ($lignefacture[artcode])</b> :  $multilangue[quantite_s_expediee_s] : ".$qtesExpdiees[$lignefacture[lignefactureid]]." / $lignefacture[qte]<br>";
			}
		}
		
		if ($texteErreur) {
			$listeParamsHidden = "";
			$tabParamsIgnores = array("action", "multiColis");
			foreach ($_POST as $cle => $val) {
				if ($cle && !in_array($cle, $tabParamsIgnores)) {
					if (is_array($val)) {
						foreach($val as $sscle => $ssval) {
							if (is_array($ssval)) {
								foreach($ssval as $sscle2 => $ssval2) {
									$listeParamsHidden .= "<input type=\"hidden\" name=\"".$cle."[".$sscle."][".$sscle2."]\" value=\"$ssval2\">\n";
								}
								
							} else {
								$listeParamsHidden .= "<input type=\"hidden\" name=\"".$cle."[".$sscle."]\" value=\"".$ssval."\">\n";
							}
						}
					} else {
						$listeParamsHidden .= "<input type=\"hidden\" name=\"$cle\" value=\"$val\">\n";
					}
				}
			}
			eval(charge_template($langue, $referencepage, "ExpedierErreur"));
		}else{
			expedierFacture($DB_site, $factureid);
			header("location: commandes.php?action=modifier&factureid=$factureid");
		}
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "reexpedier"){
	if($admin_droit[$scriptcourant][ecriture]){
		$libNavigSupp = "$multilangue[reexpedier_commande] N°$factureid";
		eval(charge_template($langue, $referencepage, "LibNavigSupp"));
		
		$facture=$DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
		
		if(in_array(4, $modules) || $mode == "test_modules"){
			eval(charge_template($langue, $referencepage, "ReexpedierStocks"));
		}
		
		$transporteurs = $DB_site->query("SELECT * FROM mode_livraison_site INNER JOIN mode_livraison USING (modelivraisonid) WHERE siteid = '1'");
		while($transporteur = $DB_site->fetch_array($transporteurs)){
			if($transporteur[modelivraisonid] == $facture[modelivraisonid]){
				$selectedTransp = "selected='selected'";
				$adresse_suivi = $transporteur[URL] ;
			}else{
				$selectedTransp = "";
			}
			eval(charge_template($langue, $referencepage, "ReexpedierTransporteursBit"));
		}
		
		$lignesfacture = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$factureid'");
		while ($lignefacture = $DB_site->fetch_array($lignesfacture)) {
			$TemplateCommandesReexpedierQteBit = "";
			$lignefacture[caracteristiques] = "" ;
			$lignesfacturecaracteristique = $DB_site->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid = '$lignefacture[lignefactureid]'");
			while ($lignefacturecaracteristique = $DB_site->fetch_array($lignesfacturecaracteristique)) {
				if($lignefacturecaracteristique[libcaract] != "" || $lignefacturecaracteristique[libcaractval] != ""){
					$lignefacture[caracteristiques] .= "$lignefacturecaracteristique[libcaract] : $lignefacturecaracteristique[libcaractval]," ;
				}
			}
			
			if ($lignefacture[caracteristiques]) {
				$lignefacture[caracteristiques] = " (" . substr($lignefacture[caracteristiques], 0, -1) . ")" ;
			}
				
			for ($c = 0; $c <= $lignefacture[qte]; $c++) {
				eval(charge_template($langue, $referencepage, "ReexpedierQteBit"));
			}
				
			eval(charge_template($langue, $referencepage, "ReexpedierArticleBit"));
		}
		
		$selectedLM = "";
		$selectedLMme = "";
		if($facture[lcivilite] == "0"){
			$selectedLM = "selected = 'selected'";
			$facture[lcivilite] = $facture[monsieur];
		}else{
			$selectedLMme  = "selected = 'selected'";
			$facture[lcivilite] = $facture[madame];
		}
		$liste_pays_l = retournerListePays($DB_site, $facture[lpaysid]);
		
		if(!empty($facture[letage])){
			eval(charge_template($langue, $referencepage, "ReexpedierCoordLivraisonFormEtage"));
		}
		if(!empty($facture[lescalier])){
			eval(charge_template($langue, $referencepage, "sReexpedierCoordLivraisonFormEscalier"));
		}
		if(!empty($facture[lcodeacces])){
			eval(charge_template($langue, $referencepage, "ReexpedierCoordLivraisonFormCodeAcces"));
		}
		
		eval(charge_template($langue, $referencepage, "Reexpedier"));
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "expedier"){
	if($admin_droit[$scriptcourant][ecriture]){
		$libNavigSupp = "$multilangue[expedier_commande] N°$factureid";
		eval(charge_template($langue, $referencepage, "LibNavigSupp"));
		
		$commande = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");
		
		$livraisonMultiColis = 0;
		if (in_array(5967, $modules) || $mode == "test_modules") {
			$livraisonMultiColis = 1;
		}
		
		if ($livraisonMultiColis) {
			eval(charge_template($langue, $referencepage, "ExpedierHiddenMultiColis"));
			$nbQtes = $DB_site->query_first("SELECT SUM(qte) AS total FROM lignefacture WHERE factureid = '$factureid'");
			$nbColisMax = $nbQtes[total];
			if ($nbColisMax > 10) {
				$nbColisMax = 10;
			}
			
			$fmode=$DB_site->query_first("SELECT modelivraisonid FROM facture where factureid = '$factureid'");
			for ($i = 1; $i <= $nbColisMax; $i++) {
				$selectedNbColis = "";
				if ($nbColis && $nbColis == $i) {
					$selectedNbColis = "selected";
					if($i > $nbColis){
						$display = "style='display: none;'";
					}
				}else{
					if($i != 1){
						$display = "style='display: none;'";
					}
				}
				
				$numeroSuiviColis = "";
				if (isset($numero_suivi[$i]) && $numero_suivi[$i] != "") {
					$numeroSuiviColis = $numero_suivi[$i];
				}
				
				if (isset($transporteur[$i]) && $transporteur[$i] != "") {
					$transporteurColis = $transporteur[$i];
				} else {
					$transporteurColis = $fmode[modelivraisonid];
				}
				
				$transporteurs = $DB_site->query("SELECT * FROM mode_livraison_site INNER JOIN mode_livraison USING (modelivraisonid) WHERE siteid = '1'");
				while($transporteur = $DB_site->fetch_array($transporteurs)){
					if($transporteur[modelivraisonid] == $transporteurColis){
						$selectedTransp = "selected='selected'";
						$adresse_suivi = $transporteur[URL] ;
					}else{
						$selectedTransp = "";
					}
					eval(charge_template($langue, $referencepage, "ExpedierTransporteursBit"));
				}
				$TemplateCommandesExpedierArticleBit = "";
				$lignesfacture = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$factureid'");
				while ($lignefacture = $DB_site->fetch_array($lignesfacture)) {
					$TemplateCommandesExpedierQteBit = "";
					$lignefacture[caracteristiques] = "" ;
					$lignesfacturecaracteristique = $DB_site->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid = '$lignefacture[lignefactureid]'");
					while ($lignefacturecaracteristique = $DB_site->fetch_array($lignesfacturecaracteristique)) {
						if($lignefacturecaracteristique[libcaract] != "" || $lignefacturecaracteristique[libcaractval] != ""){
							$lignefacture[caracteristiques] .= "$lignefacturecaracteristique[libcaract] : $lignefacturecaracteristique[libcaractval]," ;
						}
					}
					
					if ($lignefacture[caracteristiques]) {
						$lignefacture[caracteristiques] = " (" . substr($lignefacture[caracteristiques], 0, -1) . ")" ;
					}
						
					for ($c = 0; $c <= $lignefacture[qte]; $c++) {
						if (isset($qtes[$i][$lignefacture[lignefactureid]]) && $qtes[$i][$lignefacture[lignefactureid]] == $c) {
							$selectedQte = "selected";
						}else{
							$selectedQte = "";
						}
				
						eval(charge_template($langue, $referencepage, "ExpedierQteBit"));
					}
						
					eval(charge_template($langue, $referencepage, "ExpedierArticleBit"));
				}
				eval(charge_template($langue, $referencepage, "ExpedierContenuColisBit"));
				eval(charge_template($langue, $referencepage, "ExpedierNbColisBit"));
			}
			
			eval(charge_template($langue, $referencepage, "ExpedierNbColis"));
			eval(charge_template($langue, $referencepage, "ExpedierMultiColis"));
		}else{
			$transporteurs = $DB_site->query("SELECT * FROM mode_livraison_site INNER JOIN mode_livraison USING (modelivraisonid) WHERE siteid = '1'");
			while($transporteur = $DB_site->fetch_array($transporteurs)){
				if($transporteur[modelivraisonid] == $transporteur[modelivraisonid]){
					$selectedTransp = "selected='selected'";
					$adresse_suivi = $transporteur[URL] ;
				}else{
					$selectedTransp = "";
				}
				eval(charge_template($langue, $referencepage, "ExpedierSimpleColisTransporteursBit"));
			}
			
			$lignesfacture = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$factureid'");
			while ($lignefacture = $DB_site->fetch_array($lignesfacture)) {
				$TemplateCommandesExpedierSimpleColisQteBit = "";
				$lignefacture[caracteristiques] = "" ;
				$lignesfacturecaracteristique = $DB_site->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid = '$lignefacture[lignefactureid]'");
				while ($lignefacturecaracteristique = $DB_site->fetch_array($lignesfacturecaracteristique)) {
					if($lignefacturecaracteristique[libcaract] != "" || $lignefacturecaracteristique[libcaractval] != ""){
						$lignefacture[caracteristiques] .= "$lignefacturecaracteristique[libcaract] : $lignefacturecaracteristique[libcaractval]," ;
					}
				}
				
				if ($lignefacture[caracteristiques]) {
					$lignefacture[caracteristiques] = " (" . substr($lignefacture[caracteristiques], 0, -1) . ")" ;
				}
					
				eval(charge_template($langue, $referencepage, "ExpedierSimpleColisArticleBit"));
			}
			
			eval(charge_template($langue, $referencepage, "ExpedierSimpleColis"));
		}
		
		eval(charge_template($langue, $referencepage, "Expedier"));
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "multiple"){
	if($admin_droit[$scriptcourant][ecriture]){
		if(sizeof($chk) > 0){
			switch ($select_multiple){
				case "":
					header("location: commandes.php?erreur=1");
					break;
				case "exp":
					foreach ($chk as $key => $value){
						$etat = $DB_site->query_first("SELECT etatid FROM facture WHERE factureid = '$key'");
						if($etat[etatid] != '1'){
							unset($chk[$key]);
						}
					}
					$fichier = fopen("export/csv/expeditor.csv", "w+");
					fseek($fichier, 0);
					$filename = "export/csv/expeditor.csv";
					/*$en_tete = "Référence expédition;Code produit;Code destinataire(Num client);Email;Nom destinataire;Adresse 1;Adresse 2;Adresse 3;Adresse 4;Code postal;Commune;Code pays;Telephone;Portable;Date expedition;Code point de retrait\n";*/
					//fputs($fichier, $en_tete);
					
					while (list($key, $val) = each($chk)) {
						if($val=="on"){
							//modif benj les codes expeditor sont dans la table mode_livraison, colonne code_expeditor
							$rs_expeditor=$DB_site->query_first("SELECT f.factureid, f.userid, f.lcivilite, f.lnom, f.lprenom, f.raison_sociale, f.ladresse, f.ladresse2, f.lcodepostal, f.lville, f.ltelephone, f.lraison_sociale,
																	p.diminutif, f.modelivraisonid, f.modelivraisontypeid, f.pointrelaisid, f.telephone2, f.mail, p.europe, f.lpaysid, f.simplicitePRID, f.simpliciteDELIVERYMODE
																	FROM facture f INNER JOIN pays p ON (f.lpaysid = p.paysid)
																	WHERE f.factureid = $key");
							if ($rs_expeditor['factureid'] != "" && $rs_expeditor['factureid']>0){
								$rq_ligneexpeditors=$DB_site->query("SELECT qte, poids FROM lignefacture WHERE factureid = '$rs_expeditor[factureid]'");
								$poids_total = 0;
								while ($rq_ligneexpeditor=$DB_site->fetch_array($rq_ligneexpeditors)){
									$poids_total += $rq_ligneexpeditor[qte] * $rq_ligneexpeditor[poids];
								}
								if ($rs_expeditor[simpliciteDELIVERYMODE]) {
									$codeproduit = $rs_expeditor[simpliciteDELIVERYMODE];
								} else {
									$mode_livraison=$DB_site->query_first("SELECT code_expeditor FROM mode_livraison WHERE modelivraisonid='$rs_expeditor[modelivraisonid]'");
									$codeproduit = $mode_livraison[code_expeditor];
									if ($rs_expeditor[modelivraisontypeid]) {
										$mode_livraison_type = $DB_site->query_first("SELECT reference FROM mode_livraison_type WHERE modelivraisontypeid='$rs_expeditor[modelivraisontypeid]'");
										if ($mode_livraison_type[reference]) {
											$codeproduit = $mode_livraison_type[reference];
										}
									}
								}
								$codePointRelais = "";
								if ($rs_expeditor[pointrelaisid]) {
									$pointrelais = $DB_site->query_first("SELECT reference FROM pointrelais WHERE pointrelaisid = '$rs_expeditor[pointrelaisid]'");
									$codePointRelais = $pointrelais[reference];
								} elseif ($rs_expeditor[simplicitePRID]) {
									$codePointRelais = $rs_expeditor[simplicitePRID];
								}
								$rs_expeditor[lcivilite] += 2;
								$rs_expeditor[ladresse] = str_replace(";", ",", $rs_expeditor[ladresse]);
								$rs_expeditor[ladresse2] = str_replace(";", ",", $rs_expeditor[ladresse2]);
								
								$adresse3="";
								if(strlen($rs_expeditor[ladresse]) > 35 && $rs_expeditor[ladresse2] == ""){
									$exp_adresse=explode("||",wordwrap($rs_expeditor[ladresse], 35, "||"));
									$rs_expeditor[ladresse] = $exp_adresse[0];
									$rs_expeditor[ladresse2] = $exp_adresse[1];
								
									if($exp_adresse[2] != ""){
										$adresse3=$exp_adresse[2];
									}
								}
								
								$numfacture = $rs_expeditor[factureid] ;
								while (strlen($numfacture) < 7){
									$numfacture = "0".$numfacture ;
								}
								$numfacture = $params[prefixe_numcommande].$numfacture;
								//Num cde
								$line = "".$numfacture.";";
								//Nom
								$line .=$rs_expeditor[lnom].";";
								//Adresse 3
								$line .=$adresse3.";";
								//Adresse 1
								$line .=$rs_expeditor[ladresse].";";
								//Adresse 2
								$line .=$rs_expeditor[ladresse2].";";
								//Code postal
								$line .=$rs_expeditor[lcodepostal].";";
								//Ville
								$line .=$rs_expeditor[lville].";";
								//Pays
								$line .=$rs_expeditor[diminutif].";";
								//vide
								$line .=";";
								//vide
								$line .=";";
								//tel fixe
								$line .=$rs_expeditor[ltelephone].";";
								//date
								$line .=";";
								//vide
								$line .=";";
								//email
								$line .=$rs_expeditor[mail].";";
								//? mettre à 1
								$line .="1;";
								//Prénom
								$line .=$rs_expeditor[lprenom].";";
								//raison sociale
								$line .=$rs_expeditor[lraison_sociale].";";
								//tel portable
								$line .=$rs_expeditor[telephone2].";";
								$line .= "\n";
								
								/*if($rs_expeditor[lraison_sociale]){
									$line = "".$rs_expeditor[factureid].";".$rs_expeditor[userid].";".$rs_expeditor[lcivilite].";".$rs_expeditor[lnom].";".$rs_expeditor[lprenom].";".$rs_expeditor[lraison_sociale].";".$rs_expeditor[ladresse].";".$rs_expeditor[ladresse2].";;".$rs_expeditor[lcodepostal].";".$rs_expeditor[lville].";".$rs_expeditor[diminutif].";".$rs_expeditor[ltelephone].";".$rs_expeditor[telephone2].";".$rs_expeditor[mail].";".date('dmY').";".$poids_total.";".$codeproduit.";".$codePointRelais.";".$nomCommercialChargeurExpeditor.";\n";
								}else{
									$line = "".$rs_expeditor[factureid].";".$rs_expeditor[userid].";".$rs_expeditor[lcivilite].";".$rs_expeditor[lnom].";".$rs_expeditor[lprenom].";".$rs_expeditor[ladresse].";".$rs_expeditor[ladresse2].";;;".$rs_expeditor[lcodepostal].";".$rs_expeditor[lville].";".$rs_expeditor[diminutif].";".$rs_expeditor[ltelephone].";".$rs_expeditor[telephone2].";".$rs_expeditor[mail].";".date('dmY').";".$poids_total.";".$codeproduit.";".$codePointRelais.";".$nomCommercialChargeurExpeditor.";\n";
								}*/
								
								fputs($fichier, $line);
								
								// Ajouter les lignes de facture pour la Cn23
								if ($params[export_auto_cn23] == "1" && ($rs_expeditor[europe] == "0" || $rs_expeditor[lpaysid] == "158")) {
									$rq_ligneexpeditors = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$rs_expeditor[factureid]'");
									while ($rq_ligneexpeditor=$DB_site->fetch_array($rq_ligneexpeditors)){
										$paysOrigine = $DB_site->query_first("SELECT diminutif FROM pays WHERE paysid = '$rq_ligneexpeditor[pays_origine]'");
										if ($paysOrigine[diminutif] == "") {
											$paysOrigine[diminutif] = "FR";
										}
										$line = "CN2;".substr(stripslashes($rq_ligneexpeditor[libelle]), 0, 25).";".$rq_ligneexpeditor[numero_tarifaire_laposte].";".$paysOrigine[diminutif].";$rq_ligneexpeditor[poids];$rq_ligneexpeditor[prix];$rq_ligneexpeditor[qte]\n";
										fputs($fichier, $line);
									}
								}
								$DB_site->query("UPDATE facture SET exportcsv = '1' WHERE factureid = '$key'");
							} else {
								echo "erreur";
							}
						}
					}
					fclose($fichier);
					
					if (file_exists($filename)) {
						if(!is_dir($filename)){
							header('Content-Description: File Transfer');
							header('Content-Type: application/octet-stream');
							header('Content-Disposition: attachment; filename='.basename($filename));
							header('Content-Transfer-Encoding: binary');
							header('Expires: 0');
							header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
							header('Pragma: public');
							header('Content-Length: ' . filesize($filename));
							ob_clean();
							flush();
							readfile($filename);
							return "Downloading $filename";
							exit;
						}
					}
					break;
				case "supprimer":
					$ids_facture = "";
					foreach ($chk as $key => $value){
						$etat = $DB_site->query_first("SELECT etatid FROM facture WHERE factureid = '$key'");
						if($etat[etatid] == '0'){
							$infos_facture = $DB_site->query_first("SELECT factureid, montanttotal_ttc, siteid, prenom, nom FROM facture WHERE factureid = '$key'");
							$ids_facture .= "$infos_facture[factureid],";
							$devise_actuel = $tabsites[$infos_facture[siteid]][devise_complete];
							eval(charge_template($langue, $referencepage, "ModalSupprInfos"));
						}
					}
					$ids_facture = substr($ids_facture, 0, -1);
					eval(charge_template($langue, $referencepage, "ModalSupprMultiple"));
					$action = "";
					break;
				case "chk_bc":
					foreach ($chk as $key => $value){
						$etat = $DB_site->query_first("SELECT etatid FROM facture WHERE factureid = '$key'");
						if($etat[etatid] == '0' || $etat[etatid] == '2' || $etat[etatid] == '3'){
							unset($chk[$key]);
						}
					}
					if(sizeof($chk) > 0){
						$retourImpressionMultiple = lancerImpressionMultiple($DB_site, $select_multiple, $chk);
					}
					break;
				case "chk_fact":
					foreach ($chk as $key => $value){
						$etat = $DB_site->query_first("SELECT etatid FROM facture WHERE factureid = '$key'");
						if($etat[etatid] == '0' || $etat[etatid] == '1' || $etat[etatid] == '2' || $etat[etatid] == '3'){
							unset($chk[$key]);
						}
					}
					if(sizeof($chk) > 0){
						$retourImpressionMultiple = lancerImpressionMultiple($DB_site, $select_multiple, $chk);
					}
					break;
				case "chk_bl":
					foreach ($chk as $key => $value){
						$etat = $DB_site->query_first("SELECT etatid FROM facture WHERE factureid = '$key'");
						if($etat[etatid] == '0' || $etat[etatid] == '2' || $etat[etatid] == '3'){
							unset($chk[$key]);
						}
					}
					if(sizeof($chk) > 0){
						$retourImpressionMultiple = lancerImpressionMultiple($DB_site, $select_multiple, $chk);
					}
					break;
				case "chk_bp":
					foreach ($chk as $key => $value){
						$etat = $DB_site->query_first("SELECT etatid FROM facture WHERE factureid = '$key'");
						if($etat[etatid] == '0' || $etat[etatid] == '2' || $etat[etatid] == '3'){
							unset($chk[$key]);
						}
					}
					if(sizeof($chk) > 0){
						$retourImpressionMultiple = lancerImpressionMultiple($DB_site, $select_multiple, $chk);
					}
					break;
				case "chk_not":
					foreach ($chk as $key => $value){
						$etat = $DB_site->query_first("SELECT etatid FROM facture WHERE factureid = '$key'");
						if($etat[etatid] == '0' || $etat[etatid] == '2' || $etat[etatid] == '3'){
							unset($chk[$key]);
						}
					}
					if(sizeof($chk) > 0){
						$retourImpressionMultiple = lancerImpressionMultiple($DB_site, $select_multiple, $chk);
					}
					break;
			}
			$action = "";
			eval(charge_template($langue, $referencepage, "RetourImpressionMultiple"));
		}else{
			header("location: commandes.php?erreur=2");
		}
	}else{
		header('location: commandes.php?erreurdroits=1');	
	}
}

/** --- avoir le fichier zip de l'article de la commande--- **/
/**
if(isset($action) && $action == "zipfile"){
    echo "test";
    exit();
}
/*
/** --- avoir le fichier zip de l'article de la commande--- **/
if(isset($action) && $action == "modifier"){
	$libNavigSupp = "$multilangue[commande] N°$factureid";
	eval(charge_template($langue, $referencepage, "LibNavigSupp"));
	
	$commande = $DB_site->query_first("SELECT * FROM facture WHERE factureid = '$factureid'");

	$canDeleteFacture = true;
	//Affichage du référent
	if($commande[referentid] == "0"){
		$referent = $multilangue[inconnu];
	}else{
		$referent = $DB_site->query_first("SELECT referent FROM referents WHERE referentid = '$commande[referentid]'");	
		$referent = $referent[referent];
	}
	
	//Affichage des infos de la commande
	$moyen_paiement = $DB_site->query_first("SELECT libelle FROM moyenpaiement_site WHERE siteid= '1' AND moyenid = '$commande[moyenid]'");
	$etat = $DB_site->query_first("SELECT couleur, libelle FROM etatfacture INNER JOIN etatfacture_langue USING (etatid) WHERE etatid = '$commande[etatid]'");
	$mode_livraison = $DB_site->query_first("SELECT nom FROM mode_livraison_site WHERE modelivraisonid = '$commande[modelivraisonid]' ");
	$commande[montanttotal_ttc_regle] = formaterPrix($commande[montanttotal_ttc_regle]);
	$commande[montanttotal_ttc] = formaterPrix($commande[montanttotal_ttc]);
	$devise_actuelle = $tabsites[$commande[siteid]][devise_complete];
	$date = date("d/m/Y H:i:s", $commande[timestamp2]);
	$commande[dateexpedition] = date("d-m-Y", strtotime($commande[dateexpedition]));
	$commande[datefacture] = date("d-m-Y", strtotime($commande[datefacture]));
	$commande[timestamp] = date("d-m-Y H:i:s", strtotime($commande[timestamp]));

	$moyens = $DB_site->query("SELECT * FROM moyenpaiement_site WHERE siteid='1'");
	while($moyen = $DB_site->fetch_array($moyens)){
		
		if($moyen[moyenid] == $commande[moyenid]){
			$selectedMoyen = "selected = 'selected'";
		}else{
			$selectedMoyen = "";
		}
		eval(charge_template($langue, $referencepage, "ModificationLigneActionValiderReglementListeMoyensBit"));
	}
	
	if($commande[ip]){
		$paysOrigine = $DB_site->query_first("SELECT libelle FROM iptocountry ip INNER JOIN pays p ON (ip.paysid = p.paysid) WHERE ipstart <='$commande[ip]' AND ipend >='$commande[ip]'");
		$ipCommande = chainetoip($commande[ip]);
		$ipbloquer=$DB_site->query("SELECT * FROM ipbloquer WHERE ipdeb <= '$commande[ip]' AND ipfin >= '$commande[ip]' AND bloquer = '1'");
		if ($DB_site->num_rows($ipbloquer) > 0) {
			$namebtbloquerip = $multilangue[autoriser_ip];
			$actionip = "autoriser";
		}else{
			$namebtbloquerip = $multilangue[bloquer_ip];
			$actionip = "bloquer";
		}
	}
	//Affichage des infos clients
	$utilisateur=$DB_site->query_first("SELECT * FROM utilisateur WHERE userid='$commande[userid]' ");
	
	$selectedM = "";
	$selectedMme = ""; 
	if($commande[civilite] == "0"){
		$selectedM = "selected = 'selected'";
		$commande[civilite] = $multilangue[monsieur];
	}else{
		$selectedMme  = "selected = 'selected'";
		$commande[civilite] = $multilangue[madame];
	}
	$liste_pays = retournerListePays($DB_site, $commande[paysid]);
	$libelle_pays = retournerLibellePays($DB_site, $commande[paysid]);
	
	$etage = false;
	$escalier = false;
	$codeacces = false;
	$letage = false;
	$lescalier = false;
	$lcodeacces = false;
	if(!empty($commande[etage])){
		$etage = true;
	}
	if(!empty($commande[escalier])){
		$escalier = true;
	}
	if(!empty($commande[codeacces])){
		$codeacces = true;
	}
	
	if(!empty($commande[letage])){
		$letage = true;
	}
	if(!empty($commande[lescalier])){
		$lescalier = true;
	}
	if(!empty($commande[lcodeacces])){
		$lcodeacces = true;
	}
	
	$selectedLM = "";
	$selectedLMme = "";
	if($commande[lcivilite] == "0"){
		$selectedLM = "selected = 'selected'";
		$commande[lcivilite] = $commande[monsieur];
	}else{
		$selectedLMme  = "selected = 'selected'";
		$commande[lcivilite] = $commande[madame];
	}
	$liste_pays_l = retournerListePays($DB_site, $commande[lpaysid]);
	$libelle_pays_l = retournerLibellePays($DB_site, $commande[lpaysid]);
	
	//Mails envoyés
	$emailsSend = $DB_site->query("SELECT * FROM emails_envoyes WHERE destinataire = '$commande[mail]' ORDER BY emailenvoyeid DESC");
	while($emailSend = $DB_site->fetch_array($emailsSend)){
		$datemail = date("d/m/Y $multilangue[a] H:i:s", $emailSend[dateline]);
		// $emailSend[contenu] = strip_tags(gzuncompress($emailSend[contenu]));
		$emailSend[contenu] = strip_tags($emailSend[contenu]);
		eval(charge_template($langue, $referencepage, "ModificationEmailsEnvoyesBit"));
	}
	
	//Détail des produits de la commande
	$lignes_facture = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$factureid' AND deleted = '0'");
	while($ligne_facture = $DB_site->fetch_array($lignes_facture)){
		if($ligne_facture[tva] != "0"){
			$tva = $ligne_facture[tva];
		}
		
		if($ligne_facture[prixachat] == "0"){
			$ligne_facture[prixachat] = "0.00";
		}
		$ligne_facture[caracteristiques] = "";
		
		$TemplateCommandesModificationDetailProduitsModifiableCaractBit = "";
		$TemplateCommandesModificationDetailProduitsModifiableBtnsVisu = "";
		$TemplateCommandesModificationDetailProduitsModifiableImage = "";
		$TemplateCommandesModificationDetailProduitsAjoutArticle = "";
		$TemplateCommandesModificationDetailProduitsModifiableNumeroSerie = "";
		$TemplateCommandesModificationDetailProduitsModifiableQte = "";
		$TemplateCommandesModificationDetailProduitsModifiablePrixAuMetre = "";
		$TemplateCommandesModificationDetailProduitsModifiablePersonnalisationBit = "";
		
		$TemplateCommandesModificationDetailProduitsNonModifiableBtnsVisu = "";
		$TemplateCommandesModificationDetailProduitsNonModifiableImage = "";
		$TemplateCommandesModificationDetailProduitsNonModifiableNumeroSerie = "";
		$TemplateCommandesModificationDetailProduitsNonModifiableRemboursement = "";
		$TemplateCommandesModificationDetailProduitsNonModifiablePrixAuMetre = "";
		$TemplateCommandesModificationDetailProduitsNonModifiableQte = "";
		$TemplateCommandesModificationDetailProduitsNonModifiablePersonnalisation = "";
		
		
		//Etats 0/1, on peut modifier les articles
		if($commande[etatid] == "0" || $commande[etatid] == "1"){
			
			if($ligne_facture[artid] == "0"){
				$reference = $ligne_facture[artcode];
				$designation = $ligne_facture[libelle];
				$qte = $ligne_facture[qte];
				$prixachat = $ligne_facture[prixachat];
				$prixachat = formaterPrix($prixachat);
				$prix = $ligne_facture[prix]*$qte;
				$prix = formaterPrix($prix);
				$supprimerLigne = "supprimerLigneChamp";
				$modif_ligne = "champ";
			}else{
				$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$ligne_facture[artid]' AND siteid = '1'");
				$regleurlrewriteDefaut = $regleurlrewrite[1];				
				$article[librewrite] = url_rewrite($article[libelle]);
				$article[url] = "http://$host/$regleurlrewriteDefaut[article]-$article[librewrite]-$article[artid].htm";
				
				
				$reference = $ligne_facture[artcode];
				$designation = $ligne_facture[libelle];
				if((in_array(5937, $modules) || $mode == "test_modules") && $ligne_facture[nbcm]){
					$nbcm = $ligne_facture[nbcm];
					eval(charge_template($langue, $referencepage, "ModificationDetailProduitsModifiablePrixAuMetre"));
					eval(charge_template($langue, $referencepage, "ModificationDetailProduitsAjoutArticlePrixAuMetre"));
				}else{
					$qte = $ligne_facture[qte];
					eval(charge_template($langue, $referencepage, "ModificationDetailProduitsModifiableQte"));
					eval(charge_template($langue, $referencepage, "ModificationDetailProduitsAjoutArticleQte"));
				}
				$prixachat = $ligne_facture[prixachat];
				$prixachat = formaterPrix($prixachat);
				$prix = $ligne_facture[prix]*$qte;
				$prix = formaterPrix($prix);
				$supprimerLigne = "supprimerLigne";
				$modif_ligne = "ligne";

				$lignes_facture_caracteristique = $DB_site->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid = '$ligne_facture[lignefactureid]'");	
				while($ligne_facture_caracteristique = $DB_site->fetch_array($lignes_facture_caracteristique)){
					
					$rq_libcaract = $DB_site->query_first("SELECT * FROM caracteristique_site WHERE caractid = '$ligne_facture_caracteristique[caractid]' AND siteid='1'");
					$libcaract = $rq_libcaract[libelle];
					
					$caractid = $ligne_facture_caracteristique[caractid];
					$TemplateCommandesModificationDetailProduitsModifiableCaractValeurBit = "";
					$caracteristiques = $DB_site->query("SELECT * FROM caracteristiquevaleur AS cv 
															INNER JOIN caracteristiquevaleur_site AS cvs ON cv.caractvalid = cvs.caractvalid 
															INNER JOIN article_caractval AS acv ON cvs.caractvalid = acv.caractvalid
															WHERE cv.caractid = '$ligne_facture_caracteristique[caractid]' AND acv.artid = '$ligne_facture[artid]' AND cvs.siteid = '1' ");
					while($caracteristique = $DB_site->fetch_array($caracteristiques)){
						if($caracteristique[caractvalid] == $ligne_facture_caracteristique[caractvalid] ){
							$selectedCaract = "selected = 'selected'";	
						}else{
							$selectedCaract = "";
						}
						$caractvalid = $caracteristique[caractvalid];
						$caractvallib = $caracteristique[libelle];
						eval(charge_template($langue, $referencepage, "ModificationDetailProduitsModifiableCaractValeurBit"));
					}
					eval(charge_template($langue, $referencepage, "ModificationDetailProduitsModifiableCaractBit"));
				}
				
				if(in_array(5804, $modules) || $mode == "test_modules"){
					eval(charge_template($langue, $referencepage, "ModificationDetailProduitsModifiableNumeroSerie"));
				}
				
				//Module Photo par caractéristique
				/*$imageParCaractTrouvee = 0;
				if (in_array(5941, $modules) || $mode == "test_modules") {
					$existePhoto = $DB_site->query_first("SELECT * FROM article_caractval_photo WHERE artid = '$ligne_facture[artid]' AND caractvalid IN (SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid = '$ligne_facture[lignefactureid]') ORDER BY position LIMIT 1");
					if ($existePhoto[articlecaractvalphotoid] && $existePhoto[image]) {
						$ligne_facture[image] = "<img src=\"http://$host/supc-$article[librewrite]-$existePhoto[articlecaractvalphotoid].$existePhoto[image]\">" ;
						$imageParCaractTrouvee = 1;
					}
				}
				if (!$imageParCaractTrouvee) {
					if (file_exists($rootpath."configurations/".$host."/images/produits/suppl/".$ligne_facture[artid].".jpg"))
						$ligne_facture[image] = "<img src=\"http://".$host."/sup-".$article[librewrite]."-".$ligne_facture[artid].".jpg\">" ;
					elseif (file_exists($rootpath."configurations/".$host."/images/produits/suppl/".$ligne_facture[artid].".gif"))
						$ligne_facture[image] = "<img src=\"http://".$host."/sup-".$article[librewrite]."-".$ligne_facture[artid].".gif\">" ;
				}*/
                /** --- set image module preview --- **/
				$ligne_facture[image] = "<img src=".$ligne_facture[urlimage]." style=\"width:100px;height:auto\" />";
				/** --- set image module preview --- **/
				//Module multi mode livraison
				if(in_array(5867, $modules) || $mode == "test_modules") {
					$multiMode = "";
					$verifMutltiMode = $DB_site->query_first("SELECT modelivraison_multi FROM facture WHERE factureid = '$ligne_facture[factureid]'");
					if($verifMutltiMode[modelivraison_multi] != 0) {
						$verifMutltiMode = $DB_site->query_first("SELECT modelivraison_multi_article FROM lignefacture WHERE lignefactureid = '$ligne_facture[lignefactureid]'");
						if($verifMutltiMode[modelivraison_multi_article] != 0) {
							$modesBis = $DB_site->query("SELECT nom FROM mode_livraison_site WHERE modelivraisonid IN ($verifMutltiMode[modelivraison_multi_article])");
							$multiMode .= "<br>$multilangue[mode_de_livraison] : ";
							$separateur  = "";
							while ($modeBis=$DB_site->fetch_array($modesBis)) {
								$multiMode .= $separateur."<i>".$modeBis[$nom]."</i>";
								$separateur = " $multilangue[ou] ";
							}
						}
					}
					eval(charge_template($langue, $referencepage, "ModificationDetailProduitsModifiableMultiMode"));
				}
				
				//Module Personnalisation
				if(in_array(113, $modules) || $mode == "test_modules"){
					$champs = $DB_site->query("SELECT * FROM article_champ INNER JOIN article_champ_site USING (articlechampid) WHERE artid = '$ligne_facture[artid]' AND siteid = '$commande[siteid]'");
					while($champ = $DB_site->fetch_array($champs)){
						$displayInputFile = "display: none";
						$champForm = "";
						$valeurs = $DB_site->query("SELECT * FROM article_champ_valeur INNER JOIN article_champ_valeur_site USING (articlechampvaleurid) WHERE articlechampid = '$champ[articlechampid]' AND siteid = '$commande[siteid]'");
						switch ($champ[type]){
							case "1": // boite texte
								$vals ="" ;
								$lignefacture_valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$ligne_facture[lignefactureid]'");
								while ($valeur=$DB_site->fetch_array($valeurs)){
									$vals .= "$valeur[libelle]='$valeur[valeur]' " ;
								}
								$champForm = "<textarea class='form-control' id='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]' name='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]' $vals>$lignefacture_valeur[valeur]</textarea>";
								break ;
							case "2": // Boutons radio
								$lignefacture_valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$ligne_facture[lignefactureid]'");
								$champForm = "<div class='radio-list radio-inline'>" ;
								while ($valeur=$DB_site->fetch_array($valeurs)){
									if($valeur[valeur] == $lignefacture_valeur[valeur]){
										$selected = "checked";
									}else{
										$selected = "";	
									}
									$champForm .= "<label><input type='radio' class='radio-inline' id='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]' name='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]' $valeur[libelle]='$valeur[valeur]' $selected> $valeur[description]</label>";
								}
								$champForm .= "</div>";
								break ;
							case "3": // Cases à cocher
								$champForm = "<div class='checkbox-list>'" ;
								while ($valeur=$DB_site->fetch_array($valeurs)){
									$lignefacture_valeurs = $DB_site->query("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$ligne_facture[lignefactureid]'");
									$selected = "";
									while($lignefacture_valeur = $DB_site->fetch_array($lignefacture_valeurs)){
										if($valeur[valeur] == $lignefacture_valeur[valeur]){
											$selected = "checked";
										}
									}
									$champForm .= "<label><input type='checkbox' id='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]' name='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]' $valeur[libelle]='$valeur[valeur]' $selected> $valeur[description]</label>";
								}
								$champForm .= "</div>"; 
								break ;
							case "4": // Champ texte
								$lignefacture_valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$ligne_facture[lignefactureid]'");
								$vals ="" ;
								while ($valeur=$DB_site->fetch_array($valeurs)){
									$vals .= "$valeur[libelle]='$valeur[valeur]' " ;
								}
								$champForm = "<input type='text' class='form-control' id='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]' name='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]' value='$lignefacture_valeur[valeur]' $vals>" ;
								break ;
							case "5": // Liste à sélection unique
								$lignefacture_valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$ligne_facture[lignefactureid]'");
								$options = "" ;
								while ($valeur=$DB_site->fetch_array($valeurs)){
									if($valeur[valeur] == $lignefacture_valeur[valeur]){
										$selected = "selected='selected'";
									}else{
										$selected = "";
									}
									$options .= "<option $valeur[libelle]='$valeur[valeur]' $selected>$valeur[description]</option>" ;
								}
								$champForm = "<select class='form-control' id='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]' name='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]'><option value=''></option>$options</select>" ;
								break ;
							case "6": // Liste à sélections multiples
								$options = "" ;
								while ($valeur=$DB_site->fetch_array($valeurs)){
									$lignefacture_valeurs = $DB_site->query("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$ligne_facture[lignefactureid]'");
									$selected = "";
									while($lignefacture_valeur = $DB_site->fetch_array($lignefacture_valeurs)){
										if($valeur[valeur] == $lignefacture_valeur[valeur]){
											$selected = "selected";
										}
									}
									$options .= "<option $valeur[libelle]=$valeur[valeur] $selected>$valeur[description]</option>" ;
								}
								$champForm = "<select class='form-control' multiple='multiple' id='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]' name='perso[$ligne_facture[lignefactureid]][$champ[articlechampid]]'><option value=''></option>$options</select>" ;
								break ;
							case "7": // Insertion de fichier
								$lignefacture_valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ[articlechampid]' AND lignefactureid = '$ligne_facture[lignefactureid]'");
								if($lignefacture_valeur[valeur]){
									$champForm = "<a target='_blank' href='http://$host/uploads-$lignefacture_valeur[valeur]'><input type='button' onclick='fichierPersonnalisation' value='$multilangue[changer]'>";
								}else{
									$displayInputFile = "";
								}
								break ;
						}
						eval(charge_template($langue, $referencepage, "ModificationDetailProduitsModifiablePersonnalisationBit"));
					}
				}
				
				$urlzip = $ligne_facture[urlzip];
				eval(charge_template($langue, $referencepage, "ModificationDetailProduitsModifiableBtnsVisu"));
				eval(charge_template($langue, $referencepage, "ModificationDetailProduitsModifiableImage"));
			}
			eval(charge_template($langue, $referencepage, "ModificationDetailProduitsModifiableBit"));

		//Etats 2/3/5/6, on affiche seulement les informations des produits
		}else{
			
			if($ligne_facture[artid] == "0"){
				$reference = $ligne_facture[artcode];
				$designation = $ligne_facture[libelle];
				$qte = $ligne_facture[qte];
				$prixachat = $ligne_facture[prixachat];
				$prixachat = formaterPrix($prixachat);
				$prix = $ligne_facture[prix]*$qte;
				$prix = formaterPrix($prix);
			}else{
				$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$ligne_facture[artid]' AND siteid = '1'");
				$regleurlrewriteDefaut = $regleurlrewrite[1];
				$article[librewrite] = url_rewrite($article[libelle]);
				$article[url] = "http://$host/$regleurlrewriteDefaut[article]-$article[librewrite]-$article[artid].htm";
				
				$reference = $ligne_facture[artcode];
				$designation = $ligne_facture[libelle];
				if((in_array(5937, $modules) || $mode == "test_modules") && $ligne_facture[nbcm]){
					$nbcm = $ligne_facture[nbcm];
					eval(charge_template($langue, $referencepage, "ModificationDetailProduitsNonModifiablePrixAuMetre"));
				}else{
					$qte = $ligne_facture[qte];
					eval(charge_template($langue, $referencepage, "ModificationDetailProduitsNonModifiableQte"));
				}
				
				$prixachat = $ligne_facture[prixachat];
				$prixachat = formaterPrix($prixachat);
				$prix = $ligne_facture[prix]*$qte;
				$prix = formaterPrix($prix);

				$lignes_facture_caracteristique=$DB_site->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid = '$ligne_facture[lignefactureid]' order by lignefacturecaracteristiqueid");
				while ($ligne_facture_caracteristique = $DB_site->fetch_array($lignes_facture_caracteristique)){
					if($ligne_facture_caracteristique[libcaract] == "" || $ligne_facture_caracteristique[libcaractval] == ""){
						$libcaract = $DB_site->query_first("SELECT libelle FROM caracteristique_site WHERE caractid = '$ligne_facture_caracteristique[caractid]' AND siteid = '1'");
						$libcaractval = $DB_site->query_first("SELECT libelle FROM caracteristiquevaleur_site WHERE caractvalid = '$ligne_facture_caracteristique[caractvalid]' AND siteid = '1'");
						
						$ligne_facture_caracteristique[libcaract] = $libcaract[libelle];
						$ligne_facture_caracteristique[libcaractval] = $libcaractval[libelle];
					}
					$ligne_facture[caracteristiques] .= "$ligne_facture_caracteristique[libcaract] : $ligne_facture_caracteristique[libcaractval]<br>" ;
				}
				
				if($commande[etatid] == 5 && $ligne_facture[lignefactureidremboursement] == 0 && !$commande[avoir_parentid]){
					$qtesRemboursees = $DB_site->query_first("SELECT SUM(qte) FROM lignefacture WHERE lignefactureidremboursement = '$ligne_facture[lignefactureid]'");
					$qtesRestantes = $ligne_facture[qte] + $qtesRemboursees[0];
					if ($qtesRestantes > 0){
						eval(charge_template($langue, $referencepage, "ModificationDetailProduitsNonModifiableRemboursement"));
					}
				}
				
				if(in_array(5804, $modules) || $mode == "test_modules"){
					eval(charge_template($langue, $referencepage, "ModificationDetailProduitsNonModifiableNumeroSerie"));
				}
				
				//Module photo par caracteristique
				/*$imageParCaractTrouvee = 0;
				if (in_array(5941, $modules) || $mode == "test_modules") {
					$existePhoto = $DB_site->query_first("SELECT * FROM article_caractval_photo WHERE artid = '$ligne_facture[artid]' AND caractvalid IN (SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid = '$ligne_facture[lignefactureid]') ORDER BY position LIMIT 1");
					if ($existePhoto[articlecaractvalphotoid] && $existePhoto[image]) {
						$ligne_facture[image] = "<img src=\"http://$host/supc-$article[librewrite]-$existePhoto[articlecaractvalphotoid].$existePhoto[image]\">" ;
						$imageParCaractTrouvee = 1;
					}
				}
				if (!$imageParCaractTrouvee) {
					if (file_exists($rootpath."configurations/".$host."/images/produits/suppl/".$ligne_facture[artid].".jpg"))
						$ligne_facture[image] = "<img src=\"http://".$host."/sup-".$article[librewrite]."-".$ligne_facture[artid].".jpg\">" ;
					elseif (file_exists($rootpath."configurations/".$host."/images/produits/suppl/".$ligne_facture[artid].".gif"))
					$ligne_facture[image] = "<img src=\"http://".$host."/sup-".$article[librewrite]."-".$ligne_facture[artid].".gif\">" ;
				}*/
				
				$ligne_facture[image] = "<img src=".$ligne_facture[urlimage]." style=\"width:100px;height:auto\" />";
				//Module Personnalisation
				if(in_array(113, $modules) || $mode == "test_modules"){
					$personnalisation = "$multilangue[personnalisation] : <br>";
					$champs_article = $DB_site->query("SELECT * FROM article_champ INNER JOIN article_champ_site USING (articlechampid) WHERE artid = '$ligne_facture[artid]' AND siteid = '$commande[siteid]'");
					if($DB_site->num_rows($champs_article) > 0){
						while($champ_article = $DB_site->fetch_array($champs_article)){
							$valeur = $DB_site->query_first("SELECT * FROM lignefacturechamp WHERE articlechampid = '$champ_article[articlechampid]'");
							$personnalisation .= "<label>$champ_article[libelle] => $valeur[valeur]<br>";
						}
						eval(charge_template($langue, $referencepage, "ModificationDetailProduitsNonModifiablePersonnalisation"));
					}
				}
				eval(charge_template($langue, $referencepage, "ModificationDetailProduitsNonModifiableBtnsVisu"));
				eval(charge_template($langue, $referencepage, "ModificationDetailProduitsNonModifiableImage"));
				eval(charge_template($langue, $referencepage, "ModificationDetailProduitsNonModifiableBit"));
			}	
			
			
		}
		
	}
	
	//Module article offert
	if (in_array(5874, $modules) || $mode == "test_modules") {
		$lignesfactureoffert=$DB_site->query("SELECT * FROM lignefactureoffert WHERE factureid = '$factureid'");
		while ($lignefactureoffert=$DB_site->fetch_array($lignesfactureoffert)) {
			$lignes_facture_offert_caracteristique=$DB_site->query("SELECT * FROM lignefactureoffertcaracteristique WHERE lignefactureoffertid = '$lignefactureoffert[lignefactureoffertid]' ORDER BY lignefactureoffertcaracteristiqueid");
			while ($ligne_facture_offert_caracteristique = $DB_site->fetch_array($lignes_facture_offert_caracteristique)){
				if($ligne_facture_offert_caracteristique[libcaract] == "" || $ligne_facture_offert_caracteristique[libcaractval] == ""){
					$libcaract = $DB_site->query_first("SELECT libelle FROM caracteristique_site WHERE caractid = '$ligne_facture_offert_caracteristique[caractid]' AND siteid = '1'");
					$libcaractval = $DB_site->query_first("SELECT libelle FROM caracteristiquevaleur_site WHERE caractvalid = '$ligne_facture_offert_caracteristique[caractvalid]' AND siteid = '1'");
			
					$ligne_facture_caracteristique[libcaract] = $libcaract[libelle];
					$ligne_facture_caracteristique[libcaractval] = $libcaractval[libelle];
				}
				$lignesfactureoffert[caracteristiques] .= "$ligne_facture_offert_caracteristique[libcaract] : $ligne_facture_offert_caracteristique[libcaractval]<br>" ;
			}
			
			$article = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$lignefactureoffert[artid]' AND siteid = '1'");
			$regleurlrewriteDefaut = $regleurlrewrite[1];
			$article[librewrite] = url_rewrite($article[libelle]);
			$article[url] = "http://$host/$regleurlrewriteDefaut[article]-$article[librewrite]-$article[artid].htm";
			
			//Module photo par caracteristique
			$imageParCaractTrouvee = 0;
			if (in_array(5941, $modules) || $mode == "test_modules") {
				$existePhoto = $DB_site->query_first("SELECT * FROM article_caractval_photo WHERE artid = '$lignefactureoffert[artid]' AND caractvalid IN (SELECT caractvalid FROM lignefactureoffertcaracteristique WHERE lignefactureoffertid = '$lignefactureoffert[lignefactureoffertid]') ORDER BY position LIMIT 1");
				if ($existePhoto[articlecaractvalphotoid] && $existePhoto[image]) {
					$lignefactureoffert[image] = "<img src=\"http://$host/supc-$article[librewrite]-$existePhoto[articlecaractvalphotoid].$existePhoto[image]\">" ;
					$imageParCaractTrouvee = 1;
				}
			}
			if (!$imageParCaractTrouvee) {
				if (file_exists($rootpath."configurations/".$host."/images/produits/suppl/".$lignefactureoffert[artid].".jpg"))
					$lignefactureoffert[image] = "<img src=\"http://".$host."/sup-".$article[librewrite]."-".$lignefactureoffert[artid].".jpg\">" ;
				elseif (file_exists($rootpath."configurations/".$host."/images/produits/suppl/".$lignefactureoffert[artid].".gif"))
				$lignefactureoffert[image] = "<img src=\"http://".$host."/sup-".$article[librewrite]."-".$lignefactureoffert[artid].".gif\">" ;
			}
			eval(charge_template($langue, $referencepage, "ModificationDetailProduitsArticleOffertBit"));
		}
	}
	
	//Totaux
	$commande[montantport] = formaterPrix($commande[montantport]);
	//$totaux = calculerTotalFacture($DB_site, $commande[factureid]);
	//$totalTTC = formaterPrix($totaux[totalTTC]);
	$totalTTC = $commande[montanttotal_ttc];
	//$sousTotalTTC = formaterPrix($totaux[sousTotalTTC]);
	$txt_total = strtoupper($multilangue[total]);
	if ($commande[montantRemiseCommerciale] > 0) {
		$commande[montantRemiseCommerciale] = formaterPrix($commande[montantRemiseCommerciale]) ;
		if ($commande[etatid] == 0 || $commande[etatid] == 1) {
			eval(charge_template($langue, $referencepage, "ModificationTotauxNouvelleRemiseComm"));
		}else{
			eval(charge_template($langue, $referencepage, "ModificationTotauxRemiseCommExistante"));
		}
	}else{
		if ($commande[etatid] == 0 || $commande[etatid] == 1) {
			$commande[montantRemiseCommerciale] = 0;
			eval(charge_template($langue, $referencepage, "ModificationTotauxNouvelleRemiseComm"));
		}
	}
	
	if ($commande[moyenid] == '3') {
		$commande[prix_contre_remboursement] = formaterPrix($commande[prix_contre_remboursement]) ;
		eval(charge_template($langue, $referencepage, "ModificationTotauxSuppContreRemboursement"));
	}
	//Module offres promotionnelles
	if ((in_array("5914", $modules) || $mode == "test_modules") && $totaux[montantOperationsTTC]) {
		$commande[montantOperations] = formaterPrix($totaux[montantOperationsTTC]);
		eval(charge_template($langue, $referencepage, "ModificationTotauxModuleOffrePromo"));
	}
	
	//Module fidélité
	if ((in_array("5834", $modules) || $mode == "test_modules") && $commande[montantreductionfidelite]) {
		$commande[montantreductionfidelite] = formaterPrix($commande[montantreductionfidelite]);
		eval(charge_template($langue, $referencepage, "ModificationTotauxModuleFidelite"));
	}
	
	//Module Bon achat
	if ((in_array("5949", $modules) || $mode == "test_modules") && $commande[montantBonAchat] ) {
		$commande[montantbonachat] = formaterPrix($commande[montantBonAchat]) ;
		eval(charge_template($langue, $referencepage, "ModificationTotauxModuleBonAchat"));
	}
	
	//Module Cadeau
	if (in_array("13", $modules) || $mode == "test_modules") {
		if ($commande[montantcadeau] && $commande[cadeauid]) {
			$infoscadeau = $DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid = '$commande[cadeauid]'");
			$commande[montantcadeau] = formaterPrix($commande[montantcadeau]) ;
			eval(charge_template($langue, $referencepage, "ModificationTotauxModuleCadeauCodeExistant"));
		} elseif ($commande[etatid] == 0 || $commande[etatid] == 1) {
			eval(charge_template($langue, $referencepage, "ModificationTotauxModuleCadeauNouveauCode"));
		}
	}
	
	// Module commande cadeau
	if(in_array(5824, $modules) || $mode == "test_modules"){
		if($rs_facture[commandecadeau]==1){
			$texteCadeau = $multilangue[oui];
			$commande[dedicace] = htmlentities(stripslashes($commande[dedicace]));
			eval(charge_template($langue, $referencepage, "ModificationCadeauDedicace"));
		}else{
			$texteCadeau = $multilangue[non];
		}
		eval(charge_template($langue, $referencepage, "ModificationModuleCadeau"));
	}
	
	//Module statut logistique
	if(in_array(5881, $modules) || $mode == "test_modules"){
		$selectedStatut = "";
		if($commande[statutlogistiqueid] == '0'){
			$selectedStatut0 = "selected = 'selected'";
		}else{
			$selectedStatut0 = "";
		}
		$statuts = $DB_site->query("SELECT * FROM statut_logistique");
		while($statut = $DB_site->fetch_array($statuts)){
			if($statut[statutlogistiqueid] == $commande[statutlogistiqueid]){
				$selectedStatut = "selected='selected'";
			}else{
				$selectedStatut = "";
			}
			eval(charge_template($langue, $referencepage, "ModificationStatutLogistiqueBit"));
		}
		eval(charge_template($langue, $referencepage, "ModificationStatutLogistique"));
	}
	
	//Module stocks
	if(in_array(4, $modules) || $mode == "test_modules"){
		$infoDecrementation = "";
		$infoIncrementation = "";
		$boutonActionStocks = "";
		if ($commande[datedecrementation] != "0000-00-00"){
			$infoDecrementation .= "$multilangue[stocks_decrementes_le] ".convertirDateEnChaine($commande[datedecrementation]).".<br>";
		}else{
			$infoDecrementation .= "$multilangue[stocks_non_decrementes]<br>";
		}
		
		if ($commande[dateincrementation] != "0000-00-00"){
			$infoIncrementation .= "$multilangue[stocks_incrementes_le] ".convertirDateEnChaine($commande[dateincrementation]).".<br>";
		}
		if ($commande[etatid] != "6") {
			if ($commande[decrementation] == "0"){
				$boutonActionStocks = $multilangue[decrementer_stocks];
			}else{
				$boutonActionStocks = $multilangue[incrementer_stocks];
			}
			eval(charge_template($langue, $referencepage, "ModificationStocks"));
		}
		
		if($commande[dateincrementationprevue] != '0000-00-00' && $commande[etatid] == 0){
			$valueInputStock = $commande[dateincrementationprevue];
			$inputDateStock = convertirDateEnChaine($valueInputStock);
			if($commande[dateincrementationprevue] <= date("Y-m-d") && $commande[decrementation] == '0'){
				eval(charge_template($langue, $referencepage, "ModificationStocksDateIncrementation"));
			} else {
				eval(charge_template($langue, $referencepage, "ModificationStocksFormIncrementation"));
			}
		}
	}
	
	//Module commentaire
	if(in_array(5892, $modules) || $mode == "test_modules"){
		$commentaires = $DB_site->query("SELECT * FROM facture_commentaires WHERE factureid = '$commande[factureid]' ORDER BY date DESC");
		while($commentaire = $DB_site->fetch_array($commentaires)){
			$dateCommentaire = date("d/m/Y $multilangue[a] H:i:s", $commentaire[date]);
			$commentaire[commentaire] = stripslashes($commentaire[commentaire]);
			eval(charge_template($langue, $referencepage, "ModificationCommentaireBit"));
		}
		eval(charge_template($langue, $referencepage, "ModificationCommentaire"));
	}
	
	//Module code barre
	if (in_array(5865, $modules) || $mode == "test_modules"){
		$imageCodeBarre = genererCodeBarre($factureid, "C128");
		eval(charge_template($langue, $referencepage, "ModificationCodeBarre"));
	}
	
	//Module points fidelite
	if (in_array(5834, $modules) || $mode == "test_modules"){
		if ($utilisateur[club] == "1"){
			$texteFidelite = "$multilangue[client_appartient_club_fidelite] (".$utilisateur[nbpoints]." $multilangue[points_fidelite])";
		}else{
			$texteFidelite = "$multilangue[client_appartient_pas_club_fidelite]";
		}
		eval(charge_template($langue, $referencepage, "ModificationModuleFidelite"));
	}
	
	//Module pro
	if (in_array("122",$modules) || $mode == "test_modules"){
		if($utilisateur[pro] == "1"){
			eval(charge_template($langue, $referencepage, "ModificationModulePro"));
		}
	}
	
	//Module avoirs
	if ((in_array("5921", $modules) || $mode == "test_modules") && !$commande[avoir_parentid]) {
		$avoirs = $DB_site->query("SELECT * FROM facture WHERE avoir_parentid = '$commande[factureid]' AND deleted = '0' AND etatid = '5'");
		if ($DB_site->num_rows($avoirs)) {
			while ($avoir = $DB_site->fetch_array($avoirs)) {
				$avoir[datefacture] = date("d-m-Y", strtotime($avoir[datefacture]));
				$avoir[montanttotal_ttc] = formaterPrix($avoir[montanttotal_ttc]);
				$avoir[moyenid] = retournerLibellePaiement($DB_site, $avoir[moyenid]);
				eval(charge_template($langue, $referencepage, "ModificationListeAvoirsBit"));
			}
			eval(charge_template($langue, $referencepage, "ModificationListeAvoirs"));
		}
	}
	
	//Affichage des spécifications suivant chaque état de commande
	if($commande[etatid] == '0'){
		
		eval(charge_template($langue, $referencepage, "ModificationLigneActionValiderReglement"));
		if($canDeleteFacture){
			eval(charge_template($langue, $referencepage, "ModificationLigneActionSupprFacture"));
		}else{
			eval(charge_template($langue, $referencepage, "ModificationLigneActionImpSupprFacture"));
		}
		eval(charge_template($langue, $referencepage, "ModificationLigneAction"));
		eval(charge_template($langue, $referencepage, "ModificationColonneAction"));
		
		if($etage){
			eval(charge_template($langue, $referencepage, "ModificationCoordFacturationFormEtage"));
		}
		if($escalier){
			eval(charge_template($langue, $referencepage, "ModificationCoordFacturationFormEscalier"));
		}
		if($codeacces){
			eval(charge_template($langue, $referencepage, "ModificationCoordFacturationFormCodeAcces"));
		}
		if($letage){
			eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonFormEtage"));
		}
		if($lescalier){
			eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonFormEscalier"));
		}
		if($lcodeacces){
			eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonFormCodeAcces"));
		}
		
		eval(charge_template($langue, $referencepage, "ModificationImprimeProForma"));
		eval(charge_template($langue, $referencepage, "ModificationCoordFacturationForm"));
		eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonForm"));
		eval(charge_template($langue, $referencepage, "ModificationDetailProduitsAjoutArticle"));
		
	}else{
		
		eval(charge_template($langue, $referencepage, "ModificationImpressionFacture"));
		if (in_array(5824, $modules) || $mode == "test_modules") {
			eval(charge_template($langue, $referencepage, "ModificationImprimeFactureCadeau"));
		}
		eval(charge_template($langue, $referencepage, "ModificationImpression"));
		
		if($commande[etatid] == '1'){
			
			if ($commande[modelivraisonid] != $modelivraison_retrait){
				$namebtnexpedier = $multilangue[expedier_commande];
			}else{
				$namebtnexpedier = $multilangue[restituer_commande];
			}
			
			if((in_array(5888, $modules) || $mode == "test_module") && is_immateriel($DB_site, $factureid)){
				eval(charge_template($langue, $referencepage, "ModificationLigneActionExpedierImmateriel"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationLigneActionExpedierMateriel"));
			}
			
			if($canDeleteFacture){
				eval(charge_template($langue, $referencepage, "ModificationLigneActionRefuser"));
				eval(charge_template($langue, $referencepage, "ModificationLigneActionSupprFacture"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationLigneActionImpRefuserFacture"));
				eval(charge_template($langue, $referencepage, "ModificationLigneActionImpSupprFacture"));
			}
			
			eval(charge_template($langue, $referencepage, "ModificationLigneActionExpedier"));
			eval(charge_template($langue, $referencepage, "ModificationLigneAction"));
			eval(charge_template($langue, $referencepage, "ModificationColonneAction"));
			
			if($etage){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationFormEtage"));
			}
			if($escalier){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationFormEscalier"));
			}
			if($codeacces){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationFormCodeAcces"));
			}
			if($letage){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonFormEtage"));
			}
			if($lescalier){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonFormEscalier"));
			}
			if($lcodeacces){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonFormCodeAcces"));
			}

			eval(charge_template($langue, $referencepage, "ModificationCoordFacturationForm"));
			eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonForm"));
			eval(charge_template($langue, $referencepage, "ModificationDetailProduitsAjoutArticle"));
		}elseif($commande[etatid] == '2' || $commande[etatid] == '3'){
			
			if($canDeleteFacture){
				eval(charge_template($langue, $referencepage, "ModificationLigneActionSupprFacture"));
			}else{
				eval(charge_template($langue, $referencepage, "ModificationLigneActionImpSupprFacture"));
			}
			eval(charge_template($langue, $referencepage, "ModificationLigneAction"));
			eval(charge_template($langue, $referencepage, "ModificationColonneAction"));
			
			if($etage){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationEtage"));
			}
			if($escalier){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationEscalier"));
			}
			if($codeacces){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationCodeAcces"));
			}
			if($letage){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonEtage"));
			}
			if($lescalier){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonEscalier"));
			}
			if($lcodeacces){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonCodeAcces"));
			}
			
			eval(charge_template($langue, $referencepage, "ModificationCoordFacturation"));
			eval(charge_template($langue, $referencepage, "ModificationCoordLivraison"));
				
		}elseif($commande[etatid] == '5'){
			if ((in_array("5983", $modules) || $mode == "test_modules") && $params[lengowid] && $commande[lengow_orderid] && $params[maj_cmd_lengow]){
				$placelengow = $DB_site->query_first("SELECT lengow_marketplace, id_flux_lengow FROM moyenpaiement WHERE moyenid = '$commande[moyenid]'");
				if ($placelengow[id_flux_lengow]){//id flux lengow à récupérer dans le backoffice Lengow du client
					$numero_suivi = $DB_site->query_first("SELECT numero_suivi FROM colis WHERE factureid = '$commande[factureid]'");
					$numero_suivi = $numero_suivi[numero_suivi];
					switch ($placelengow[lengow_marketplace]) {
						case "Rueducommerce":
							$url_lengow = "https://wsdl.lengow.com/wsdl/rdc/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/acceptOrder.xml?TrackingColis=$numero_suivi";
							$url_lengowBis = "https://wsdl.lengow.com/wsdl/rdc/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/shippedOrder.xml?TrackingColis=$numero_suivi";
							break;
						case "Fnac.com":
							$url_lengow = "https://wsdl.lengow.com/wsdl/fnac/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/acceptOrder.xml?trackingColis=$numero_suivi";
							$url_lengowBis = "https://wsdl.lengow.com/wsdl/fnac/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/Shipped.xml?trackingColis=$numero_suivi";
							break;
						case "PriceMinister":
							$url_lengowBis = "https://wsdl.lengow.com/wsdl/priceminister/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/acceptOrder.xml";
							break;
						case "Amazon":
							$url_lengow = "https://wsdl.lengow.com/wsdl/amazon/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/accept.xml?colis_idTracking=$numero_suivi";
							$url_lengowBis = "https://wsdl.lengow.com/wsdl/amazon/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/acceptOrder.xml?colis_idTracking=$numero_suivi";
							break;
						case "eBay":
							$url_lengow = "https://wsdl.lengow.com/wsdl/ebay/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/shippedOrder.xml?TrackingColis=$numero_suivi";
							break;
						case "Cdiscount":
							$url_lengow = "https://wsdl.lengow.com/wsdl/cdiscount/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/acceptOrder.xml?TrackingColis=$numero_suivi";
							$url_lengowBis = "https://wsdl.lengow.com/wsdl/cdiscount/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/shippedOrder.xml?TrackingColis=$numero_suivi";
							break;
						case "LaRedoute":
							$url_lengow = "https://wsdl.lengow.com/wsdl/laredoute/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/acceptOrder.xml?TrackingColis=$numero_suivi";
							$url_lengowBis = "https://wsdl.lengow.com/wsdl/laredoute/".$placelengow[id_flux_lengow]."/".$commande[lengow_orderid]."/shippedOrder.xml?TrackingColis=$numero_suivi";
							break;
						default:
							$url_lengow = "";
							$url_lengowBis = "";
							break;
					}
		
					if ($url_lengowBis){
						eval(charge_template($langue, $referencepage, "ModificationLigneActionCreerAvoir"));
					}
				}
			
			}
			if (!in_array("5921", $modules) || (in_array("5921", $modules) && !$commande[avoir_parentid]) ) {
				if((in_array("5921", $modules) || $mode == "test_modules") && $commande[avoir_parentid] == "0"){
					eval(charge_template($langue, $referencepage, "ModificationLigneActionCreerAvoir"));
				}
				
				eval(charge_template($langue, $referencepage, "ModificationLigneActionEtatExpediee"));
			}
			
			if ($commande[modelivraisonid] != $modelivraison_retrait){
				$txtexp = $multilangue[expedition];
			}else{
				$txtexp = $multilangue[retrait];
			}
			
			$coliss = $DB_site->query("SELECT * FROM colis INNER JOIN facture USING (factureid) WHERE factureid = '$commande[factureid]' ORDER BY colisid");
			$i_colis = 0;
			while ($colis = $DB_site->fetch_array($coliss)){
				$TemplateCommandesModificationLigneExpeditionDetailColisBit = "";
				$TemplateCommandesModificationLigneExpeditionColisReexpedie = "";
				$TemplateCommandesModificationLigneExpeditionColisSuivi = "";
				$colis[dateexpedition] = date("d-m-Y", strtotime($colis[dateexpedition]));
				$produits = $DB_site->query_first("SELECT SUM(qte) AS total FROM colis_lignefacture WHERE colisid = '$colis[colisid]'");
				$i_colis++;
				if ($colis[transporteur] && $colis[numero_suivi] && $colis[adresse_suivi]) {
					eval(charge_template($langue, $referencepage, "ModificationLigneExpeditionColisSuivi"));
				}
				
				if ($colis[reexpedition]) {
					eval(charge_template($langue, $referencepage, "ModificationLigneExpeditionColisReexpedie"));
				}	
					
				$lignescolis = $DB_site->query("SELECT clf.qte AS qtel, lf.* FROM colis_lignefacture clf INNER JOIN lignefacture lf USING (lignefactureid) WHERE clf.colisid = '$colis[colisid]'");
				while ($lignecolis = $DB_site->fetch_array($lignescolis)) {
					$lignecolis[caracteristiques] = "" ;

					$lignesfacturecaracteristique = $DB_site->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid = '$lignecolis[lignefactureid]'");
					while ($lignefacturecaracteristique = $DB_site->fetch_array($lignesfacturecaracteristique)) {
						if($lignefacturecaracteristique[libcaract] != "" || $lignefacturecaracteristique[libcaractval] != ""){
							$lignecolis[caracteristiques] .= "$lignefacturecaracteristique[libcaract] : $lignefacturecaracteristique[libcaractval]," ;
						}
					}
					if ($lignecolis[caracteristiques]) {
						$lignecolis[caracteristiques] = " (" . substr($lignecolis[caracteristiques], 0, -1) . ")" ;
					}
					eval(charge_template($langue, $referencepage, "ModificationLigneExpeditionDetailColisBit"));
				}
				eval(charge_template($langue, $referencepage, "ModificationLigneExpeditionColisBit"));
			}
			
			eval(charge_template($langue, $referencepage, "ModificationLigneExpedition"));
			eval(charge_template($langue, $referencepage, "ModificationColonneExpedition"));
			eval(charge_template($langue, $referencepage, "ModificationLigneAction"));
			eval(charge_template($langue, $referencepage, "ModificationColonneAction"));
			
			if($etage){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationEtage"));
			}
			if($escalier){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationEscalier"));
			}
			if($codeacces){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationCodeAcces"));
			}
			if($letage){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonEtage"));
			}
			if($lescalier){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonEscalier"));
			}
			if($lcodeacces){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonCodeAcces"));
			}
			
			eval(charge_template($langue, $referencepage, "ModificationCoordFacturation"));
			eval(charge_template($langue, $referencepage, "ModificationCoordLivraison"));
			
		}else{
			
			if($etage){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationEtage"));
			}
			if($escalier){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationEscalier"));
			}
			if($codeacces){
				eval(charge_template($langue, $referencepage, "ModificationCoordFacturationCodeAcces"));
			}
			if($letage){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonEtage"));
			}
			if($lescalier){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonEscalier"));
			}
			if($lcodeacces){
				eval(charge_template($langue, $referencepage, "ModificationCoordLivraisonCodeAcces"));
			}
			
			eval(charge_template($langue, $referencepage, "ModificationCoordFacturation"));
			eval(charge_template($langue, $referencepage, "ModificationCoordLivraison"));
		}
	}
	eval(charge_template($langue, $referencepage, "Modification"));
}

if(!isset($action) || $action == ""){
	if(isset($erreur) && $erreur == '1'){
		$texteErreur = $multilangue[erreur_action_multiple_vide];
		eval(charge_template($langue, $referencepage, "Erreur"));
	}
	if(isset($erreur) && $erreur == '2'){
		$texteErreur = $multilangue[erreur_selection_commande];
		eval(charge_template($langue, $referencepage, "Erreur"));
	}
	$sites = $DB_site->query("SELECT * FROM site WHERE siteid IN (SELECT siteid FROM facture)");
	while($site = $DB_site->fetch_array($sites)){
		eval(charge_template($langue, $referencepage, "SiteBit"));
	}
	
	$moyens_paiement = $DB_site->query("SELECT * FROM moyenpaiement_site WHERE siteid='1' AND moyenid IN (SELECT moyenid FROM facture)");
	while($moyen_paiement = $DB_site->fetch_array($moyens_paiement)){
		eval(charge_template($langue, $referencepage, "ReglementBit"));
	}
	
	$etats = $DB_site->query("SELECT * FROM etatfacture_langue WHERE siteid='1' AND etatid IN (SELECT etatid FROM facture)");
	while($etat = $DB_site->fetch_array($etats)){
		eval(charge_template($langue, $referencepage, "EtatBit"));
	}
	
	$modes_livraison = $DB_site->query("SELECT * FROM  mode_livraison_site WHERE siteid='1' AND modelivraisonid IN (SELECT modelivraisonid FROM facture)");
	while($mode_livraison = $DB_site->fetch_array($modes_livraison)){
		eval(charge_template($langue, $referencepage, "LivraisonBit"));
	}
	
	$lengow = $DB_site->query_first("SELECT factureid FROM facture WHERE lengow_orderid != ''");
	if($lengow[factureid]){
		eval(charge_template($langue, $referencepage, "ColonneLengow"));
	}
	
	$shopping = $DB_site->query_first("SELECT factureid FROM facture WHERE shoppingflux_orderid != ''");
	if($shopping[factureid]){
		eval(charge_template($langue, $referencepage, "ColonneShopping"));
	}
	
	if (in_array(5979, $modules) || $mode == "test_modules") {
		$groupes = $DB_site->query("SELECT * FROM groupe_commande");
		if($DB_site->num_rows($groupes) > 0){
			while($groupe = $DB_site->fetch_array($groupes)){
				eval(charge_template($langue, $referencepage, "GroupeRechercheBoutonBit"));
			}
			eval(charge_template($langue, $referencepage, "GroupeRecherche"));
		}
		eval(charge_template($langue, $referencepage, "BoutonEnregistrer"));
		
	}
	
	if (in_array(5945, $modules) || $mode == "test_modules") {
		$ligneNotice = '{"sClass": "ta-center", "bSortable": false, "mData": "not", "iDataSort": 21, "data": "not", "bVisible": true}';
		eval(charge_template($langue, $referencepage, "ColonneNotice"));
		eval(charge_template($langue, $referencepage, "ColonneNoticeVide"));
	}
	
	if(in_array(5881, $modules) || $mode == "test_modules"){
		$statuts = $DB_site->query("SELECT * FROM statut_logistique");
		while($statut = $DB_site->fetch_array($statuts)){
			eval(charge_template($langue, $referencepage, "StatutBit"));
		}
		$ligneStatut = '{"sClass": "ta-center", "bSortable": false, "mData": "statutlogistique", "iDataSort": 15, "data": "statutlogistique", "bVisible": false},';
		eval(charge_template($langue, $referencepage, "ColonneStatut"));
		eval(charge_template($langue, $referencepage, "ColonneStatutLogistique"));
		eval(charge_template($langue, $referencepage, "ColonneStatutLogistiqueVide"));
	}
	eval(charge_template($langue, $referencepage, "Navig"));
	eval(charge_template($langue, $referencepage, "Liste"));
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