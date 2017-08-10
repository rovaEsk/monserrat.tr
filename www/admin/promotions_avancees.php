<?php
include "./includes/header.php";

$referencepage="promotions_avancees";
$pagetitle = "Promotions avancées - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) and $action == "importer2") {
	$msg_erreur_import = "";
	$msg_import = "";
	$nb_articles = 0;
	if (!empty($_FILES['fichier_import']['name'])) {
		$nom_fic=$rootpath."configurations/$host/importcsv/import.csv" ;
		copier_image($nom_fic,"fichier_import") ;
		$rh = fopen($nom_fic, 'rb');
		if ($rh) {
			$row = 1 ;
			while ($data = fgetcsv ($rh, 10000, ";"))  {
				$num = count ($data);
				for ($c = 0 ; $c < $num ; $c++)
					$montableau[$row][$c] = $data[$c] ;
				$row++;
			}
		}
		fclose($rh);				
		$folder = $rootpath."configurations/$host/importcsv";
		$dossier = opendir($folder);
		while ($fichier = readdir($dossier)) {
			if ($fichier != "." && $fichier != "..") {
				if (file_exists($folder."/import.csv"))
					unlink($folder."/import.csv");
			}
		}
		if ((isset($montableau)) and (count($montableau) > 0)) {
			$erreur = "" ;
			foreach ($montableau as $key => $value) {
				if ($key != 1) {
					switch ($choix) {
						case "LU_P": // Liste unique de produits
							$identifiant = $montableau[$key][0] ;
							$articlecount = $DB_site->query_first("SELECT count(*) FROM article WHERE $champ = '$identifiant'"); 
							if($articlecount[0] == "1") {
								$article = $DB_site->query_first("SELECT artid FROM article WHERE $champ = '$identifiant'");
								$existeArticle = $DB_site->query_first("SELECT COUNT(*) FROM operation_cible WHERE operationid = '$operationid' AND artid = '$article[artid]'");
								if ($existeArticle[0] == 0) {
									$DB_site->query("INSERT INTO operation_cible (operationid, artid, typeA, typeB, typeC) VALUES ('$operationid', '$article[artid]', '0', '0', '0')");
									$nb_articles++;
								}
							} else
								echo "$reference : $multilangue[reference_existe_pas_ou_double] ....<br>";	
							break;
						case "LA_P": // Liste A de produits
							$identifiant = $montableau[$key][0] ;
							$articlecount = $DB_site->query_first("SELECT count(*) FROM article WHERE $champ = '$identifiant'"); 
							if($articlecount[0] == "1") {
								$article = $DB_site->query_first("SELECT artid FROM article WHERE $champ = '$identifiant'");
								$DB_site->query("INSERT INTO operation_cible (operationid, artid, typeA, typeB, typeC) VALUES ('$operationid', '$article[artid]', '1', '0', '0')");
								$nb_articles++;
							} else
								echo "$reference : $multilangue[reference_existe_pas_ou_double] ....<br>";	
							break;
						case "LB_P": // Liste B de produits
							$identifiant = $montableau[$key][0] ;
							$articlecount = $DB_site->query_first("SELECT count(*) FROM article WHERE $champ = '$identifiant'"); 
							if($articlecount[0] == "1") {
								$article = $DB_site->query_first("SELECT artid FROM article WHERE $champ = '$identifiant'");
								$DB_site->query("INSERT INTO operation_cible (operationid, artid, typeA, typeB, typeC) VALUES ('$operationid', '$article[artid]', '0', '1', '0')");
								$nb_articles++;
							} else
								echo "$reference : $multilangue[reference_existe_pas_ou_double] ....<br>";	
							break;
						case "LC_P": // Liste C de produits
							$identifiant = $montableau[$key][0] ;
							$articlecount = $DB_site->query_first("SELECT count(*) FROM article WHERE $champ = '$identifiant'"); 
							if($articlecount[0] == "1") {
								$article = $DB_site->query_first("SELECT artid FROM article WHERE $champ = '$identifiant'");
								$DB_site->query("INSERT INTO operation_cible (operationid, artid, typeA, typeB, typeC) VALUES ('$operationid', '$article[artid]', '0', '0', '1')");
								$nb_articles++;
							} else
								echo "$reference : $multilangue[reference_existe_pas_ou_double] ....<br>";	
							break;
						case "LCO_P": // Liste de combinaisons fixes d'articles
							$identifiantA = $montableau[$key][0] ;
							$identifiantB = $montableau[$key][1] ;
							$identifiantC = $montableau[$key][2] ;
							$articlecountA = $DB_site->query_first("SELECT COUNT(*) FROM article WHERE $champ = '$identifiantA'"); 
							$articlecountB = $DB_site->query_first("SELECT COUNT(*) FROM article WHERE $champ = '$identifiantB'"); 
							$articlecountC = $DB_site->query_first("SELECT COUNT(*) FROM article WHERE $champ = '$identifiantC'"); 
							if(($articlecountA[0] == "1" && $articlecountB[0] == "1") || ($articlecountA[0] == "1" && $articlecountC[0] == "1") || ($articlecountB[0] == "1" && $articlecountC[0] == "1")) {
								$articleA = $DB_site->query_first("SELECT artid FROM article WHERE $champ = '$identifiantA'");
								$articleB = $DB_site->query_first("SELECT artid FROM article WHERE $champ = '$identifiantB'");
								$articleC = $DB_site->query_first("SELECT artid FROM article WHERE $champ = '$identifiantC'");
								$DB_site->query("INSERT INTO operation_combinaison (operationid, artidA, artidB, artidC) VALUES ('$operationid', '$articleA[artid]', '$articleB[artid]', '$articleC[artid]')");
								$nb_articles++;
							} else
								echo "$multilangue[erreur_operation_import_combinaison] ($referenceA &nbsp; $referenceB &nbsp; $referenceC) ....<br>";	
							break;
					}
				}
			}							
		}
		$texteSuccess = $nb_articles . " $multilangue[ligne_s_creee_s]";
		eval(charge_template($langue,$referencepage,"Success"));
		$action = "";
	} else {
		$texteErreur = $multilangue[chemin_obligatoire];
		eval(charge_template($langue,$referencepage,"Erreur"));
		$action = "";
	}
}

