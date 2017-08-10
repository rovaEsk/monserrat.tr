<?php
include "./includes/header.php";

$referencepage="jourslivraison";
$pagetitle = "Jours ouvrés et joursd fériés - $host - Admin Arobases";



if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//************************************************ GESTION SUPPRESSION JOURS FERIES *********************************************
if ($action == supprimer){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM jour_ferie WHERE jourferieid = '$jourferieid'");
		header("location: jourslivraison.php?alertSuccess0=success");
	}else{
		header('location: jourslivraison.php?erreurdroits=1');	
	}
}
//************************************************ GESTION EDITION JOURS FERIES *************************************************
if ($action == editjoursferies){
	if($admin_droit[$scriptcourant][ecriture]){
		$existe_jour=$DB_site->query_first("SELECT * FROM jour_ferie WHERE jour='$_POST[selectjour]' AND mois='$_POST[selectmois]'");
		if($existe_jour[jour] == ""){
			$DB_site->query("INSERT INTO jour_ferie (jour, mois) VALUES ('$_POST[selectjour]', '$_POST[selectmois]')");
			header("location: jourslivraison.php?alertSuccess=1");
		}else{		
			header("location: jourslivraison.php?alertErreur=1");
		}
	}else{
		header('location: jourslivraison.php?erreurdroits=1');	
	}
}
//************************************************ GESTION EDITION JOURS LIVRAISONS *********************************************
if ($action == modifierjourslivraison){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE jour_ouvert SET ouvert = '0'");
		
		foreach ($jourslivraisons as $jourouvertid=>$value){
			if($value == "on"){
				$DB_site->query("UPDATE jour_ouvert SET ouvert = '1' WHERE jourouvertid = $jourouvertid");
			}		
			header("location: jourslivraison.php?alertSuccess2=success");
		}
	}else{
		header('location: jourslivraison.php?erreurdroits=1');	
	}
}

//************************************************ GESTION AFFICHAGE PAR DEFAUT *********************************************
if (!isset($action) || $action == ""){
	
	$joursouverts= $DB_site->query("SELECT * FROM jour_ouvert");
		while($jourouvert = $DB_site->fetch_array($joursouverts)){			
			$varTemp="checkedouvert".$jourouvert[jourouvertid];
			if ($jourouvert[ouvert] == 1){
				${$varTemp} = "checked=\"checked\"";
			}else{
				${$varTemp} = "";
			}
		}
	$joursferies = $DB_site->query("SELECT * FROM jour_ferie ORDER BY mois,jour");
	while($jourferie = $DB_site->fetch_array($joursferies)){
		//echo($jourferie[jourferieid]);
		
		switch ($jourferie[mois]){
			case 1:
				$mois = "Janvier";
				break;
			case 2:
				$mois = "Février";
				break;
			case 3:
				$mois = "Mars";
				break;
			case 4:
				$mois = "Avril";
				break;
			case 5:
				$mois = "Mai";
				break;
			case 6:
				$mois = "Juin";
				break;
			case 7:
				$mois = "Juillet";
				break;
			case 8:
				$mois = "Août";
				break;
			case 9:
				$mois = "Septembre";
				break;
			case 10:
				$mois = "Octobre";
				break;
			case 11:
				$mois = "Novembre";
				break;
			case 12:
				$mois = "Décembre";
				break;
		}
		
		eval(charge_template($langue,$referencepage,"FerieBit"));
	}
	
	for ($i = 0; $i <= 30; $i++){
		$jour = $i + 1;
		eval(charge_template($langue,$referencepage,"SelectJourBit"));
	}
	if ($alertSuccess0 == 'success'){
		$texteSuccess = $texteSuccess = $multilangue[le_jour_ferie]." ".$multilangue[a_bien_ete_supprime];
		eval(charge_template($langue,$referencepage,"Success"));
	}	
	if ($alertSuccess2 == 'success'){
		$texteSuccess = $multilangue[les_jours_livraison_edites];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertErreur == '1'){
		$texteErreur = $multilangue[le_jour_ferie_pas_ajoute];
		eval(charge_template($langue,$referencepage,"Erreur"));
	}
	if ($alertSuccess == '1'){
		$texteSuccess = $multilangue[le_jour_ferie]." ".$multilangue[a_bien_ete_cre];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	
	eval(charge_template($langue,$referencepage,"Ferie"));
}


$TemplateIncludejavascript = eval(charge_template($langue, $referencepage,"Includejavascript"));
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};



$DB_site->close();
flush();

?>