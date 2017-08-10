<?php
include "includes/header.php";

$scriptcourant = "inventaire.php";

if(isset($action) && $action == "maj"){
	if($admin_droit[$scriptcourant][ecriture]){
		if($tablestock=="s"){
			if($champ == "total"){
				$en_stock = $DB_site->query_first("SELECT total, prixachatmoyen, prixachat FROM stocks WHERE stockid = '$id'");
				$nouveau_stock = $valeur - $en_stock[total];
				if($nouveau_stock > 0){
					$nouveau_prix_moyen =( ($en_stock[total]*$en_stock[prixachatmoyen]) + ($nouveau_stock * $en_stock[prixachat]) )/$valeur;
					$DB_site->query("UPDATE stocks SET prixachatmoyen = '$nouveau_prix_moyen' WHERE stockid='$id'");
					echo "$tablestock-prixachatmoyen-$id"."_".$nouveau_prix_moyen;
				}
				$DB_site->query("UPDATE stocks SET $champ = '$valeur' WHERE stockid = '$id'");	
			}elseif($champ=="differenceprix" || $champ=="differenceprixproht"){
				$DB_site->query("UPDATE stocks_site SET $champ = '$valeur' WHERE stockid = '$id' AND siteid = '$site_id'");
			}else{
				$DB_site->query("UPDATE stocks SET $champ = '$valeur' WHERE stockid = '$id'");
			}
		}else{
			if($champ=="nombre"){
				$ancien_stock = $DB_site->query_first("SELECT nombre FROM stock WHERE artid = '$id'");
				$nouveau_stock = $valeur - $ancien_stock[nombre];
				if($nouveau_stock > 0){
					$prix = $DB_site->query_first("SELECT prixachat, prixachatmoyen FROM article WHERE artid = '$id'");
					$nouveau_prix_moyen = (($ancien_stock[nombre]*$prix[prixachatmoyen])+($nouveau_stock*$prix[prixachat]))/$valeur;
					$DB_site->query("UPDATE article SET prixachatmoyen = '$nouveau_prix_moyen' WHERE artid = '$id'");
					echo "$tablestock-prixachatmoyen-$id_$nouveau_prix_moyen";
				}
				$DB_site->query("UPDATE stock SET $champ = '$valeur' WHERE artid = '$id'");
			}elseif($champ=="artcode" || $champ=="code_EAN" || $champ=="prixachat"){
				$DB_site->query("UPDATE article SET $champ='$valeur' WHERE artid = '$id'");
			}elseif($champ=="prix" || $champ=="delai" || $champ=="prixpro"){
				$DB_site->query("UPDATE article_site SET $champ='$valeur' WHERE artid = '$id' AND siteid='$site_id'");
			}else{
				$DB_site->query("UPDATE stock SET $champ='$valeur' WHERE artid = '$id'");
			}
		}
	}else{
		header("HTTP/1.1 503 $multilangue[action_page_refuse]");
		exit;
	}
}


if (isset($action) and $action == "jstreecateg"){
		header('Content-Type: application/json');
		$records = array();
		$records[] = array(
				"id" => 0,
				"parent" => "#",
				"text" => $titleFR,
				"state" => array("opened" => true)
		);
		$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '1' ORDER BY position");
		while ($categorie = $DB_site->fetch_array($categories)){			
			$records[] = array(
					"id" => $categorie[catid],
					"parent" => $categorie[parentid],
					"text" => $categorie[libelle]
			);
		}
		echo json_encode($records);
	}
	
