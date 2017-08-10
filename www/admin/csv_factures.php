<?php
/**
* Export personnalisé par sélection des factures/lignesfacture
* @Benjamin & Adeline
* @Enjoy
*/
	
require "includes/admin_global.php";

//construction des titres des colonnes
$contenu="";

if ($exporterTitre == 1){
	
	//Numéro de commande (=factureid)
	if ($factureFactureid == 'on') $contenu.="$multilangue[numero_commande];";
	
	//Numéro de facture (=numerofacture)
	if ($factureNumerofacture == 'on') $contenu.="$multilangue[numero_facture];";
	
	//Identifiant utilisateur (userid)
	if ($factureUserid == 'on') $contenu.="$multilangue[identifiant_utilisateur];";
	
	//Date de la commande
	if ($factureDatefacture == 'on') $contenu.="$multilangue[date_de_commande];";
	
	//Date de la validation
	if ($factureDatevalidation == 'on') $contenu.="$multilangue[date_validation];";
	
	//Date de livraison prévue
	if ($factureDatelivraisonprevue == 'on') $contenu.="$multilangue[date_livraison_prevue];"; 
	
	//Date de l'expedition
	if ($factureDateexpedition == 'on') $contenu.="$multilangue[date_expedition];";
	
	//Moyen de paiement
	if ($factureMoyenPaiement == 'on') $contenu.="$multilangue[moyen_de_reglement];";
	
	//Etat de la commande
	if ($factureEtat == 'on') $contenu.="$multilangue[etat_commande];";
	
	//Mode de livraison
	if ($facturemodeLivraison == 'on') $contenu.="$multilangue[mode_de_livraison];";
	
	//Numéro de suivi
	if ($factureNumero_suivi == 'on') $contenu.="$multilangue[numero_suivi];";
	
	//Adresse de suivi
	if ($factureAdresse_suivi == 'on') $contenu.="$multilangue[adresse_suivi];";
	
	//Ip de l'internaute
	if ($factureIp == 'on') $contenu.="$multilangue[ip_internaute];";
	
	//Mail de l'internaute
	if ($factureMail == 'on') $contenu.="$multilangue[email_internaute];";
	
	//Langue de la commande
	if ($factureLangue == 'on') $contenu.="$multilangue[langue];";
	
	//Commentaire
	if ($factureCommentaire == 'on') $contenu.="$multilangue[commentaire];";	
	
	//Civilité facturation
	if ($factureCivilite == 'on') $contenu.="$multilangue[civilite] ($multilangue[facturation]);";
	
	//Nom facturation
	if ($factureNom == 'on') $contenu.="$multilangue[nom] ($multilangue[facturation]);";
	
	//Prénom facturation
	if ($facturePrenom == 'on') $contenu.="$multilangue[prenom] ($multilangue[facturation]);";
	
	//Age du client
	if ($factureAge == 'on') $contenu.="$multilangue[age_client];";
	
	//Date de naissance du client
	if ($factureDatenaissance == 'on') $contenu.="$multilangue[date_naissance];";
	
	//Raison sociale facturation
	if ($factureRaison_sociale == 'on') $contenu.="$multilangue[raison_sociale] ($multilangue[facturation]);";
	
	//Adresse facturation
	if ($factureAdresse == 'on') $contenu.="$multilangue[adresse] ($multilangue[facturation]);";
	
	//Adresse2 facturation
	if ($factureAdresse2 == 'on') $contenu.="$multilangue[adresse2] ($multilangue[facturation];";
	
	//Code postal facturation
	if ($factureCodepostal == 'on') $contenu.="$multilangue[code_postal] ($multilangue[facturation]);";
	
	//Ville facturation
	if ($factureVille == 'on') $contenu.="$multilangue[ville] ($multilangue[facturation]);";
	
	//Pays facturation
	if ($facturePays == 'on') $contenu.="$multilangue[pays] ($multilangue[facturation]);";
	
	//Telephone facturation
	if ($factureTelephone == 'on') $contenu.="$multilangue[telephone] ($multilangue[facturation]);";
	
	//Telephone 2 facturation
	if ($factureTelephone2 == 'on') $contenu.="$multilangue[telephone2] ($multilangue[facturation]);";
	
	//N°TVA intracommunautaire facturation
	if ($factureNumerotva == 'on') $contenu.="$multilangue[tva_intracommunautaire] ($multilangue[facturation]);";
	
	//Siret facturation
	if ($factureSiret == 'on') $contenu.="$multilangue[siret] ($multilangue[facturation]);";
	
	//Civilité livraison
	if ($factureLcivilite == 'on') $contenu.="$multilangue[civilite] ($multilangue[livraison]);";
	
	//Nom livraison
	if ($factureLnom == 'on') $contenu.="$multilangue[nom] ($multilangue[livraison]);";
	
	//Prénom livraison
	if ($factureLprenom == 'on') $contenu.="$multilangue[prenom] ($multilangue[livraison]);";
	
	//Raison sociale livraison
	if ($factureLraison_sociale == 'on') $contenu.="$multilangue[raison_sociale] ($multilangue[livraison]);";
	
	//Adresse livraison
	if ($factureLadresse == 'on') $contenu.="$multilangue[adresse] ($multilangue[livraison]);";
	
	//Adresse 2 livraison
	if ($factureLadresse2 == 'on') $contenu.="$multilangue[adresse2] ($multilangue[livraison]);";
		
	//Code postal livraison
	if ($factureLcodepostal == 'on') $contenu.="$multilangue[code_postal] ($multilangue[livraison]);";
	
	//Ville livraison
	if ($factureLville == 'on') $contenu.="$multilangue[ville] ($multilangue[livraison]);";
	
	//Pays livraison
	if ($factureLpays == 'on') $contenu.="$multilangue[pays] ($multilangue[livraison]);";	
	
	//Telephone livraison
	if ($factureLtelephone == 'on') $contenu.="$multilangue[telephone] ($multilangue[livraison]);";

	//TCommentaire livraison
	if ($factureLcommentaire == 'on') $contenu.="$multilangue[commentaire] ($multilangue[livraison]);";
	
	//Montant total ttc
	if ($factureMontanttotal_ttc == 'on') $contenu.="$multilangue[montant_total] $multilangue[ttc];";
	
	//Montant total ht
	if ($factureMontanttotal_ht == 'on') $contenu.="$multilangue[montant_total] $multilangue[ht];";
	
	//Montant total hors frais de port TTC
	if ($factureMontanttotal_horsfraisport_ttc == 'on') $contenu.="$multilangue[montant_total] $multilangue[hors_frais_port] $multilangue[ttc];";
	
	//Montant total hors frais de port HT
	if ($factureMontanttotal_horsfraisport_ht == 'on') $contenu.="$multilangue[montant_total] $multilangue[hors_frais_port] $multilangue[ht];";
	
	//Montant port TTC
	if ($factureMontantport_ttc == 'on') $contenu.="$multilangue[montant_frais_port]  $multilangue[ttc];";
	
	//Montant port HT
	if ($factureMontantport_ht == 'on') $contenu.="$multilangue[montant_frais_port]  $multilangue[ht];";
	
	//TVA port
	if ($factureTvaport == 'on') $contenu.="$multilangue[tva_frais_port];";
	
	//Code bon de réduction utilisé
	if ($factureCodecadeau == 'on') $contenu.="$multilangue[bon_reduction_utilise];";

	//Montant bon de réduction utilisé
	if ($factureMontantcadeau == 'on') $contenu.="$multilangue[montant_bon_reduction];";
	
	//Prix contre remboursement
	if ($facturePrixcontreremboursement == 'on') $contenu.="$multilangue[prix_contre_remboursement];";	
	
	//Commande cadeau
	if ($factureCommandecadeau == 'on') $contenu.="$multilangue[commande_cadeau];";	
	
	//Dédicace
	if ($factureDedicace == 'on') $contenu.="$multilangue[dedicace];";	
	
	//Montant réduction fidélité
	if ($factureMontantreductionfidelite == 'on') $contenu.="$multilangue[montant_reduction_fidelite];";	
	
	//Points utilisés
	if ($facturePointsutilises == 'on') $contenu.="$multilangue[points_utilises];";	
	
	//Commande pro
	if ($facturePro == 'on') $contenu.="$multilangue[commande_pro];";
	
	//Montant TVA marchandises
	if ($factureMontanttvamarchandises == 'on') $contenu.="$multilangue[montant_tva_marchandises];";
	
	//Montant TVA port
	if ($factureMontanttvaport == 'on') $contenu.="$multilangue[montant_tva_port];";
	
	//Montant TVA
	if ($factureMontantTva == 'on') $contenu.="$multilangue[montant_tva];";
	
	// Lignes facture
	if ($exporterLigneFacture == 1){
		//Identifiant de la ligne de facture (lignefactureid)
		if ($lignefactureLignefactureid == 'on') $contenu.="$multilangue[identifiant_ligne_facture];";
		
		//Référence de l'article (artcode)
		if ($lignefactureArtcode == 'on') $contenu.="$multilangue[reference] ($multilangue[article]);";
		
		//Quantité
		if ($lignefactureQte == 'on') $contenu.="$multilangue[quantite] ($multilangue[article]);";
		
		//Libellé de l'article commandé
		if ($lignefactureLibelle == 'on') $contenu.="$multilangue[libelle] ($multilangue[article]);";
		
		//Caractéristiques de l'article commandé
		if ($lignefactureCaracteristiques == 'on') $contenu.="$multilangue[caracteristiques] ($multilangue[article]);";
		
		//Prix TTC de l'article commandé
		if ($lignefacturePrix == 'on') $contenu.="$multilangue[prix] $multilangue[ttc] ($multilangue[article]);";
		
		//Prix HT de l'article commandé
		if ($lignefacturePrixht == 'on') $contenu.="$multilangue[prix] $multilangue[ht] ($multilangue[article]);";
		
		//Personnalisation
		if ($lignefacturePersonnalisation == 'on') $contenu.="$multilangue[personnalisation];";

		//Prix de la personnalisation
		if ($lignefactureLf_prixperso == 'on') $contenu.="$multilangue[prix_perso];";
		
		//Ecotaxe
		if ($lignefactureEcotaxe == 'on') $contenu.="$multilangue[ecotaxe] ($multilangue[article]);";

		//Prix d'achat HT
		if ($lignefacturePrixachat == 'on') $contenu.="$multilangue[prix_achat] $multilangue[ht] ($multilangue[article]);";

		//Prix brut HT
		if ($lignefacturePrixbrut == 'on') $contenu.="$multilangue[prix_brut] $multilangue[ttc] ($multilangue[article]);";
		
		//Taux de TVA
		if ($lignefactureTauxtva == 'on') $contenu.="$multilangue[taux_tva] ($multilangue[article]);";

		//Poids
		if ($lignefacturePoids == 'on') $contenu.="$multilangue[poids] ($multilangue[article]);";
		
		//Délai de livraison
		if ($lignefactureDelai == 'on') $contenu.="$multilangue[delai_livraison] ($multilangue[article]);";
		
		//Fournisseur
		if ($lignefactureFournisseur == 'on') $contenu.="$multilangue[fournisseur] ($multilangue[article]);";
	}
	
	$contenu.="\n";
}