if (isset($action) and $action == "importer") {
	$operation = $DB_site->query_first("SELECT * FROM operation WHERE operationid = '$operationid'");
	if ($operation[operationid]) {
		$i_choix = 0;
		$checkedChoix = "";
		$offre = $DB_site->query_first("SELECT * FROM offre WHERE offreid = '$operation[offreid]'");
		if ($offre[listeUnique] == "1") {
			if ($i_choix == 0)
				$checkedChoix = "checked";
			eval(charge_template($langue,$referencepage,"Import_LU_P"));
			$i_choix ++;
		}
		if ($offre[listeA] == "1") {
			if ($i_choix == 0)
				$checkedChoix = "checked";
			eval(charge_template($langue,$referencepage,"Import_LA_P"));
			$i_choix ++;
		}
		if ($offre[listeB] == "1") {
			if ($i_choix == 0)
				$checkedChoix = "checked";
			eval(charge_template($langue,$referencepage,"Import_LB_P"));
			$i_choix ++;							
		}
		if ($offre[listeC] == "1") {
			if ($i_choix == 0)
				$checkedChoix = "checked";
			eval(charge_template($langue,$referencepage,"Import_LC_P"));
			$i_choix ++;				
		}
		if ($offre[listeCombinaison] == "1") {
			if ($i_choix == 0)
				$checkedChoix = "checked";
			eval(charge_template($langue,$referencepage,"Import_LCO_P"));
		}			
			
		$libNavigSupp = "$multilangue[import_export_produits]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		eval(charge_template($langue,$referencepage,"Import"));
	} else {
		$texteErreur = $multilangue[operation_obligatoire];
		eval(charge_template($langue,$referencepage,"Erreur"));
		$action = "";
	}
}

