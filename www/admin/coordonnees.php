<?php

include "./includes/header.php";

$referencepage="coordonnees";
$pagetitle = "Coordonnées - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if($_GET[alert] == 1){
	$texteSuccess=$multilangue[les_coordonnees_ont_ete_mises_a_jour];
	eval(charge_template($langue,$referencepage,"Success"));
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) && $action=="modifparam") {
	if($admin_droit[$scriptcourant][ecriture]){
		foreach($param as $siteid => $value){		
			if(sizeof($param[$siteid]) > 0){			
				foreach($param[$siteid] as $nom_param => $valeur_parametre){				
					$existe_param=$DB_site->query_first("SELECT * FROM parametre WHERE parametre='$nom_param' AND siteid='$siteid'");				
					if($existe_param[parametre] != ""){
						$DB_site->query("UPDATE parametre SET valeur='".securiserSql($valeur_parametre)."' WHERE parametre='$nom_param' AND siteid='$siteid'");
					}else{
						$DB_site->query("INSERT INTO parametre (parametre,valeur,siteid,editable) VALUES ('$nom_param','".securiserSql($valeur_parametre)."','$siteid','1')");
					}				
				}
			}		
		}	
		header("location: coordonnees.php?alert=1");
	}else{
		header('location: coordonnees.php?erreurdroits=1');	
	}
}


if (!isset($action) or $action == ""){
	$Tparams=array();
	$mesparametres=$DB_site->query("SELECT * FROM parametre");
	while($mesparametre=$DB_site->fetch_array($mesparametres)){
		$Tparams[$mesparametre[siteid]][$mesparametre[parametre]]=$mesparametre[valeur];
	}		
	
	//print_r($Tparams);
	
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while ($site = $DB_site->fetch_array($sites)){
		$mail_commande=var2html($Tparams[$site[siteid]][mail_commande]);
		$mail_contact=var2html($Tparams[$site[siteid]][mail_contact]);
		$nom_contact=var2html($Tparams[$site[siteid]][nom_contact]);
		$raison_sociale=var2html($Tparams[$site[siteid]][raison_sociale]);
		$adresse=var2html($Tparams[$site[siteid]][adresse]);
		$adresse2=var2html($Tparams[$site[siteid]][adresse2]);
		$cp=var2html($Tparams[$site[siteid]][cp]);
		$ville=var2html($Tparams[$site[siteid]][ville]);
		$telephone=var2html($Tparams[$site[siteid]][telephone]);
		$fax=var2html($Tparams[$site[siteid]][fax]);
		$siret=var2html($Tparams[$site[siteid]][siret]);
		$tva=var2html($Tparams[$site[siteid]][tva]);
		
		eval(charge_template($langue,$referencepage,"SiteBit"));
	}	
}



//include "./includes/footer.php";
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();
?>