$filename = './csv/factures.csv';
if (is_writable($filename)) {
	if (!$handle = fopen($filename, 'w')) {
		echo "$multilangue[erreur_ouverture_fichier] ($filename)";
		exit;
	}
	if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) {
		echo "$multilangue[erreur_ecriture_fichier] ($filename)";
		exit;
	}
}else {
	echo "$multilangue[erreur_accessibilite_ecriture_fichier] ($filename).";
	exit();
}

$handle = fopen($filename, 'a');

//requete en fonction des critères sélectionnés
$where="";
$or="";
$innerJoin="";

//Dates facture
if ($critereDateDebutFacture != "" && $critereDateFinFacture != ""){
	$where.=" AND datefacture >= '".convertirChaineEnDate($critereDateDebutFacture)."' AND datefacture < '".convertirChaineEnDate($critereDateFinFacture)."'";
}

//Pays de livraison
if ($critereLpaysid != ""){
	$where.=" AND lpaysid='$critereLpaysid'";
}

//Pays de facturation
if ($criterePaysid != ""){
	$where.=" AND paysid='$criterePaysid'";
}

//Critere selon etatid
if (is_array($critereEtatid)){
	$tabEtatFacture=array();
	foreach ($critereEtatid as $key => $value){
		array_push($tabEtatFacture,$value);
	}
	$where.=" AND f.etatid IN (".implode(',',$tabEtatFacture).")";
}