if (isset($action) and $action == "modifier2") {
	
	if ($admin_droit[$scriptcourant][ecriture]) {
		if (!$_POST[operationid]) {
			$DB_site->query("INSERT INTO operation (offreid, siteid) VALUES ('".$_POST[offreid]."', '".$_POST[siteid]."')");
			$operationid = $DB_site->insert_id();
		}
		$DB_site->query("UPDATE operation SET libelle = '".securiserSql($_POST[libelle])."', description = '".securiserSql($_POST[description])."', 
						datedebut = '".convertirChaineEnDate($_POST[datedebut])."', datefin = '".convertirChaineEnDate($_POST[datefin])."', montantremise = '".$_POST[montantremise]."',  
						typeremise = '".$_POST[typeremise]." 'WHERE operationid = '$operationid'");
		if ($_POST[offreid] == "8")
			$DB_site->query("UPDATE operation SET conditionachat = '".$_POST[conditionachat]."', montantminimum = '".$_POST[montantminimum]."' WHERE operationid = '$operationid'");
		elseif (($_POST[offreid] == "3" || $_POST[offreid] == "4" || $_POST[offreid] == "5" || $_POST[offreid] == "6" || $_POST[offreid] == "7" || $_POST[offreid] == "9" || $_POST[offreid] == "10" || $_POST[offreid] == "11") && $_POST[montantminimum])
			$DB_site->query("UPDATE operation SET montantminimum = '".$_POST[montantminimum]."' WHERE operationid = '$operationid'");
		$DB_site->query("DELETE FROM operation_cumulable WHERE operationid = '$operationid'");
		if (is_array($chk_operations)) {
			foreach ($chk_operations as $cle=>$valeur)
				$DB_site->query("INSERT INTO operation_cumulable (operationid, operationid_cumulable) values ('$operationid', '$valeur')");
		}	
		header('location: promotions_avancees.php');	
		exit();		
	} else {
		header('location: promotions_avancees.php?erreurdroits=1');	
		exit();
	}
}


if (isset($action) and $action == "modifier") {
	if(isset($operationid)){
		$operation = $DB_site->query_first("SELECT * FROM operation WHERE operationid = '$operationid'");
		$siteid = $operation[siteid];
		$offreid = $operation[offreid];
		$site = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$siteid'");
		$devise = $DB_site->query_first("SELECT * FROM devise WHERE deviseid = '$site[deviseid]'");
		$offre = $DB_site->query_first("SELECT o.*, ol.description FROM offre o INNER JOIN offre_langue ol USING (offreid) WHERE o.offreid = '$offreid' AND ol.langueid = '$admin_langueid'");
		if ($offre[immediat] == "1") {
			$libRemise = $multilangue[remise_immediate];
		} else {
			$libRemise = $multilangue[remise_ulterieure];
		}			
		if ($operation[cumulpromotions]) {
			$checked_cumulpromotions1 = "checked=\"checked\"";
		} else {
			$checked_cumulpromotions0 = "checked=\"checked\"";
		}
		if ($operation[typeremise]) {
			$checked_typeremise1 = "checked=\"checked\"";
		} else {
			$checked_typeremise0 = "checked=\"checked\"";
		}
		$operation[datedebut] = convertirDateEnChaine($operation[datedebut]);
		$operation[datefin] = convertirDateEnChaine($operation[datefin]);
		
		// certains offres necéssitent de remplir des champs particuliers
		switch ($offreid) {
			case "1" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise0"));
			break;	
			case "2" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise0"));
			break;						
			case "3" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise0"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;						
			case "4" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise1"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;						
			case "5" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise0"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;						
			case "6" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise1"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;														
			case "7" : // X € (ou X%) de réduction à partir de Y € d'achat
				eval(charge_template($langue,$referencepage,"TypeRemise"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;
			case "8" : // X € (ou X%) de réduction par tranche de Y € d'achat
				eval(charge_template($langue,$referencepage,"TypeRemise"));
				eval(charge_template($langue,$referencepage,"ConditionAchat"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;
			case "9" : // 1 produit acheté = envoi d'un code de réduction en % ou en € valable sur le prochain achat
				eval(charge_template($langue,$referencepage,"TypeRemise"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;	
			case "10" : // 1 produit A acheté + 1 produit B = envoi d'un code de réduction en % ou en € valable sur le prochain achat
				eval(charge_template($langue,$referencepage,"TypeRemise"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;
			case "11" : // 1 produit A acheté + 1 produit B + 1 produit C = envoi d'un code de réduction en % ou en € valable sur le prochain achat
				eval(charge_template($langue,$referencepage,"TypeRemise"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;		
		}
		
		$autresOperations = $DB_site->query("SELECT * FROM operation WHERE (activeV1 = '1' OR activeV2 = '1') AND datefin >= '".date("Y-m-d")."' AND operationid != '$operationid'");	
		while ($autreOperation=$DB_site->fetch_array($autresOperations)) {
			$checked_operation = "";
			$dejaCumulable = $DB_site->query_first("SELECT * FROM operation_cumulable WHERE operationid = '$operation[operationid]' AND operationid_cumulable = '$autreOperation[operationid]'");	
			if ($dejaCumulable[operationid] != "")
				$checked_operation = "checked";
			eval(charge_template($langue,$referencepage,"AutreOperationBit"));
		}
		
		$libNavigSupp = "$multilangue[modif_promotion_avancee]";
		$multilangue[ajt_promotion_avancee] = $multilangue[modif_promotion_avancee];
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		eval(charge_template($langue,$referencepage,"Modification"));
		
	}else{
		$siteid = $_POST[siteid];
		$offreid = $_POST[offreid];
		$site = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$siteid'");
		$devise = $DB_site->query_first("SELECT * FROM devise WHERE deviseid = '$site[deviseid]'");
		$offre = $DB_site->query_first("SELECT o.*, ol.description FROM offre o INNER JOIN offre_langue ol USING (offreid) WHERE o.offreid = '$offreid' AND ol.langueid = '$admin_langueid'");
		if ($offre[immediat] == "1") {
			$libRemise = $multilangue[remise_immediate];
		} else {
			$libRemise = $multilangue[remise_ulterieure];
		}			
		$checked_cumulpromotions0 = "checked=\"checked\"";
		$checked_typeremise0 = "checked=\"checked\""; 
		
		// certains offres necéssitent de remplir des champs particuliers
		switch ($offreid) {
			case "1" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise0"));
			break;	
			case "2" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise0"));
			break;						
			case "3" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise0"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;						
			case "4" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise1"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;						
			case "5" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise0"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;						
			case "6" : // 
				eval(charge_template($langue,$referencepage,"TypeRemise1"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;														
			case "7" : // X € (ou X%) de réduction à partir de Y € d'achat
				eval(charge_template($langue,$referencepage,"TypeRemise"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;
			case "8" : // X € (ou X%) de réduction par tranche de Y € d'achat
				eval(charge_template($langue,$referencepage,"TypeRemise"));
				eval(charge_template($langue,$referencepage,"ConditionAchat"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;
			case "9" : // 1 produit acheté = envoi d'un code de réduction en % ou en € valable sur le prochain achat
				eval(charge_template($langue,$referencepage,"TypeRemise"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;	
			case "10" : // 1 produit A acheté + 1 produit B = envoi d'un code de réduction en % ou en € valable sur le prochain achat
				eval(charge_template($langue,$referencepage,"TypeRemise"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;
			case "11" : // 1 produit A acheté + 1 produit B + 1 produit C = envoi d'un code de réduction en % ou en € valable sur le prochain achat
				eval(charge_template($langue,$referencepage,"TypeRemise"));
				eval(charge_template($langue,$referencepage,"MontantMinimum"));
			break;		
		}
		
		$autresOperations = $DB_site->query("SELECT * FROM operation WHERE (activeV1 = '1' OR activeV2 = '1') AND datefin >= '".date("Y-m-d")."'");	
		while ($autreOperation=$DB_site->fetch_array($autresOperations))	
			eval(charge_template($langue,$referencepage,"AutreOperationBit"));
		
		$libNavigSupp = "$multilangue[ajt_promotion_avancee]";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		eval(charge_template($langue,$referencepage,"Modification"));
	}
	
}

if (isset($action) && $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM operation WHERE operationid = '$operationid'");
		$DB_site->query("DELETE FROM operation_cible WHERE operationid = '$operationid'");
		$DB_site->query("DELETE FROM operation_combinaison WHERE operationid = '$operationid'");
		$DB_site->query("DELETE FROM operation_cumulable WHERE operationid = '$operationid'");
		$DB_site->query("DELETE FROM operation_panier WHERE operationid = '$operationid'");
		$DB_site->query("DELETE FROM operation_panier_temp WHERE operationid = '$operationid'");
		header('location: promotions_avancees.php');
		exit();
	}else{
		header('location: promotions_avancees.php?erreurdroits=1');	
		exit();
	}
}

if (!isset($action) or $action == ""){
	// Sites
	$listeSites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while ($listeSite=$DB_site->fetch_array($listeSites)) {
		eval(charge_template($langue,  $referencepage, "SiteBit"));
	}
	// Offres dispo
	$listeOffres = $DB_site->query("SELECT o.offreid, ol.description FROM offre o INNER JOIN offre_langue ol USING (offreid) WHERE ol.langueid = '$admin_langueid' AND o.active = '1' ORDER BY offreid");
	while ($listeOffre=$DB_site->fetch_array($listeOffres)) {
		eval(charge_template($langue,  $referencepage, "OffreBit"));
	}
	
	// Templates
	eval(charge_template($langue,  $referencepage, "Liste"));
	$libNavigSupp = $multilangue[liste_promotions_avancees];
	eval(charge_template($langue,  $referencepage, "NavigSupp"));
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