<?php
/**
* Export personnalisé par sélection des utilisateurs
* @Benjamin
* @Enjoy
*/
	
require "includes/admin_global.php";

//construction des titres des colonnes
$contenu="";

if ($exporterTitre == 1){
	
	//Identifiant utilisateur
	if ($utilisateurUserid == 'on') $contenu.="$multilangue[identifiant_utilisateur];";
	
	//Email
	if ($utilisateurMail == 'on') $contenu.="$multilangue[email];";
	
	//Mot de passe crypté
	if ($utilisateurPassword == 'on') $contenu.="$multilangue[mot_de_passe_crypte];";
	
	//Civilité
	if ($utilisateurCivilite == 'on') $contenu.="$multilangue[civilite];";
	
	//Nom
	if ($utilisateurNom == 'on') $contenu.="$multilangue[nom];";
	
	//Prénom
	if ($utilisateurPrenom == 'on') $contenu.="$multilangue[prenom];";
	
	//Adresse
	if ($utilisateurAdresse == 'on') $contenu.="$multilangue[adresse];";
	
	//Code postal
	if ($utilisateurCodepostal == 'on') $contenu.="$multilangue[code_postal];";
	
	//Ville
	if ($utilisateurVille == 'on') $contenu.="$multilangue[ville];";
	
	//Téléphone
	if ($utilisateurTelephone == 'on') $contenu.="$multilangue[telephone];";
	
	//Raison sociale
	if ($utilisateurRaisonsociale == 'on') $contenu.="$multilangue[raison_sociale];";
	
	//Pays
	if ($utilisateurPays == 'on') $contenu.="$multilangue[pays];";
	
	//Langue
	if ($utilisateurLangue == 'on') $contenu.="$multilangue[langue];";
	
	//Nombre de commandes
	if ($utilisateurNbCommandes == 'on') $contenu.="$multilangue[nb_commandes];";
	
	//Montant commandé
	if ($utilisateurMontantCommande == 'on') $contenu.="$multilangue[montant_total_commandes];";
	
	//Module Remise sur tout le catalogue par groupes d utilisateurs
	if ($utilisateurGroupeid == 'on') $contenu.="$multilangue[identifiant_groupe];";
	if ($utilisateurPctremise == 'on') $contenu.="$multilangue[pourcentage_remise];";
	
	//Module fidélité
	if ($utilisateurNbpoints == 'on') $contenu.="$multilangue[points_fidelite];";
	
	$contenu.="\n";
}

$filename = './csv/utilisateurs.csv';
if (is_writable($filename)) {
	if (!$handle = fopen($filename, 'w')) {
		echo "$multilangue[erreur_ouverture_fichier] ($filename)";
		exit;
	}
	if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) {
		echo "$multilangue[erreur_ecriture_fichier] ($filename)";
		exit;
	}
}else{
	echo "$multilangue[erreur_accessibilite_ecriture_fichier] ($filename)";
	exit();
}

$handle = fopen($filename, 'a');


//requete en fonction des critères sélectionnés
$where="";
$or="";
$innerJoin="";

//Langue
if ($critereLangue != ""){
	$where.=" AND langue='$critereLangue'";
}

//Pays
if ($criterePays != ""){
	$where.=" AND paysid='$criterePays'";
}

//Inscrit à la newsletter?
if ($critereInscritNewsletter != ""){
	$where.=" AND recevoir='$critereInscritNewsletter'";
}

//Deja commande
if ($critereDejaCommande != ""){
	$tabUtilisateursClients=array();
	$utilisateursClients=$DB_site->query("SELECT DISTINCT(userid) FROM facture WHERE etatid IN (1,5)");	
	while ($utilisateurClient=$DB_site->fetch_array($utilisateursClients)){
		array_push($tabUtilisateursClients,$utilisateurClient[userid]);
	}
	
	switch($critereDejaCommande){
		case 0:
			$where.=" AND u.userid NOT IN (".implode(',',$tabUtilisateursClients).")";
		break;
		case 1:
			$where.=" AND u.userid IN (".implode(',',$tabUtilisateursClients).")";
		break;
		default:
			exit;
		break;
	}
}

//Module pro
if ($criterePro != ""){
	$where.=" AND pro='$criterePro'";
}

