<?php
include "./includes/header.php";

$referencepage="fermetureboutique";
$pagetitle = "Fermeture de la boutique - $host - Admin Arobases";



if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}/******************* ACTION ACTIVE ********************************/

/****************************** ACTION EDIT/AJOUT FERMETURE *******************************************/
if ($action == editerfermeture){
	if($admin_droit[$scriptcourant][ecriture]){
		$valssites = $DB_site->query("SELECT * FROM site");	
		while ($valssite = $DB_site->fetch_array($valssites)){
		$valsfermetures = $DB_site->query("SELECT * FROM fermetureboutique");
		//while($valsfermeture = $DB_site->fetch_array($valsfermetures)){
			$contenuDyn = "contenufermeture_".$valssite[siteid] ;
			$passagecommandeDyn = "commande_".$valssite[siteid] ;
			$debutdateDyn = "datedeb_".$valssite[siteid] ;
			$findateDyn = "datefin_".$valssite[siteid] ;
			$debtimeDyn = "timedeb_".$valssite[siteid];
			$fintimeDyn = "timefin_".$valssite[siteid] ;
			$activeDyn = "active_".$valssite[siteid];
			
			
			if (${$findateDyn}=='00/00/0000' || ${$findateDyn}=='') $findateDyn='0/0/0';
			if (${$debutdateDyn}=='00/00/0000' || ${$debutdateDyn}=='') $debutdateDyn='0/0/0';
			if (${$debtimeDyn}=='00:00:00' || ${$debtimeDyn}=='') $debtimeDyn='0:0:0';
			if (${$fintimeDyn}=='00:00:00' ||${ $fintimeDyn}=='') $fintimeDyn='0:0:0';
			
			//var_dump(${$contenuDyn});
			
			$date1=explode("/",${$debutdateDyn});
			$date2=explode("/",${$findateDyn});
			$heure1=explode(":",${$debtimeDyn});
			$heure2=explode(":",${$fintimeDyn});
			
			$debut=mktime($heure1[0],$heure1[1],$heure1[2],$date1[1],$date1[0],$date1[2]);
			$fin=mktime($heure2[0],$heure2[1],$heure2[2],$date2[1],$date2[0],$date2[2]);
			
			$test = $DB_site->query_first("SELECT * FROM fermetureboutique WHERE siteid = '$valssite[siteid]'");
			
			if ( $test[id] != ""){
				if($fin>$debut ){
					$DB_site->query("UPDATE fermetureboutique SET debut = '$debut', fin = '$fin', contenu = '".securiserSql(${$contenuDyn}, "html")."', passagecommande = '${$passagecommandeDyn}', active = '${$activeDyn}' WHERE siteid='$valssite[siteid]'");
				}else{
					$texteErreur = "$valssite[libelle] : $multilangue[date_fin_superieure_date_debut]";
					eval(charge_template($langue,$referencepage,"Erreur"));
				}	
			}else{
				$DB_site->query("INSERT INTO fermetureboutique(siteid, contenu, active, passagecommande, debut, fin) 
									VALUES('$valssite[siteid]', '".securiserSql(${$contenuDyn}, "html")."', '${$activeDyn}', ${$passagecommandeDyn}, '$debut', '$fin')");
			}
			//$action="";
			header("location: fermetureboutique.php?alertSuccess1=success");
			
		//}		
	}
	}else{
		header('location: fermetureboutique.php?erreurdroits=1');	
	}
}
//************************************************ GESTION AFFICHAGE INITIAL *********************************************
if (!isset($action) || $action == ""){
	
	$sites= $DB_site->query("SELECT * FROM site");
	while ($site = $DB_site->fetch_array($sites)){
		
		$modalitesfermeture = $DB_site->query_first("SELECT * FROM fermetureboutique WHERE siteid = '$site[siteid]'");
		
		if  ($modalitesfermeture[passagecommande]==0)
			$checked0="checked";
		if  ($modalitesfermeture[passagecommande]==1)
			$checked1="checked";

		
		$datedebut = date("d/m/Y",$modalitesfermeture[debut]) ;
		$datefin = date("d/m/Y",$modalitesfermeture[fin]) ;
		$heuredeb = date("H:i",$modalitesfermeture[debut]) ;
		$heurefin = date("H:i",$modalitesfermeture[fin]) ;
		if ($datefin=='01/01/1970') $datefin='00/00/0000';
		if ($datedebut=='01/01/1970') $datedebut='00/00/0000';
		if ($heuredeb=='01:01') $heuredeb='00:00';
		if ($heurefin=='01:01') $heurefin='00:00';
			
		
		if($modalitesfermeture[active]==1){
			$color_aff = "vert";
			$color2_aff = "green";
			$ico_aff = "fa-check-square-o";
		}else{
			$color_aff = "rouge";
			$color2_aff = "red";
			$ico_aff = "fa-square-o";		
		}
		
		
		if($modalitesfermeture[active]==1){
			$checked1 = "checked=\"checked\"";
		}else{
			$checked1 = "";
		}
		
		if($modalitesfermeture[passagecommande]==1){
			$checked2 = "checked=\"checked\"";
		}else{
			$checked2 = "";
		}		
		
	eval(charge_template($langue,$referencepage,"SiteListeBit"));
	}
	if ($alertSuccess1 == 'success'){
		$texteSuccess = $multilangue[modalites_fermeture_boutique];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	eval(charge_template($langue,$referencepage,"FormBit"));
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