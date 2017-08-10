<?php
include "./includes/header.php";

$referencepage = "ventes_par_article";
$pagetitle = "Ventes par article - $host - Admin Arobases";

if (! parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

$class_menu_ventes_par_article_active = $class_menu_marketing_active = "active";

if ($etatSelected != '0' and $etatSelected != '1' and $etatSelected != '2')
	$etatSelected = $params ['etat_calcul_marge'];
switch ($etatSelected){
	case '0' :
		$etatid = '5';
		break;
	case '1' :
		$etatid = '1';
		break;
	case '2' :
	default :
		$etatid = '1,5';
		break;
}

$datedebut = $datedebutform;
$datefin = $datefinform;
$vente = $_POST [vente];
$searchAll = $_POST [searchAll];

/**
 * ************************* GESTION DATE ************************************************************************************
 */

// header("location: ventes_par_article.php?datedebut=$datedebut&datefin=$datefin&categories=$_POST[categories]&vente=$_POST[vente]&searchAll=$_POST[searchAll]");

/**
 * ************************* GESTION RECUPERATION DATES / CATEGORIES ************************************************************************************
 */
/*
 * if ($action == "doafficher"){ $action=""; }
 */

/**
 * ************************* AFFICHAGE PRINCIPAL ************************************************************************************
 */

if (! isset($action) || $action == ""){
	// INITIALISATION $WHERE
	$categories = explode(",", $_POST [categories]);
	
	/*
	 * if (is_array($categories)){ foreach ($categories as $value){ $eviction = $DB_site->query_first("SELECT parentid FROM categorie WHERE catid = '$value'"); if ($eviction[parentid] == 0){ $haschild = in_array(); } } }
	 */
	
	if (is_array($categories)){
		$where = "AND a.catid IN (";
		foreach($categories as $value){
			$cats = catid_enfants($DB_site, $value);
			$where .= $value.substr($cats, 1).',';
		}
		$where = substr($where, 0, - 1);
		$where .= ")";
	} else {
		$where = "";
	}
	
	
	// GESTION AFFICHAGE TABLEAU VPA*********************************************
	
	$ajd = date('Y-m-d');
	$initDebut = date("Y-m-d", mktime(0, 0, 0, date("m"), 01, date("Y"))); // Debut du mois
	if ($datedebut && $datefin){
		$datedebut2 = explode('/', $datedebut);
		$datedebut2 = $datedebut2 [2].'-'.$datedebut2 [1].'-'.$datedebut2 [0];
		$datefin2 = explode('/', $datefin);
		$datefin2 = $datefin2 [2].'-'.$datefin2 [1].'-'.$datefin2 [0];
		if ($datedebut2 > $datefin2){
			$datefin = $datedebut;
			$datefin2 = $datedebut2;
		}
	} else {
		$datedebut2 = $initDebut;
		$datedebut = explode('-', $initDebut);
		$datedebut = $datedebut [2].'/'.$datedebut [1].'/'.$datedebut [0];
		$datefin2 = $ajd;
		$datefin = explode('-', $ajd);
		$datefin = $datefin [2].'/'.$datefin [1].'/'.$datefin [0];
	}
	
	$periode3 = explode('-', $datedebut2);
	$periode1 = $periode3 [1];
	$periode4 = explode('-', $datefin2);
	$periode2 = $periode4 [1];
	$tabmois = array (
			"",
			$multilangue [janvier],
			$multilangue [fevrier],
			$multilangue [mars],
			$multilangue [avril],
			$multilangue [mai],
			$multilangue [juin],
			$multilangue [juillet],
			$multilangue [aout],
			$multilangue [septembre],
			$multilangue [octobre],
			$multilangue [novembre],
			$multilangue [decembre] 
	);
	// $fichier = fopen("export/journal_ventes_articles.csv", "w+");
	// $en_tete = "$multilangue[designation];";
	// $en_tete = ";;Du $datedebut Au $datefin;;;\nArticle;";
	// fputs($fichier, $en_tete);
	
	// cast de l'année en int, pour l'incrémenter ensuite à chaque nouvelle année passée, dans le tableau.
	// var_dump($datedebut);var_dump($datefin);var_dump($nbmois);var_dump($nbmois_tab[mois]);
	// $categories = explode(",", $categories);
	/*
	 * foreach($categories as $key => $value){ //seuls les articles des catégories sélectionnées seront affichés $infosCategorie = $value; $occurrences=$DB_site->query("SELECT lf.artcode, lf.libelle, lf.lignefactureid, f.datefacture, factureid FROM facture f INNER JOIN lignefacture lf USING (factureid) LEFT OUTER JOIN article a USING (artid) WHERE f.deleted != '1' AND f.datefacture >= '$datedebut2' AND f.datefacture <='$datefin2' AND lf.catid = '$value' GROUP BY lf.artcode ORDER BY lf.artcode ASC "); while($occurrence = $DB_site->fetch_array($occurrences)){ //var_dump($occurrence[artcode]); if ($value != ""){ //$TemplateVentes_par_articleAfficherColonnesBit = ""; while($j<$nbmois){ if($i==13){$i=1;} //$tabmois[$i]=substr($tabmois[$i],0,4); if($tabmois[$i] == 'Janvier' && $cpt_tmp != 0){ //si le mois est janvier, incrémentation de l'année $anneeint++; } $cpt_tmp = 0; $cpt_tmp++ ; eval(charge_template($langue,$referencepage,"EnteteBit"));//$tabmois[$i] //fputs($fichier, $tabmois[$i].";"); $moisCommandeStep1 = explode('-', $occurrence[datefacture]); $moisCommande = substr($moisCommandeStep1[1], -2); if ($i < 10){ $i = "0".$i; } $afficheArticle = ""; if ($moisCommande == $i){ $infosAffichageArticle = $DB_site->query_first("SELECT lf.qte, lf.prix FROM lignefacture lf INNER JOIN facture f ON (lf.factureid) WHERE artcode = '$occurrence[artcode]' AND lf.factureid = '$occurrence[factureid]' AND lignefactureid = '$occurrence[lignefactureid]'"); $afficheArticle = $infosAffichageArticle[qte]." x ".$infosAffichageArticle[prix]; } $articlesVendus = $DB_site->query_first("SELECT SUM(lf.prix*lf.qte) FROM lignefacture lf INNER JOIN facture f WHERE lf.artcode = '$occurrence[artcode]' && f.datefacture >= '$datedebut2' && f.datefacture <='$datefin2'"); $i++; $j++; eval(charge_template($langue,$referencepage,"AfficherColonnesBit")); } } eval(charge_template($langue,$referencepage,"AfficherBit")); } }
	 */
	
	// fputs($fichier, "Quantité totale;$multilangue[total]\n");
	
	if ($datedebut2 and $datefin2){
		
		$checked1 = "";
		
		$periode3 = explode('-', $datedebut2);
		$periode1 = $periode3 [1];
		$periode4 = explode('-', $datefin2);
		$periode2 = $periode4 [1];
		$tabmois = array (
				"",
				"Janvier",
				"Février",
				"Mars",
				"Avril",
				"Mai",
				"Juin",
				"Juillet",
				"Août",
				"Septembre",
				"Octobre",
				"Novembre",
				"Décembre" 
		);
		$fichier = fopen("export/journal_ventes_articles.csv", "w+");
		$en_tete = "$multilangue[designation];";
		$en_tete = ";;Du $datedebut Au $datefin;;;\nArticle;";
		fputs($fichier, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));
		fputs($fichier, $en_tete);
		
		$dateCalcul = explode("-", $datefin2);		
		$nbmois_tab = NbJour($datedebut, $datefin);

		if ($nbmois_tab [ans] > 0){
			$nbmois_tab [mois] = $nbmois_tab [ans] * 12 + $nbmois_tab[mois];
		}
		if ($nbmois_tab [jours] > 0){
			$nbmois = $nbmois_tab [mois] + 1;
		}else {
			$nbmois = $nbmois_tab [mois];
		}
		
		$j = 0;
		$i = intval($periode1);
		
		$annee = explode("/", $datedebut);
		$anneeint = 0 + $annee [2];
		
		while($j < $nbmois){
			if ($i == 13){
				$i = 1;
			}
			// echo "<td align=\"center\">$tabmois[$i]</td>";
			if ($tabmois [$i] == 'Janvier' && $cpt_tmp != 0){ // si le mois est janvier, incrémentation de l'année
				$anneeint ++;
			}
			$cpt_tmp = 0;
			$cpt_tmp ++;
			eval(charge_template($langue, $referencepage, "EnteteBit")); // $tabmois[$i]
			fputs($fichier, $tabmois [$i].";");
			
			$i ++;
			$j ++;
		}
		fputs($fichier, "Quantité totale;$multilangue[total]\n");
		if ($searchAll != 1){
			$factures = $DB_site->query("SELECT  lf.artcode, lf.libelle
											FROM  facture f INNER JOIN lignefacture lf USING (factureid)
											LEFT OUTER JOIN article a USING (artid)
											WHERE f.deleted != '1'
											AND f.datefacture >= '$datedebut2' 
											AND f.datefacture <='$datefin2' 
											$where
											AND  etatid IN ($etatid)
											GROUP BY lf.artcode ORDER BY lf.artcode ASC ");
		} else {
			$factures = $DB_site->query("SELECT  lf.artcode, lf.libelle
									FROM  facture f INNER JOIN lignefacture lf USING (factureid)
									LEFT OUTER JOIN article a USING (artid)
									WHERE f.deleted != '1'
									AND f.datefacture >= '$datedebut2'
									AND f.datefacture <='$datefin2'
									AND  etatid IN ($etatid)
									GROUP BY lf.artcode ORDER BY lf.artcode ASC ");
		}
		/*
		 * echo "SELECT lf.artcode, lf.libelle FROM facture f INNER JOIN lignefacture lf USING (factureid) LEFT OUTER JOIN article a USING (artid) WHERE f.deleted != '1' AND f.datefacture >= '$datedebut2' AND f.datefacture <='$datefin2' $where AND etatid IN ($etatid) GROUP BY lf.artcode ORDER BY lf.artcode ASC ";
		 */
		$total_all_facture_ht = 0;
		$total_all_facture_tva = 0;
		$total_all_facture_ttc = 0;
		
		while($facture = $DB_site->fetch_array($factures)){
			$rowalt = "td_users".getrowbg ();
			// echo "misérance";
			// echo "<tr><td align=\"center\" class=\"$rowalt\">$facture[artcode]<br> $multilangue[valeur]</td>";
			$facture [ca] = 0;
			// fputs($fichier, $facture[artcode].";".$facture[libelle].";");
			$i = intval($periode1);
			$j = 0;
			$m = 0;
			$o = 0;
			$an = explode('/', $datedebut);
			$an = $an [2];
			
			$TemplateVentes_par_articleAfficherColonnesBit="";
			
			while($j < $nbmois){
				
				if ($i == 13){
					$i = 1;
					$an += 1;
				}
				
				$ma = ($i < 10 ? '0' : '').$i;
				if ($searchAll != 1){
					$arts = $DB_site->query("SELECT SUM(lf.prix*lf.qte) as ca, lf.prix as prix, SUM(qte) as qte, lf.tva, lf.prixbrut,a.artid
											FROM facture f INNER JOIN lignefacture lf USING (factureid)
											LEFT OUTER JOIN article a USING (artid)
											WHERE lf.artcode = '$facture[artcode]'
											AND f.deleted != '1'
											AND f.datefacture>='$datedebut2' 
											AND f.datefacture<='$datefin2'
											AND f.datefacture LIKE '$an-$ma%'
											AND  etatid IN ($etatid)
											$where 
											GROUP BY lf.prix");
				} else {
					$arts = $DB_site->query("SELECT SUM(lf.prix*lf.qte) as ca, lf.prix as prix, SUM(qte) as qte, lf.tva, lf.prixbrut,a.artid
											FROM facture f INNER JOIN lignefacture lf USING (factureid)
											LEFT OUTER JOIN article a USING (artid)
											WHERE lf.artcode = '$facture[artcode]'
											AND f.deleted != '1'
											AND f.datefacture>='$datedebut2'
											AND f.datefacture<='$datefin2'
											AND f.datefacture LIKE '$an-$ma%'
											AND  etatid IN ($etatid)
											GROUP BY lf.prix");
					
					$checked2 = "checked=\"checked\"";
				}
				
				/*
				 * echo "SELECT SUM(lf.prix*lf.qte) as ca, lf.prix as prix, SUM(qte) as qte, lf.tva, lf.prixbrut,a.artid FROM facture f INNER JOIN lignefacture lf USING (factureid) LEFT OUTER JOIN article a USING (artid) WHERE lf.artcode = '$facture[artcode]' AND f.deleted != '1' AND f.datefacture>='$datedebut2' AND f.datefacture<='$datefin2' AND f.datefacture LIKE '%$an-$ma-%' AND etatid IN ($etatid) $where GROUP BY lf.prix";
				 */
				
				$passemois = 0;
				$ca2 = 0;
				$tva = "";
				$ca3 = 0;
				$qte = 0;
				$net = "";
				$affichage_ligne = "";
				while($art = $DB_site->fetch_array($arts)){
					
					$total_all_facture_ttc += $art [ca];
					$caht = $art [ca] * (1 - $art [tva] / 100);
					$total_all_facture_ht += $caht;
					$facture [ca] += $art [ca];
					$tva = formaterPrix($art [ca] - $caht);
					$caht = formaterPrix($caht);
					$brut = formaterPrix($art [prixbrut]);
					$net = formaterPrix($art [prix]);
					$ca = formaterPrix($art [ca]);
					$qte += $art [qte];
					$ca2 += $art [ca];
					$ca3 = formaterPrix($ca2);
					$facture [qte] += $art [qte];
					$passemois = 1;
					$facture [ca] = number_format($facture [ca], 2, ",", " ");
					// Contenu case
					// echo "$art[qte] x $net<br>";
					
					if ($art[qte] != 0){
						$affichage_ligne .= "$art[qte] x $net €<br>";
					}
					
					
					$m ++;
					if ($artid_sauvegarde != $art[artid]){
						$list_art .= $art[artid].",";
					}
					$artid_sauvegarde = $art[artid];
				}
				
				if ($affichage_ligne == ""){
					$affichage_ligne = "-";
				}
				//var_dump($affichage_ligne);
				$catlist = implode(",", $categories);
				//var_dump($catlist[0]);
				if ($vente != 1){
					eval(charge_template($langue, $referencepage, "AfficherColonnesBit"));
				}elseif($vente == 1 and $catlist[0]!= ""){
					eval(charge_template($langue, $referencepage, "AfficherColonnesBit"));					
				}
				
				if($o == 0){
					fputs($fichier, $facture[artcode].";".$qte.";");
					$o = 1;
				}else{
					fputs($fichier, $qte.";");
				}
				
				$i ++;
				$j ++;
			}
			// Total ligne
			if ($vente != 1){
				eval(charge_template($langue, $referencepage, "AfficherBit"));
			} elseif ($vente == 1 and $catlist [0] != ""){
				eval(charge_template($langue, $referencepage, "AfficherBit"));
			}
			
			fputs ($fichier, $facture[qte].";".$facture[ca]."€\n");
			// fputs($fichier, $facture[qtett].";".number_format($facture[lfprixbrut], 2, ",", " ")."€;".number_format($facture[lfprix], 2, ",", " ")."€;".number_format($facture[ca], 2, ",", " ")."€;\n");
		}
		
		$i = intval($periode1);
		
		$total_all_facture_tva = $total_all_facture_ttc - $total_all_facture_ht;
		
		$i = intval($periode1);
	}
	// ****************************** Inclure les articles jamais vendus
	
	if ($vente == 1){
		$checked1 = "checked=\"checked\"";
		$catlist = implode(",", $categories);
		$affichage_ligne = "";
		$list_art = substr($list_art, 0, - 1);
		$list_art = str_replace(",,", ",", $list_art);
		$listeAV="";
		if($categories[0] == ""){
			$tests = $DB_site->query("SELECT * FROM lignefacture");
			while($test = $DB_site->fetch_array($tests)){
				$listeAV .= $test [artid].',';
			}
			if($listeAV != ""){
				$listeAV = substr($listeAV, 0, - 1);
				$arts_nonvendus = $DB_site->query("SELECT * FROM article WHERE artid NOT IN ($listeAV)");
			}else{
				$arts_nonvendus = $DB_site->query ("SELECT * FROM article");
			}			
		}elseif($list_art == ""){
			$arts_nonvendus = $DB_site->query("SELECT * FROM article
														WHERE catid IN ($catlist)");
		}else{
			$tests = $DB_site->query("SELECT * FROM lignefacture WHERE catid in ($catlist)");
			while($test = $DB_site->fetch_array($tests)){
				$listeAV .= $test [artid].',';
			}
			$listeAV = substr($listeAV, 0, - 1);
			$arts_nonvendus = $DB_site->query("SELECT * FROM article
												WHERE  artid  NOT IN ($listeAV)
												AND catid IN ($catlist)");
		}
		
		while($arts_nonvendu = $DB_site->fetch_array($arts_nonvendus)){
			
			$j = 0;
			$m = 0;
			$o = 0;
			$rowalt = "td_users".getrowbg ();
			// <td align=\"center\" class=\"$rowalt\">$arts_nonvendu[artcode]<br> $multilangue[valeur]</td>";
			$TemplateVentes_par_articleAfficherColonnesBit = "";
			while($j < $nbmois){
				
				if ($i == 13){
					$i = 1;
					$an += 1;
				}
				
				if ($o == 0){
					fputs($fichier, $arts_nonvendu [artcode].";0;");
					$o = 1;
				} else {
					fputs($fichier, "0;");
				}
				
				$i ++;
				$j ++;
				
				eval(charge_template($langue, $referencepage, "AfficherColonnesBit"));
			}
			eval(charge_template($langue, $referencepage, "AfficherBit"));
			
			fputs($fichier, "0;".formaterPrix(0)."€\n");
		}
	}
}

fclose($fichier);

// GESTION AFFICHAGE TREEVIEW *******************************************************
$tabCatid = explode(',', $_POST [categories]);
$niveaux0 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '0' && siteid= '1' ORDER BY position");
$TemplateVentes_par_articleModificationNiveau0 = "";

while($niveau0 = $DB_site->fetch_array($niveaux0)){
	$checked = (in_array($niveau0 [catid], $tabCatid) ? '{"checked" : true}' : "");
	$TemplateVentes_par_articleModificationNiveau1 = "";
	
	$niveaux1 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau0[catid]' && siteid= '1' ORDER BY position");
	while($niveau1 = $DB_site->fetch_array($niveaux1)){
		$checked = (in_array($niveau1 [catid], $tabCatid) ? '{"checked" : true}' : "");
		$TemplateVentes_par_articleModificationNiveau2 = "";
		
		$niveaux2 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau1[catid]' && siteid= '1' ORDER BY position");
		while($niveau2 = $DB_site->fetch_array($niveaux2)){
			$checked = (in_array($niveau2 [catid], $tabCatid) ? '"checked" : true' : "");
			$TemplateVentes_par_articleModificationNiveau3 = "";
			
			$niveaux3 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau2[catid]' && siteid= '1' ORDER BY position");
			while($niveau3 = $DB_site->fetch_array($niveaux3)){
				$checked = (in_array($niveau3 [catid], $tabCatid) ? '"checked" : true' : "");
				$TemplateVentes_par_articleModificationNiveau4 = "";
				
				$niveaux4 = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$niveau3[catid]' && siteid= '1' ORDER BY position");
				while($niveau4 = $DB_site->fetch_array($niveaux4)){
					$checked = (in_array($niveau4 [catid], $tabCatid) ? '"checked" : true' : "");
					eval(charge_template($langue, $referencepage, "ModificationNiveau4"));
				}
				
				eval(charge_template($langue, $referencepage, "ModificationNiveau3"));
			}
			
			eval(charge_template($langue, $referencepage, "ModificationNiveau2"));
		}
		
		eval(charge_template($langue, $referencepage, "ModificationNiveau1"));
	}
	
	eval(charge_template($langue, $referencepage, "ModificationNiveau0"));
}

$total_all_facture_tva = $total_all_facture_ttc - $total_all_facture_ht;
$afficheTotalFactureHT = formaterPrix($total_all_facture_ht);
$afficheTotalFactureTVA = formaterPrix($total_all_facture_tva);
$afficheTotalFactureTTC = formaterPrix($total_all_facture_ttc);

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex = "Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close ();
flush ();

?>