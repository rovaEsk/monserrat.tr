<?php 

require "includes/admin_global.php";


if(isset($_POST['debut']) and isset($_POST['fin']) and ($_POST['debut'] < $_POST['fin']) and !empty($_POST['debut']) and !empty($_POST['fin'])){
	$filename = './csv/referent.csv';
	$contenu = "";
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
	
	if(isset($_POST['choix_referent']) and !empty($_POST['choix_referent'])){
		if ($_POST['choix_referent'] == '!links!'){
			$where = "AND motcle = ''";
			$groupby = 'referent';
			$exportmotcle = 0;
		}else{
			$where = "AND referent = '".securiserSql($_POST['choix_referent'])."'";
			$groupby = 'motcle';
			$exportmotcle = 1;
		}
	}else{
		$where = "";
		$groupby = 'referent';
		$exportmotcle = 0;
	}
	
	if ($exportmotcle){
		$contenu = "Réferents;Mot clé;Hit;Nombre de commande;Montant des commandes \n";
	}else{
		$contenu = "Réferents;Hit;Nombre de commande;Montant des commandes \n";
	}
	
	fwrite($handle, stripslashes(html_entity_decode($contenu)));
	$export = $DB_site->query("SELECT *, SUM(hit) AS hit, SUM(nb_commande) AS nb_commande, SUM(montant_commande) AS montant_commande FROM referents WHERE dateline >= '".securiserSql(convertirChaineEnDate($_POST['debut']))."' AND dateline <= '".securiserSql(convertirChaineEnDate($_POST['fin']))."' $where GROUP BY $groupby");
	if ($_POST['choix_referent'] == '!links!'){
		$moteurs=$DB_site->query("SELECT nom FROM moteurs_recherche");
		$mdr=array();
		while ($moteur=$DB_site->fetch_array($moteurs)) {
			$mdr[]=$moteur[nom];
		}
				
		while($recupExport = $DB_site->fetch_array($export)){
			if (!in_array($recupExport['referent'],$mdr)){
				$contenu = "";
				$contenu .= $recupExport['referent'].';';
				$contenu .= $recupExport['hit'].';'.$recupExport['nb_commande'].';'.formaterPrix($recupExport['montant_commande']);
				$contenu .= "\n";
				fwrite($handle, stripslashes(html_entity_decode($contenu)));
			}
		}	
	}else{
		while($recupExport = $DB_site->fetch_array($export)){
			$contenu = "";
			$contenu .= $recupExport['referent'].';';
			if ($exportmotcle){
				$contenu .= $recupExport['motcle'].';';
			}
			$contenu .= $recupExport['hit'].';'.$recupExport['nb_commande'].';'.formaterPrix($recupExport['montant_commande']);
			$contenu .= "\n";
			fwrite($handle, stripslashes(html_entity_decode($contenu)));
		}
	}
	
	fclose($handle);
	$file = realpath(".")."/csv/referent.csv";  
	header('Content-Description: File Transfer'); 
	header('Content-Type: application/force-download'); 
	header('Content-Length: ' . filesize($file)); 
	header('Content-Disposition: attachment; filename=' . basename($file)); 
	readfile($file); 

}else{
	header('Location: referents.php'); 
}