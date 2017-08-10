<?php
include "includes/header.php";

$scriptcourant = "produits_en_avant.php";

if(isset($action) && $action == "org"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM $table WHERE siteid = '$idSite'");
		if($ordre != ""){
			$ordre = explode("|", $ordre);
			for($i=0;$i<sizeof($ordre);$i++){
				$position = $i+1;
				$DB_site->query("INSERT INTO $table (artid, position, siteid) VALUES ('$ordre[$i]','$position', '$idSite')");
			}
		}
	}else{
		header("HTTP/1.1 503 $multilangue[action_page_refuse]");
		exit;
	}
}

if (isset($action) and $action == "jstreearticles"){
	header('Content-Type: application/json');
	$records = array();
	$records[] = array(
			"id" => "cat0",
			"parent" => "#",
			"text" => $titleFR,
			"state" => array("opened" => true)
	);
	
	
	$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '1' ORDER BY position");
	while ($categorie = $DB_site->fetch_array($categories)){
		$records[] = array(
				"id" =>"cat$categorie[catid]",
				"parent" =>"cat$categorie[parentid]",
				"text" => $categorie[libelle]
		);
			
		$articles = $DB_site->query("SELECT * FROM article AS a
				INNER JOIN article_site AS asite USING(artid)
				WHERE siteid = '1'
				AND catid = '$categorie[catid]'
				ORDER BY asite.libelle");
			
		while ($article = $DB_site->fetch_array($articles)){
			$records[] = array(
					"id" => "art$article[artid]",
					"parent" => "cat$categorie[catid]",
					"text" => $article[libelle],
					"icon" => "fa fa-inbox"
			);
		}

	}
	
	$articles_niveau0 = $DB_site->query("SELECT * FROM article AS a INNER JOIN article_site AS asite USING(artid) WHERE asite.siteid = '1'  AND a.catid = '0' ORDER BY asite.libelle");
	while($article_niveau0 = $DB_site->fetch_array($articles_niveau0)){
		$records[] = array(
				"id" => "art$article_niveau0[artid]",
				"parent" => "cat0",
				"text" => $article_niveau0[libelle],
				"icon" => "fa fa-inbox"
		);
	}
	echo json_encode($records);
}

if(!isset($action) || $action = ""){
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
			$orderby = "sitelibelle";
			break;
		case "2" :
			$orderby = "articlelibelle";
			break;
		case "3" :
			$orderby = "categlibelle";
			break;
		case "4" :
			$orderby = "datedebut";
			break;
		case "5" :
			$orderby = "datefin";
			break;
		case "6" :
			$orderby = "pctpromo";
			break;
		case "8" :
			$orderby = "prix";
			break;
		default:
			$orderby = "datefin";
			break;
	}
	 
	$sensorder = $order[0][dir];
	
	$search[value] = securiserSql($search[value]);
	$where = "";
	if($search[value]!=""){
		$where = "AND (site.libelle LIKE '%$search[value]%'";
		$where .= " OR a.artcode LIKE '%$search[value]%'";
		$where .= " OR asite.libelle LIKE '%$search[value]%'";
		$where .= " OR cs.libelle LIKE '%$search[value]%'";
		$where .= " OR aps.pctpromo LIKE '%$search[value]%'";
		$where .= " OR asite.prix LIKE '%$search[value]%')";
	}
	
	$promos = $DB_site->query("SELECT aps.siteid, aps.promoid, site.libelle AS sitelibelle, a.artid, asite.libelle AS articlelibelle, a.artcode, cs.libelle AS categlibelle, aps.datedebut, aps.datefin, aps.pctpromo, asite.prix
								FROM article_promo_site AS aps
								INNER JOIN article AS a ON a.artid = aps.artid
								INNER JOIN article_site AS asite ON asite.artid = a.artid AND asite.siteid = aps.siteid
								LEFT JOIN categorie_site AS cs ON cs.catid = a.catid AND cs.siteid = aps.siteid
								INNER JOIN site ON site.siteid = aps.siteid
								WHERE aps.datefin > '".time()."' $where ORDER BY $orderby $sensorder");
	
	$promocount = $DB_site->num_rows($promos);
	 
	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $cadeaucount);
	 
	if ($promocount > 0) {
		$promos = $DB_site->query("SELECT aps.siteid, aps.promoid, site.libelle AS sitelibelle, a.artid, a.image, asite.libelle AS articlelibelle, a.artcode, cs.libelle AS categlibelle, aps.datedebut, aps.datefin, aps.pctpromo, asite.prix
								FROM article_promo_site AS aps
								INNER JOIN article AS a ON a.artid = aps.artid
								INNER JOIN article_site AS asite ON asite.artid = a.artid AND asite.siteid = aps.siteid
								LEFT JOIN categorie_site AS cs ON cs.catid = a.catid AND cs.siteid = aps.siteid
								INNER JOIN site ON site.siteid = aps.siteid
								WHERE aps.datefin > '".time()."' $where ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
		
		while ($promo = $DB_site->fetch_array($promos)){
			$datedebut = ($promo[datedebut] == "0" ? $multilangue[aucune] : date("d/m/Y H:i:s", $promo[datedebut]));
			$datefin = ($promo[datefin] == "0" ? $multilangue[aucune] : date("d/m/Y H:i:s", $promo[datefin]));
			$prix_promo = formaterPrix((1-($promo[pctpromo]/100))*$promo[prix]);
			$action = '<a href="#myModal' . $promo[promoid] . '" id="btn_suppr' . $promo[promoid]. '" data-original-title="' . $multilangue[supprimer] . '" data-placement="top" data-toggle="modal" role="button" class="btn tooltips">';
			$action .= '<i class="fa fa-trash-o fs-18 font-red"></i>';
			$action .= '</a>';
			$action .= '<div aria-hidden="true" aria-labelledby="myModalLabe' . $promo[promoid] . '" role="dialog" tabindex="-1" class="modal fade" id="myModal' . $promo[promoid] . '" style="display: none;">';
			$action .= '<div class="modal-dialog">';
			$action .= '<div class="modal-content">';
			$action .= '<div class="modal-header">';
			$action .= '<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>';
			$action .= '<h4 class="modal-title">'.$multilangue[suppression_promotion].' ' . formaterPrix($promo[pctpromo]) . '% '.$multilangue[sur].' '.$promo[articlelibelle].' ('.$promo[artcode].') ?</h4>';
			$action .= '</div>';
			$action .= '<div class="modal-body">';
			$action .= $multilangue[suppression_promotion_infos];
			$action .= '</div>';
			$action .= '<div class="modal-footer">';
			$action .= '<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>';
			$action .= '<a href="produits_en_avant.php?action=supprimer&promoid=' . $promo[promoid] . '" class="btn blue">'.$multilangue[oui_supprimer].'</a>';
			$action .= '</div>';
			$action .= '</div>';
			$action .= '</div>';
			$action .= '</div>';
			
			if($promo[image] != ""){
				$image_article="<img src='http://$host/br-a-$promo[artid].$promo[image]' style='max-width:140px;max-height:100px;'>";
			}else{
				$image_article="";
			}
			
			
			$records["aaData"][] = array(
					"site" => $promo[sitelibelle],					
					"photo" => $image_article,
					"article" => $promo[articlelibelle]." (".$promo[artcode].")",
					"rayon" => $promo[categlibelle],
					"date_debut" => $datedebut,
					"date_fin" => $datefin,
					"pourcentage" => formaterPrix($promo[pctpromo])."%",
					"prix_promo" => $prix_promo." ".$tabsites["devise_symbole".$promo[siteid]],
					"prix_normal" => formaterPrix($promo[prix])." ".$tabsites["devise_symbole".$promo[siteid]],
					"action" => $action
			);
		}
	}

	$records["iTotalRecords"] = $promocount;
	$records["iTotalDisplayRecords"] = $promocount;

	echo json_encode($records);
}

?>