if (!isset($action) or $action == ""){
	$iDisplayLength = intval($length);
	$iDisplayStart = intval($start);

	$records = array();
	$records["aaData"] = array();

	if(!isset($iDisplayStart))
		$iDisplayStart = 0;
	if(!isset($iDisplayLength))
		$iDisplayLength = 10;

	switch($order[0][column]){
		case "0" :
			$orderby = "libelle";
			break;
		case "1" :
			$orderby = "reference";
			break;
		case "2" :
			$orderby = "code_EAN";
			break;
		case "3" :
			$orderby = "prix";
			break;
		case "4" :
			$orderby = "prixpro";
			break;
		case "5" :
			$orderby = "total";
			break;
		case "6" :
			$orderby = "seuil";
			break;
		case "7" :
			$orderby = "delaiappro";
			break;
		case "8" :
			$orderby = "delai";
			break;
		case "9" :
			$orderby = "prixachat";
			break;
		case "10" :
			$orderby = "prixachatmoyen";
			break;
		case "11" :
			$orderby = "differenceprix";
			break;
		case "12" :
			$orderby = "differenceprixproht";
			break;
		case "13" :
			$orderby = "zonestockage";
			break;
		default:
			$orderby = "libelle";
			break;
	}

	$sensorder = $order[0][dir];
	$search[value] = securiserSql($search[value]);
	
	
	if($catids_select != ""){
		$and_catids = "AND aa.catid IN ($catids_select) ";
		$and_catids2 = "AND a.catid IN ($catids_select) ";
	}else{
		$and_catids="";
		$and_catids2="";
	}
	
	if($fournisseursid_select != ""){
		$and_fournisseursid = "AND aa.fournisseurid IN ($fournisseursid_select)";
		$and_fournisseursid2 = "AND a.fournisseurid IN ($fournisseursid_select)";
	}else{
		$and_fournisseursid="";
		$and_fournisseursid2="";
	}

	if($stock_select != ""){
		switch ($stock_select){
				case "0":
					$and_stock .= "AND st.total = '0'";
					$and_stock2 .= "AND s.nombre = '0'";
					break;
				case "1":
					$and_stock .= "AND st.total < st.seuil_alerte";
					$and_stock2 .= "AND s.nombre < s.seuil";
					break;
				case "2":
					$and_stock .= "AND st.total > '0'";
					$and_stock2 .= "AND s.nombre > '0'";
					break;
				case "0,1":
					$and_stock .= "AND (st.total = '0' OR st.total < st.seuil_alerte)";
					$and_stock2 .= "AND (s.nombre > '0' OR s.nombre < s.seuil)";
					break;
				case "1,2":
					$and_stock .= "AND (st.total > '0' OR st.total < st.seuil_alerte)";
					$and_stock2 .= "AND (s.nombre > '0' OR s.nombre < s.seuil)";
					break;
				default :
					$and_stock .= "";
					$and_stock2 .= "";
					break;
			}
	}else{
		$and_stock="";
		$and_stock2="";
	}
	
	$and_search = "";
	$and_search2 = "";
	if($search[value]!=""){
		$and_search .= " AND (libelle LIKE '%$search[value]%'";
		$and_search .= " OR reference LIKE '%$search[value]%'";
		$and_search .= " OR st.code_EAN LIKE '%$search[value]%'";
		$and_search .= " OR prix LIKE '%$search[value]%'";
		$and_search .= " OR prixpro LIKE '%$search[value]%'";
		$and_search .= " OR total LIKE '%$search[value]%'";
		$and_search .= " OR st.seuil_alerte LIKE '%$search[value]%'";
		$and_search .= " OR st.delai_appro LIKE '%$search[value]%'";
		$and_search .= " OR st.delai_livraison LIKE '%$search[value]%'";
		$and_search .= " OR st.prixachat LIKE '%$search[value]%'";
		$and_search .= " OR st.prixachatmoyen LIKE '%$search[value]%'";
		$and_search .= " OR differenceprix LIKE '%$search[value]%'";
		$and_search .= " OR differenceprixproht LIKE '%$search[value]%'";
		$and_search .= " OR zonestockage LIKE '%$search[value]%')";
		
		$and_search2 .= " AND (libelle LIKE '%$search[value]%'";
		$and_search2 .= " OR a.artcode LIKE '%$search[value]%'";
		$and_search2 .= " OR a.code_EAN LIKE '%$search[value]%'";
		$and_search2 .= " OR prix LIKE '%$search[value]%'";
		$and_search2 .= " OR prixpro LIKE '%$search[value]%'";
		$and_search2 .= " OR s.nombre LIKE '%$search[value]%'";
		$and_search2 .= " OR s.seuil LIKE '%$search[value]%'";
		$and_search2 .= " OR s.delaiappro LIKE '%$search[value]%'";
		$and_search2 .= " OR delai LIKE '%$search[value]%'";
		$and_search2 .= " OR prixachat LIKE '%$search[value]%'";
		$and_search2 .= " OR prixachatmoyen LIKE '%$search[value]%'";
		$and_search2 .= " OR zonestockage LIKE '%$search[value]%')";
	}
	
	$where = "$and_fournisseursid $and_catids $and_search $and_stock";
	$where2 = "$and_fournisseursid2 $and_catids2 $and_search2 $and_stock2 ";
	
	/*$articles = $DB_site->query("SELECT article.artid, article.artcode, article.code_EAN AS articlecodeean, article.prixachat AS articleprixachat, article.prixachatmoyen AS articleprixachatmoyen,
									article_site.prix, article_site.prixpro, article_site.delai, article_site.libelle,
									stocks.stockid, stocks.total, stocks.reference, stocks.code_EAN, stocks.seuil_alerte, stocks.delai_appro, stocks.prixachat, stocks.prixachatmoyen, stocks.delai_livraison, stocks.zonestockage,
									stocks_site.differenceprix, stocks_site.differenceprixproht
									FROM article 
									INNER JOIN article_site ON article_site.artid = article.artid AND article_site.siteid='1'
									LEFT JOIN stocks ON stocks.artid = article.artid
									LEFT JOIN stocks_site ON stocks_site.stockid = stocks.stockid AND stocks_site.siteid='1' 
									WHERE $where 
									ORDER BY $orderby $sensorder");*/
	
	
	$articles = $DB_site->query("SELECT aa.artid, st.delai_livraison AS delai, st.code_EAN AS code_EAN, st.seuil_alerte AS seuil,
									st.delai_appro AS delaiappro, st.prixachat AS prixachat, st.prixachatmoyen AS prixachatmoyen, ars.prix AS prix, ars.prixpro, ars.libelle,
									st.total, st.reference, sts.differenceprix, sts.differenceprixproht, st.stockid						
									FROM article AS aa
									INNER JOIN article_site AS ars ON ars.artid = aa.artid AND ars.siteid='$siteid_select'
									LEFT JOIN stocks AS st ON st.artid = aa.artid
									LEFT JOIN stocks_site AS sts ON sts.stockid = st.stockid AND sts.siteid='$siteid_select'
									WHERE aa.artid IN (SELECT artid FROM article_caractval) AND aa.stock_illimite != 1 $where
									
									UNION
			
									SELECT a.artid, delai, a.code_EAN, s.seuil, s.delaiappro, a.prixachat, a.prixachatmoyen, asite.prix,
									asite.prixpro, asite.libelle, s.nombre AS total, a.artcode AS reference, null AS differenceprix,
									null AS differenceprixproht, null AS stockid									
									FROM article AS a
									INNER JOIN article_site AS asite ON asite.artid = a.artid AND asite.siteid='$siteid_select'
									LEFT JOIN stock AS s ON s.artid = a.artid
									WHERE a.artid NOT IN (SELECT artid FROM article_caractval) AND a.stock_illimite != 1 $where2
			
									ORDER BY $orderby $sensorder");

	$articlecount = $DB_site->num_rows($articles);
	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $articlecount);

	if ($articlecount > 0) {
		/*$articles = $DB_site->query("SELECT article.artid, article.artcode, article.code_EAN AS articlecodeean, article.prixachat AS articleprixachat, article.prixachatmoyen AS articleprixachatmoyen,
										article_site.prix, article_site.prixpro, article_site.delai, article_site.libelle,
										stocks.stockid, stocks.total, stocks.reference, stocks.code_EAN, stocks.seuil_alerte, stocks.delai_appro, stocks.prixachat, stocks.prixachatmoyen, stocks.delai_livraison, stocks.zonestockage,
										stocks_site.differenceprix, stocks_site.differenceprixproht
										FROM article 
										INNER JOIN article_site ON article_site.artid = article.artid AND article_site.siteid='1'
										LEFT JOIN stocks ON stocks.artid = article_site.artid 
										LEFT JOIN stocks_site ON stocks_site.stockid=stocks.stockid AND stocks_site.siteid='1' 
										WHERE $where 
										ORDER BY $orderby $sensorder 
										LIMIT $limitlower, $perpage");*/
		
		$articles = $DB_site->query("SELECT aa.artid, st.delai_livraison AS delai, st.code_EAN, st.seuil_alerte AS seuil,
										st.delai_appro AS delaiappro, st.prixachat, st.prixachatmoyen, ars.prix, ars.prixpro, ars.libelle,
										st.total, st.reference, sts.differenceprix, sts.differenceprixproht, st.stockid
										FROM article AS aa
										INNER JOIN article_site AS ars ON ars.artid = aa.artid AND ars.siteid='$siteid_select'
										LEFT JOIN stocks AS st ON st.artid = aa.artid
										LEFT JOIN stocks_site AS sts ON sts.stockid = st.stockid AND sts.siteid='$siteid_select'
										WHERE aa.artid IN (SELECT artid FROM article_caractval) AND aa.stock_illimite != 1 $where
											
										UNION
											
										SELECT a.artid, delai, a.code_EAN, s.seuil, s.delaiappro, a.prixachat, a.prixachatmoyen, asite.prix,
										asite.prixpro, asite.libelle, s.nombre AS total, a.artcode AS reference, null AS differenceprix,
										null AS differenceprixproht, null AS stockid
										FROM article AS a
										INNER JOIN article_site AS asite ON asite.artid = a.artid AND asite.siteid='$siteid_select'
										LEFT JOIN stock AS s ON s.artid = a.artid
										WHERE a.artid NOT IN (SELECT artid FROM article_caractval) AND a.stock_illimite != 1 $where2
											
										ORDER BY $orderby $sensorder
										LIMIT $limitlower, $perpage");
		

		while ($article = $DB_site->fetch_array($articles)){
			$caract = $DB_site->query("SELECT * FROM article_caractval WHERE artid='$article[artid]'");
			if($DB_site->num_rows($caract)>0){
				$caractsarticle = $DB_site->query("SELECT * FROM stocks_caractval 
													INNER JOIN caracteristiquevaleur_site USING (caractvalid)
													WHERE siteid='1' AND stockid = $article[stockid]");
				$caractlibelle = "<br>(";
				while($caract = $DB_site->fetch_array($caractsarticle)){
						$caractlibelle .= $caract[libelle]." - ";
				}
				if($caractlibelle == "<br>("){
					$caractlibelle = "";
				}else{
					$caractlibelle=substr($caractlibelle,0,-3).")";
				}
				$color = "";
				if($stock_select != ""){
					if($article[total] > 0)
						$color = "font-green";
					if($article[total] == 0)
						$color = "font-red";
					if($article[total] < $article[seuil])
						$color = "font-yellow";
				}
				$records["aaData"][] = array(
						"libelle" => "<span class='$color'>$article[libelle]</span><i>$caractlibelle</i>",
						"reference" => "<input type='text' class='form-control input-small input ta-center' name='s-reference-$article[stockid]' value='$article[reference]'>",
						"code_EAN" => "<input type='text' class='form-control input-small input ta-center' name='s-code_EAN-$article[stockid]' value='$article[code_EAN]'>",
						"prix_vente" => formaterPrix($article[prix])." ".$tabsites[$siteid_select][devise_complete],
						"prix_pro" => formaterPrix($article[prixpro])." ".$tabsites[$siteid_select][devise_complete],
						"stock" => "<input type='text' style=\"margin: 0 auto; width:55px !important;\" class='form-control input-xsmall input ta-center' name='s-total-$article[stockid]' value='$article[total]'>",
						"seuil" => "<input type='text' style=\"margin: 0 auto; width:55px !important;\" class='form-control input-xsmall input ta-center' name='s-seuil_alerte-$article[stockid]' value='$article[seuil]'>",
						"delai_reappro" => "<input type='text' style=\"margin: 0 auto; width:55px !important;\" class='form-control input-xsmall input ta-center' name='s-delai_appro-$article[stockid]' value='$article[delaiappro]'>",
						"delai_livraison" => "<input type='text' style=\"margin: 0 auto; width:55px !important;\" class='form-control input-xsmall input ta-center' name='s-delai_livraison-$article[stockid]' value='$article[delai]'>",
						"prix_achat" => "<input type='text' style=\"display: inline; margin: 0 auto; width:70px !important;\" class='form-control input-xsmall input ta-center' name='s-prixachat-$article[stockid]' value='$article[prixachat]'> ".$tabsites[$siteid_select][devise_complete],
						"prix_achat_moyen" => "<span  class='s-prixachatmoyen-$article[stockid]'>".formaterPrix($article[prixachatmoyen])."</span> ".$tabsites[$siteid_select][devise_complete],
						"modif_prix" => "<input type='text' style=\"display: inline; margin: 0 auto; width:70px !important;\" class='form-control input-xsmall input ta-center' name='s-differenceprix-$article[stockid]' value='$article[differenceprix]'> ".$tabsites[$siteid_select][devise_complete],
						"modif_prix_pro" => "<input type='text' style=\"display: inline; margin: 0 auto; width:70px !important;\" class='form-control input-xsmall input ta-center' name='s-differenceprixproht-$article[stockid]' value='$article[differenceprixproht]'> ".$tabsites[$siteid_select][devise_complete],
						"zone" => "<input type='text' style=\"margin: 0 auto; width:70px !important;\" class='form-control input-xsmall input ta-center' name='s-zonestockage-$article[stockid]' value='$article[zonestockage]'>"
				);
			} else {
				$color = "";
				if($stock_select != ""){
					if($article[total] > 0)
						$color = "font-green";
					if($article[total] == 0)
						$color = "font-red";
					if($article[total] < $article[seuil])
						$color = "font-yellow";
				}
				$articlestock = $DB_site->query_first("SELECT * FROM stock WHERE artid = '$article[artid]'");
				$records["aaData"][] = array(
						"libelle" => "<span class='$color'>$article[libelle]</span>",
						"reference" => "<input class='form-control input-small input ta-center' type='text' name='as-artcode-$article[artid]' value='$article[reference]'>",
						"code_EAN" => "<input type='text' class='form-control input-small input ta-center' name='as-code_EAN-$article[artid]' value='$article[code_EAN]'>",
						"prix_vente" => "<input style=\"display: inline; margin: 0 auto; width:70px !important;\" type='text' class='form-control input-xsmall input ta-center' name='as-prix-$article[artid]' value='$article[prix]'> ".$tabsites[$siteid_select][devise_complete],
						"prix_pro" => "<input style=\"display: inline; margin: 0 auto; width:70px !important;\" type='text' class='form-control input-xsmall input ta-center' name='as-prix_pro-$article[artid]' value='$article[prixpro]'> ".$tabsites[$siteid_select][devise_complete],
						"stock" => "<input type='text' style=\"margin: 0 auto; width:55px !important;\"  class='form-control input-xsmall input ta-center' name='as-nombre-$article[artid]' value='$articlestock[nombre]'>",
						"seuil" => "<input type='text' style=\"margin: 0 auto; width:55px !important;\" class='form-control input-xsmall input ta-center' name='as-seuil-$article[artid]' value='$articlestock[seuil]'>",
						"delai_reappro" => "<input type='text' style=\"margin: 0 auto; width:55px !important;\" class='form-control input-xsmall input ta-center' name='as-delaiappro-$article[artid]' value='$articlestock[delaiappro]'>",
						"delai_livraison" => "<input type='text' style=\"margin: 0 auto; width:55px !important;\" class='form-control input-xsmall input ta-center' name='as-delai-$article[artid]' value='$article[delai]'>",
						"prix_achat" => "<input type='text' style=\"display: inline; margin: 0 auto; width:70px !important;\" class='form-control input-xsmall input ta-center'  name='as-prixachat-$article[artid]' value='$article[prixachat]'> ".$tabsites[$siteid_select][devise_complete],
						"prix_achat_moyen" => "<span class='as-prixachatmoyen-$article[artid]'>".formaterPrix($article[prixachatmoyen])."</span> ".$tabsites[$siteid_select][devise_complete],
						"modif_prix" => "",
						"modif_prix_pro" => "",
						"zone" => "<input type='text' style=\"margin: 0 auto; width:70px !important;\" class='form-control input-xsmall input ta-center' name='as-zonestockage-$article[artid]' value='$article[zonestockage]'>"
				);
			}
		}
	}
	$records["iTotalRecords"] = $articlecount;
	$records["iTotalDisplayRecords"] = $articlecount;
	echo json_encode($records);
}

?>