//Bon de réduction
if ($critereCadeau != ""){
	$where.=" AND cadeauid>0 AND montantcadeau>0";
}

//Commande cadeau
if ($critereCommandecadeau != ""){
	$where.=" AND commandecadeau='1'";
}

//Points utlisés
if ($criterePointsutilises != ""){
	$where.=" AND pointsutilises>0";
}

//Commandes supprimées
if ($critereSuppression != ""){
	if ($critereSuppression == 0){
		$where.=" AND deleted='0'";
	}elseif ($critereSuppression == 1){
		$where.=" AND deleted='1'";
	}
}


$sql = "SELECT DISTINCT(f.factureid),f.* FROM facture f $innerJoin WHERE 1=1 $where ORDER BY factureid";

$factures=$DB_site->query($sql);

while($facture=$DB_site->fetch_array($factures)) {
	
	$contenu="";
	
	//Numéro de commande (=factureid)
	if ($factureFactureid == 'on'){
		$contenu.=$facture[factureid].";";
	}
	
	//Numéro de facture (=numerofacture)
	if ($factureNumerofacture == 'on'){
		if ($facture[numerofacture]){
			$contenu.=$params[codefacture].$facture[numerofacture].";";
		}else{
			$contenu.=";";
		}
	}
	
	//Identifiant utilisateur (userid)
	if ($factureUserid == 'on'){
		$contenu.=$facture[userid].";";
	}
	
	//Date de la commande
	if ($factureDatefacture == 'on') {
		$contenu.=convertirDateEnChaine($facture[datefacture]).";";
	}
	
	//Date de la validation
	if ($factureDatevalidation == 'on'){
		if ($facture[datevalidation] != '0000-00-00'){
			$contenu.=convertirDateEnChaine($facture[datevalidation]).";";
		}else{
			$contenu.=";";
		}	
	}

	//Date de livraison prévue
	if ($factureDatelivraisonprevue == 'on'){
		if ($facture[datelivraisonprevue] != '0000-00-00'){
			$contenu.=convertirDateEnChaine($facture[datelivraisonprevue]).";";
		}else{
			$contenu.=";";
		}	
	}
	
	//Date de l'expedition
	if ($factureDateexpedition == 'on'){
		if ($facture[dateexpedition] != '0000-00-00'){
			$contenu.=convertirDateEnChaine($facture[dateexpedition]).";";
		}else{
			$contenu.=";";
		}
	}
	
	//Moyen de paiement
	if ($factureMoyenPaiement == 'on'){
		$contenu.=secureChaineExport(retournerLibellePaiement($DB_site, $facture[moyenid])).";";
	}
	
	//Etat de la commande
	if ($factureEtat == 'on'){
		$contenu.=secureChaineExport(retournerLibelleEtatFacture($DB_site, $facture[etatid])).";";
	}
	
	//Mode de livraison
	if ($facturemodeLivraison == 'on'){
		$contenu.=secureChaineExport(retournerLibelleLivraison($DB_site, $facture)).";";
	}
	
	$facture[numero_suivi] = "";
	$facture[adresse_suivi] = "";
	$coliss = $DB_site->query("SELECT * FROM colis WHERE factureid = '$facture[factureid]' ORDER BY colisid");
	while($colis = $DB_site->fetch_array($coliss)) {
		$facture[numero_suivi] .= "$colis[numero_suivi]|";		
		$facture[adresse_suivi] .= "$colis[adresse_suivi]|";		
	}
	$facture[numero_suivi] = substr($facture[numero_suivi], 0, -1);
	$facture[adresse_suivi] = substr($facture[adresse_suivi], 0, -1);
	//Numéro de suivi
	if ($factureNumero_suivi == 'on'){
		$contenu.=$facture[numero_suivi].";";
	}
	
	//Adresse de suivi
	if ($factureAdresse_suivi == 'on'){
		$contenu.=$facture[adresse_suivi].";";
	}

	//Ip de l'internaute
	if ($factureIp == 'on'){
		$contenu.=chainetoip($facture[ip]).";";
	}
	
	//Mail de l'internaute
	if ($factureMail == 'on'){
		$contenu.=$facture[mail].";";
	}
	
	//Langue de la commande
	if ($factureLangue == 'on'){
		$contenu.=$facture[langue].";";
	}
	
	//Commentaire
	if ($factureCommentaire == 'on'){
		$contenu.=secureChaineExport($facture[commentaire]).";";
	}
	
	//Civilité facturation
	if ($factureCivilite == 'on'){
		$contenu.=retournerCivilite($facture[civilite]).";";
	}
	
	//Nom facturation
	if ($factureNom == 'on'){
		$contenu.=secureChaineExport($facture[nom]).";";	
	}
	
	//Prénom facturation
	if ($facturePrenom == 'on'){
		$contenu.=secureChaineExport($facture[prenom]).";";	
	}
	
	//Age et date de naissance
	if ($factureAge == 'on' || $factureDatenaissance == 'on'){
		$utilisateur=$DB_site->query_first("SELECT datenaissance FROM utilisateur WHERE userid='$facture[userid]'");
		if ($factureAge == 'on'){
			//calcul de l'age qu'avait l'internaute au moment de la commande
			if ($utilisateur[datenaissance]!="0000-00-00"){
				$age = $facture[datefacture] - $utilisateur[datenaissance];
			}else{
				$age=$multilangue[non_renseigne];
			}
			$contenu.=$age.";";
		}
		if ($factureDatenaissance == 'on'){
			if ($utilisateur[datenaissance]!="0000-00-00"){
				$dateNaissance = $utilisateur[datenaissance];
			}else{
				$dateNaissance=$multilangue[non_renseigne];
			}
			$contenu.=$dateNaissance.";";
		}
	}
	
	//Raison sociale facturation
	if ($factureRaison_sociale == 'on'){
		$contenu.=secureChaineExport($facture[raison_sociale]).";";	
	}
	
	
	//Adresse facturation
	if ($factureAdresse == 'on'){
		$contenu.=secureChaineExport($facture[adresse]).";";	
	}
	
	//Adresse2 facturation
	if ($factureAdresse2 == 'on'){
		$contenu.=secureChaineExport($facture[adresse2]).";";	
	}
	
	//Code postal facturation
	if ($factureCodepostal == 'on'){
		$contenu.=secureChaineExport($facture[codepostal]).";";	
	}
	
	//Ville facturation
	if ($factureVille == 'on'){
		$contenu.=secureChaineExport($facture[ville]).";";	
	}
	
	//Pays facturation
	if ($facturePays == 'on'){
		$contenu.=secureChaineExport(retournerLibellePays($DB_site,$facture[paysid])).";";
	}
	
	//Telephone facturation
	if ($factureTelephone == 'on'){
		$contenu.=secureChaineExport($facture[telephone]).";";	
	}
	
	//Telephone 2 facturation
	if ($factureTelephone2 == 'on') {
		$contenu.=secureChaineExport($facture[telephone2]).";";	
	}

	//N°TVA intracommunautaire facturation
	if ($factureNumerotva == 'on') {
		$contenu.=secureChaineExport($facture[tva]).";";	
	}

	//Siret 2 facturation
	if ($factureSiret == 'on') {
		$contenu.=secureChaineExport($facture[siret]).";";	
	}
	
	//Civilité livraison
	if ($factureLcivilite == 'on'){
		$contenu.=retournerCivilite($facture[lcivilite]).";";
	}
	
	//Nom livraison
	if ($factureLnom == 'on'){
		$contenu.=secureChaineExport($facture[lnom]).";";	
	}
	
	//Prénom livraison
	if ($factureLprenom == 'on'){
		$contenu.=secureChaineExport($facture[lprenom]).";";	
	}
	
	//Raison sociale livraison
	if ($factureLraison_sociale == 'on'){
		$contenu.=secureChaineExport($facture[lraison_sociale]).";";	
	}
	
	//Adresse livraison
	if ($factureLadresse == 'on'){
		$contenu.=secureChaineExport($facture[ladresse]).";";	
	}
	
	//Adresse 2 livraison
	if ($factureLadresse2 == 'on'){
		$contenu.=secureChaineExport($facture[ladresse2]).";";	
	}
	
	//Code postal livraison
	if ($factureLcodepostal == 'on'){
		$contenu.=secureChaineExport($facture[codepostal]).";";	
	}
	
	//Ville livraison
	if ($factureLville == 'on'){
		$contenu.=secureChaineExport($facture[ville]).";";	
	}
	
	//Pays livraison
	if ($factureLpays == 'on'){
		$contenu.=secureChaineExport(retournerLibellePays($DB_site,$facture[paysid])).";";
	}
	
	//Telephone livraison
	if ($factureLtelephone == 'on'){
		$contenu.=secureChaineExport($facture[ltelephone]).";";
	}

	//Commentaire livraison
	if ($factureLcommentaire == 'on'){
		$contenu.=secureChaineExport($facture[lcommentaire]).";";
	}
		
	//Montant total ttc
	if ($factureMontanttotal_ttc == 'on'){
		$contenu.=formaterPrix($facture[montanttotal_ttc],2,$seperateurDecimal)." ;";
	}
	
	//Montant total ht
	if ($factureMontanttotal_ht == 'on'){
		$contenu.=formaterPrix($facture[montanttotal_ht],2,$seperateurDecimal)." ;";
	}
	
	//Montant total hors frais de port TTC
	if ($factureMontanttotal_horsfraisport_ttc == 'on'){
		$contenu.=formaterPrix($facture[montanttotal_horsfraisport_ttc],2,$seperateurDecimal)." ;";
	}
	
	//Montant total hors frais de port HT
	if ($factureMontanttotal_horsfraisport_ht == 'on'){
		$contenu.=formaterPrix($facture[montanttotal_horsfraisport_ht],2,$seperateurDecimal)." ;";
	}
	
	//Montant port ttc
	if ($factureMontantport_ttc == 'on'){
		$contenu.=formaterPrix($facture[montantport],2,$seperateurDecimal)." ;";
	}
	
	//Montant port ht
	if ($factureMontantport_ht == 'on'){
		$montantPortHt = calculerLignePrixHT($facture[montantport],$facture[tvaport]);
		$contenu.=formaterPrix($montantPortHt,2,$seperateurDecimal)." ;";
	}
	
	//TVA port
	if ($factureTvaport == 'on'){
		$contenu.=formaterPrix($facture[tvaport],2,$seperateurDecimal)."%;";
	}
	
	//Code bon de réduction utilisé
	if ($factureCodecadeau == 'on'){
		if ($facture[cadeauid]){
			$cadeau=$DB_site->query_first("SELECT code FROM cadeau WHERE cadeauid = '$facture[cadeauid]'");
			$contenu.=secureChaineExport($cadeau[code]).";";
		}else{
			$contenu.=";";
		}
	}
	
	//Montant bon de réduction utilisé
	if ($factureMontantcadeau == 'on'){
		$contenu.=formaterPrix($facture[montantcadeau],2,$seperateurDecimal)." ;";
	}	
	
	//Prix contre remboursement
	if ($facturePrixcontreremboursement == 'on'){
		$contenu.=formaterPrix($facture[prix_contre_remboursement],2,$seperateurDecimal)." ;";
	}	
	
	//Commande cadeau
	if ($factureCommandecadeau == 'on'){
		if ($facture[commandecadeau]){
			$contenu.="Oui;";
		}else{
			$contenu.="Non;";
		}
	}	
	
	//Dédicace
	if ($factureDedicace == 'on'){
		$contenu.=secureChaineExport($facture[dedicace]).";";
	}	
	
	//Montant réduction fidélité
	if ($factureMontantreductionfidelite == 'on'){
		$contenu.=formaterPrix($facture[montantreductionfidelite],2,$seperateurDecimal)." ;";
	}		
	
	//Points utilisés
	if ($facturePointsutilises == 'on'){
		$contenu.=$facture[pointsutilises].";";
	}		
	
	//Commande pro
	if ($facturePro == 'on'){
		if ($facture[pro]){
			$contenu.="Oui;";
		}else{
			$contenu.="Non;";
		}
	}
	
	
	//Montant TVA marchandises
	if ($factureMontanttvamarchandises == 'on') {
		$contenu.=formaterPrix($facture[montanttotal_horsfraisport_ttc] - $facture[montanttotal_horsfraisport_ht],2,$seperateurDecimal)." ;";
	}
	
	//Montant TVA port
	if ($factureMontanttvaport == 'on') {
		$contenu.=formaterPrix($facture[montantport] - calculerLignePrixHT($facture[montantport], $facture[tvaport]),2,$seperateurDecimal)." ;";
	}
	
	//Montant TVA
	if ($factureMontantTva == 'on') {
		$factureMontanttva = $facture[montanttotal_ttc] - $facture[montanttotal_ht];
		$contenu.=formaterPrix($factureMontanttva,2,$seperateurDecimal)." ;";
	}
	
	// Lignes facture
	if ($exporterLigneFacture == 1){
	
		$contenuFacture = $contenu;
		
		$lignesfacture = $DB_site->query("SELECT * FROM lignefacture WHERE factureid = '$facture[factureid]'");
		while($lignefacture=$DB_site->fetch_array($lignesfacture)) {
			$contenu = $contenuFacture;
			
			//Identifiant de la ligne de facture (lignefactureid)
			if ($lignefactureLignefactureid == 'on'){
				$contenu.=$lignefacture[lignefactureid].";";
			}		
			
			//Référence de l'article (artcode)
			if ($lignefactureArtcode == 'on'){
				$contenu.=secureChaineExport($lignefacture[artcode]).";";
			}	
			
			//Quantité
			if ($lignefactureQte == 'on'){
				$contenu.=$lignefacture[qte].";";
			}		
			
			//Libellé de l'article commandé
			if ($lignefactureLibelle == 'on'){
				$contenu.=secureChaineExport($lignefacture[libelle]).";";
			}	
			
			//Caractéristiques de l'article commandé
			if ($lignefactureCaracteristiques == 'on'){
				$lignesfacturecaracteristique = $DB_site->query("SELECT * FROM lignefacturecaracteristique WHERE lignefactureid = '$lignefacture[lignefactureid]'");
				while($lignefacturecaracteristique=$DB_site->fetch_array($lignesfacturecaracteristique)) {
					$contenu.=secureChaineExport($lignefacturecaracteristique[libcaract].":".$lignefacturecaracteristique[libcaractval])." / ";
				}
				if ($DB_site->num_rows($lignesfacturecaracteristique)) {
					$contenu=substr($contenu, 0, -2);
				}
				$contenu.=";";
			}
			
			//Prix TTC de l'article commandé
			if ($lignefacturePrix == 'on'){
				$contenu.=formaterPrix($lignefacture[prix],2,$seperateurDecimal)." ;";
			}		
			
			//Prix HT de l'article commandé
			if ($lignefacturePrixht == 'on'){
				$contenu.=formaterPrix($lignefacture[prixht],2,$seperateurDecimal)." ;";
			}		
			
			//Personnalisation
			if ($lignefacturePersonnalisation == 'on'){
				$lignesfacturechamp = $DB_site->query("SELECT * FROM lignefacturechamp WHERE lignefactureid = '$lignefacture[lignefactureid]'");
				while($lignefacturechamp=$DB_site->fetch_array($lignesfacturechamp)) {
					$contenu.=secureChaineExport($lignefacturechamp[libelle].":".$lignefacturechamp[valeur])." / ";
				}
				if ($DB_site->num_rows($lignesfacturechamp)) {
					$contenu=substr($contenu, 0, -2);
				}
			}
			
			//Prix personnalisation
			if ($lignefactureLf_prixperso == 'on'){
				$contenu.=formaterPrix($lignefacture[lf_prixperso],2,$seperateurDecimal)."  ;";
			}
			
			//Ecotaxe
			if ($lignefactureEcotaxe == 'on'){
				$contenu.=formaterPrix($lignefacture[ecotaxe],2,$seperateurDecimal)." ;";
			}		

			//Prix d'achat HT
			if ($lignefacturePrixachat == 'on'){
				$contenu.=formaterPrix($lignefacture[prixachat],2,$seperateurDecimal)." ;";
			}		

			//Prix brut HT
			if ($lignefacturePrixbrut == 'on'){
				$contenu.=formaterPrix($lignefacture[prixbrut],2,$seperateurDecimal)." ;";
			}		

			//Taux de TVA
			if ($lignefactureTauxtva == 'on'){
				$contenu.=formaterPrix($lignefacture[tva],2,$seperateurDecimal)."%;";
			}

			//Poids
			if ($lignefacturePoids == 'on'){
				$contenu.=$lignefacture[poids]." gr;";
			}		
			
			//Délai de livraison
			if ($lignefactureDelai == 'on'){
				$contenu.=$lignefacture[delai].";";
			}
			
			//Fournisseur
			if ($lignefactureFournisseur == 'on'){
				$contenu.=$lignefacture[fournisseur].";";
			}
			
			$contenu.="\n";
			fwrite($handle, stripslashes(html_entity_decode($contenu)));		
		}
	} else {
		$contenu.="\n";
		fwrite($handle, stripslashes(html_entity_decode($contenu)));
	}
}

fclose($handle);
$file = realpath(".")."/csv/factures.csv";  
header('Content-Description: File Transfer'); 
header('Content-Type: application/force-download'); 
header('Content-Length: ' . filesize($file)); 
header('Content-Disposition: attachment; filename=' . basename($file)); 
readfile($file); 
exit;

?>