//Module fidélité
if ($critereFidelite != ""){
	$where.=" AND pro='$critereFidelite'";
}

//Dates d'inscription
if ($critereDateDebut != "" && $critereDateFin != ""){
	$where.=" AND dateinscription > '".convertirChaineEnDate($critereDateDebut)."' AND dateinscription < '".convertirChaineEnDate($critereDateFin)."'";
}

$sql = "SELECT DISTINCT(u.userid),u.* FROM utilisateur u $innerJoin WHERE 1=1 $where ORDER BY userid";

$utilisateurs=$DB_site->query($sql);

while($utilisateur=$DB_site->fetch_array($utilisateurs)) {
	
	$contenu="";
	
	//Identifiant utilisateur
	if ($utilisateurUserid == 'on'){
		$contenu.=$utilisateur[userid].";";
	}
	
	//Email
	if ($utilisateurMail == 'on'){
		$contenu.=$utilisateur[mail].";";
	}
	
	//Mot de passe crypté
	if ($utilisateurPassword == 'on'){
		$contenu.=$utilisateur[password].";";
	}
	
	//Civilité
	if ($utilisateurCivilite == 'on'){
		$contenu.=retournerCivilite($utilisateur[civilite]).";";
	}
	
	//Nom
	if ($utilisateurNom == 'on'){
		$contenu.=secureChaineExport($utilisateur[nom]).";";
	}
	
	//Prénom
	if ($utilisateurPrenom == 'on'){
		$contenu.=secureChaineExport($utilisateur[prenom]).";";
	}
	
	//Adresse
	if ($utilisateurAdresse == 'on'){
		$contenu.=secureChaineExport($utilisateur[adresse]).";";
	}
	
	//Code postal
	if ($utilisateurCodepostal == 'on'){
		$contenu.=secureChaineExport($utilisateur[codepostal]).";";
	}
	
	//Ville
	if ($utilisateurVille == 'on'){
		$contenu.=secureChaineExport($utilisateur[ville]).";";
	}
	
	//Téléphone
	if ($utilisateurTelephone == 'on'){
		$contenu.=secureChaineExport($utilisateur[telephone]).";";
	}
	
	//Raison sociale
	if ($utilisateurRaisonsociale == 'on'){
		$contenu.=secureChaineExport($utilisateur[raisonsociale]).";";
	}
	
	//Pays
	if ($utilisateurPays == 'on'){
		$contenu.=secureChaineExport(retournerLibellePays($DB_site,$utilisateur[paysid])).";";
	}
	
	//Langue
	if ($utilisateurLangue == 'on'){
		$contenu.=$utilisateur[langue].";";
	}
	
	//Nombre de commandes
	if ($utilisateurNbCommandes == 'on'){
		$nbCommande=$DB_site->query_first("SELECT COUNT(factureid) FROM facture WHERE userid='$utilisateur[userid]' AND etatid IN (1,5)");
		$contenu.=$nbCommande[0].";";
	}
	
	//Montant commandé
	if ($utilisateurMontantCommande == 'on'){
		$montantCommande=$DB_site->query_first("SELECT SUM(montanttotal_ttc) FROM facture WHERE userid='$utilisateur[userid]' AND etatid IN (1,5)");
		$contenu.=formaterPrix($montantCommande[0]).";";
	}
	
	//Groupeid
	if ($utilisateurGroupeid == 'on'){
		$contenu.=$utilisateur[groupeid].";";
	}
	
	//Pct remise
	if ($utilisateurPctremise == 'on'){
		$contenu.=$utilisateur[pctremise].";";
	}
	
	//Points fidélité
	if ($utilisateurNbpoints == 'on'){
		$contenu.=$utilisateur[nbpoints].";";
	}	
	
	$contenu.="\n";
	fwrite($handle, stripslashes(html_entity_decode($contenu)));
}

fclose($handle);
$file = realpath(".")."/csv/utilisateurs.csv";  
header('Content-Description: File Transfer'); 
header('Content-Type: application/force-download'); 
header('Content-Length: ' . filesize($file)); 
header('Content-Disposition: attachment; filename=' . basename($file)); 
readfile($file); 
exit;

?>
