<?php
	include "includes/header.php";
	
	if (isset($action) and $action == "googleshopping"){
		$googleshoppingattributid = ($googleshoppingattributid ? $googleshoppingattributid : 0);
		$select = '<select class="form-control col-md-4 googleshoppingattribut">';
		$select .= '<option value="' . $googleshoppingattributid . '">' . $multilangue[tous] . '</option>';
		$attributs = $DB_site->query("SELECT * FROM googleshopping_attribut WHERE parentid = '$googleshoppingattributid'");
		while ($attribut = $DB_site->fetch_array($attributs)){
			$full = 1;
			$select .= '<option value="' . $attribut[attributid] . '">' . $attribut[libelle] . '</option>';
		}
		$select .= '</select>';
		if ($full)
			echo $select;
	}
	
	if (isset($action) and $action == "nextag"){
		$nextagcatnextagid = ($nextagcatnextagid ? $nextagcatnextagid : 0);
		$select = '<select class="form-control col-md-4 nextagcatnextag">';
		$select .= '<option value="' . $nextagcatnextagid . '">' . $multilangue[tous] . '</option>';
		$attributs = $DB_site->query("SELECT * FROM nextag_attribut WHERE parentid = '$nextagcatnextagid'");
		while ($attribut = $DB_site->fetch_array($attributs)){
			$full = 1;
			$select .= '<option value="' . $attribut[attributid] . '">' . $attribut[libelle] . '</option>';
		}
		$select .= '</select>';
		if ($full)
			echo $select;
	}
	
	if (isset($action) and $action == "jstree"){
		header('Content-Type: application/json');
		$records = array();
		$records[] = array(
				"id" => 0,
				"parent" => "#",
				"text" => $title1,
				"state" => array("opened" => true)
		);
		$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '1' ORDER BY position");
		while ($categorie = $DB_site->fetch_array($categories)){
			$position = $DB_site->query_first("SELECT * FROM position WHERE artid = '$artid' AND catid = '$categorie[catid]'");
			$records[] = array(
					"id" => $categorie[catid],
					"parent" => $categorie[parentid],
					"text" => $categorie[libelle],
					"state" => array("selected" => ($position[catid] ? true : false))
			);
		}
		echo json_encode($records);
	}
	
	if (isset($action) and $action == "jstreecateg"){
		header('Content-Type: application/json');
		$records = array();
		$records[] = array(
				"id" => 0,
				"parent" => "#",
				"text" => $title1,
				"state" => array("opened" => true)
		);
		$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '1' ORDER BY position");
		while ($categorie = $DB_site->fetch_array($categories)){
			$records[] = array(
					"id" => $categorie[catid],
					"parent" => $categorie[parentid],
					"text" => $categorie[libelle]/*,
					"state" => array("selected" => ($position[catid] ? true : false))*/
			);
		}
		echo json_encode($records);
	}

	
	if(isset($action) && $action=="tablePromo"){
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
			case "1" :
				$orderby = "datedebut";
				break;
			case "2" :
				$orderby = "datefin";
				break;
			case "3" :
				$orderby = "pctpromo";
				break;
			case "4" :
				$orderby = "prix";
				break;
			default:
				$orderby = "datefin";
				break;
		}
		
		$sensorder = $order[0][dir];
		$promos = $DB_site->query("SELECT aps.siteid, site.libelle AS sitelibelle, aps.promoid, a.artid, aps.datedebut, aps.datefin, aps.pctpromo, asite.prix
								FROM article_promo_site AS aps
								INNER JOIN article AS a ON a.artid = aps.artid
								INNER JOIN article_site AS asite ON asite.artid = a.artid AND asite.siteid = aps.siteid
								INNER JOIN site ON site.siteid = aps.siteid
								WHERE aps.datefin > '".time()."' AND a.artid='$artid' ORDER BY $orderby $sensorder");
		
		$promocount = $DB_site->num_rows($promos);
		
		$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
		$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $cadeaucount);
		
		if ($promocount > 0) {
			$promos = $DB_site->query("SELECT aps.siteid, site.libelle AS sitelibelle, aps.promoid, a.artid, aps.datedebut, aps.datefin, aps.pctpromo, asite.prix
								FROM article_promo_site AS aps
								INNER JOIN article AS a ON a.artid = aps.artid
								INNER JOIN article_site AS asite ON asite.artid = a.artid AND asite.siteid = aps.siteid
								INNER JOIN site ON site.siteid = aps.siteid
								WHERE aps.datefin > '".time()."' AND a.artid='$artid' ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
		
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
				$action .= '<h4 class="modal-title">'.$multilangue[suppression_promotion].' ' . formaterPrix($promo[pctpromo]) . '% ?</h4>';
				$action .= '</div>';
				$action .= '<div class="modal-body">';
				$action .= $multilangue[suppression_promotion_infos];
				$action .= '</div>';
				$action .= '<div class="modal-footer">';
				$action .= '<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>';
				$action .= '<a href="produits.php?action=supprPromo&promoid=' . $promo[promoid] . '&artid='.$promo[artid].'" class="btn blue">'.$multilangue[oui_supprimer].'</a>';
				$action .= '</div>';
				$action .= '</div>';
				$action .= '</div>';
				$action .= '</div>';
					
				$records["aaData"][] = array(
						"site" => $promo[sitelibelle],
						"date_debut" => $datedebut,
						"date_fin" => $datefin,
						"pourcentage" => formaterPrix($promo[pctpromo])."%",
						"prix_promo" => $prix_promo." ".$tabsites["devise_symbole".$promo[siteid]],
						"prix_normal" =>formaterPrix($promo[prix])." ".$tabsites["devise_symbole".$promo[siteid]],
						"action" => $action
				);
			}
		}
		
		$records["iTotalRecords"] = $promocount;
		$records["iTotalDisplayRecords"] = $promocount;
		
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
	  		case "1" :
	  			$orderby = "a.artid";
	  			break;
	  		case "2" :
	  			$orderby = "totalventes";
	  			break;
	  		case "3" :
	  			$orderby = "totalvues";
	  			break;
	  		case "4" :
	  			$orderby = "asite.libelle";
	  		break;
	  		case "5" :
	  			$orderby = "a.artcode";
	  		break;
	  		case "6" :
	  			$orderby = "a.code_EAN";
	  		break;
	  		case "9" :
	  			$orderby = "aps.pctpromo";
	  			break;
	  		case "10" :
	  			$orderby = "asite.prix";
	  		break;
	  		case "11" :
	  			$orderby = "asite.prixpublic";
	  		break;
	  		case "12" :
	  			$orderby = "asite.prixpro";
	  		break;
	  		case "13" :
	  			$orderby = "s.nombre";
	  		break;
	  		default:
	  			$orderby = "asite.libelle";
	  		break;
	  	}
	  	
	  	$sensorder = $order[0][dir];
	  	$search[value] = securiserSql($search[value]);
	  	
	  	if($catids_select != ""){
	  		$and_catids = "a.catid IN ($catids_select) AND";
	  	}else{
	  		$and_catids="";
	  	}
	  		  	
		$where = "$and_catids (a.artid LIKE '%$search[value]%'";
		$where .= " OR asite.libelle LIKE '%$search[value]%'";
	  	$where .= " OR a.artcode LIKE '%$search[value]%'";
	  	$where .= " OR a.code_EAN LIKE '%$search[value]%'";
	  	$where .= " OR aps.pctpromo LIKE '%$search[value]%'";
	  	$where .= " OR asite.prix LIKE '%$search[value]%'";
	  	$where .= " OR asite.prixpublic LIKE '%$search[value]%'";
	  	$where .= " OR asite.prixpro LIKE '%$search[value]%'";
	  	$where .= " OR s.nombre LIKE '%$search[value]%')";
	  	
	  	$articles = $DB_site->query("SELECT *,a.image as image FROM article AS a 
								  			INNER JOIN article_site AS asite ON a.artid = asite.artid AND asite.siteid = '1'
	  										LEFT JOIN article_promo_site AS aps ON a.artid = aps.artid AND aps.siteid = '1'
								  			LEFT JOIN stock AS s ON a.artid = s.artid
								  			WHERE $where 
	  										GROUP BY a.artid 
											ORDER BY $orderby $sensorder");

	  	$articlecount = $DB_site->num_rows($articles);
	  	
	  	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
	  	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $articlecount);
	  	
	  	if ($articlecount > 0) {
	  		$articles = $DB_site->query("SELECT *,a.image as image FROM article AS a 
								  			INNER JOIN article_site AS asite ON a.artid = asite.artid AND asite.siteid = '1'
	  										LEFT JOIN article_promo_site AS aps ON a.artid = aps.artid AND aps.siteid = '1'
								  			LEFT JOIN stock AS s ON a.artid = s.artid
								  			WHERE $where 
											GROUP BY a.artid 
	  										ORDER BY $orderby $sensorder
	  										LIMIT $limitlower, $perpage");
	  		while ($article = $DB_site->fetch_array($articles)){
	  			$cheminsanshref = "";
	  			$marque = $DB_site->query_first("SELECT * FROM article_marque INNER JOIN marque_site USING(marqueid) WHERE artid = '$article[artid]' AND siteid = '1'");
	  			if ($article[activeV1] == 1)
	  				$activeV1 = '<div data-action="activer" data-value="' . $article[artid] . '" data-version="V1" data-original-title="' . $multilangue[desactiver] . '" data-placement="top" class="btn default btn-sm activer green tooltips">V1</div>';
	  			else
	  				$activeV1 = '<div data-action="activer" data-value="' . $article[artid] . '" data-version="V1" data-original-title="' . $multilangue[activer] . '" data-placement="top" class="btn default btn-sm activer red tooltips">V1</div>';
	  			if ($article[activeV2] == 1)
	  				$activeV2 = '<div data-action="activer" data-value="' . $article[artid] . '" data-version="V2" data-original-title="' . $multilangue[desactiver] . '" data-placement="top" class="btn default btn-sm activer green tooltips">V2</div>';
	  			else
	  				$activeV2 = '<div data-action="activer" data-value="' . $article[artid] . '" data-version="V2" data-original-title="' . $multilangue[activer] . '" data-placement="top" class="btn default btn-sm activer red tooltips">V2</div>';
	  			if ($article[commandable] == 1)
	  				$commandable = '<div data-action="commandable" data-value="' . $article[artid] . '" data-original-title="' . $multilangue[desactiver] . '" data-placement="top" class="btn default btn-sm green tooltips"><i class="fa fa-check-square-o"></i></div>';
	  			else
	  				$commandable = '<div data-action="commandable" data-value="' . $article[artid] . '" data-original-title="' . $multilangue[activer] . '" data-placement="top" class="btn default btn-sm red tooltips"><i class="fa fa-square-o"></i></div>';
	  			
	  			$regleurlrewriteDefaut = $regleurlrewrite[1];	  			
	  			$article[url] = url_rewrite($article[libelle]);	  			
	  			
	  			$actions = '<a href="produits.php?action=modifier&artid=' . $article[artid] . '" data-original-title="' . $multilangue[modifier] . '" data-placement="top" class="btn tooltips">';
	  			$actions .= '<i class="fa fa-edit fs-18 font-blue"></i>';
	  			$actions .= '</a>';
	  			$actions .= '<a href="http://'.$host.'/'.$regleurlrewriteDefaut[article].'-'.$article[url].'-'.$article[artid].'.htm" target="_blank" class="btn tooltips" ><i class="fa fa-search fs-18 font-green"></i></a>';
	  			$actions .= '<a href="http://'.$host.'/V2/'.$regleurlrewriteDefaut[article].'-'.$article[url].'-'.$article[artid].'.htm" target="_blank" class="btn tooltips" ><i class="fa fa-search fs-18 font-yellow"></i></a>';
	  			$actions .= '<a href="#myModal' . $article[artid] . '" id="btn_suppr' . $article[artid]. '" data-original-title="' . $multilangue[supprimer] . '" data-placement="top" data-toggle="modal" role="button" class="btn tooltips">';
	  			$actions .= '<i class="fa fa-trash-o fs-18 font-red"></i>';
	  			$actions .= '</a>';
	  			$actions .= '<div aria-hidden="true" aria-labelledby="myModalLabe' . $article[artid] . '" role="dialog" tabindex="-1" class="modal fade" id="myModal' . $article[artid] . '" style="display: none;">';
	  			$actions .= '<div class="modal-dialog">';
	  			$actions .= '<div class="modal-content">';
	  			$actions .= '<div class="modal-header">';
	  			$actions .= '<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>';
	  			$actions .= '<h4 class="modal-title">"'.$multilangue[suppression_article].'" "' . $article[libelle] . '" ?</h4>';
	  			$actions .= '</div>';
	  			$actions .= '<div class="modal-body">';
	  			$actions .= $multilangue[suppression_article];
	  			$actions .= '</div>';
	  			$actions .= '<div class="modal-footer">';
	  			$actions .= '<button aria-hidden="true" data-dismiss="modal" class="btn default">"'.$multilangue[non].'"</button>';
	  			$actions .= '<a href="produits.php?action=supprimer&artid=' . $article[artid] . '" class="btn blue">"'.$multilangue[oui_supprimer].'"</a>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
	  			
	  			if(!$article[pctpromo]){
	  				$article[pctpromo] = 0;
	  			}
	  			
	  			$tab_retour = array(
	  				"image" => '<img width="100" src="' . ($article[image] ? 'http://' . $host . '/br-' . url_rewrite($article[libelle]) . '-' . $article[artid] . '.' . $article[image] : '') . '" alt="">',
  					"nb_ventes" => $article[totalventes],
	  				"nb_vues" => $article[totalvues],
	  				"identifiant" => $article[artid],
	  				"libelle" => $article[libelle],
  					"artcode" => $article[artcode],
  					"code_EAN" => $article[code_EAN],
  					"categorie" => cheminsanshref($article[catid], $DB_site),
  					"marque" => $marque[libelle],
	  				"pctpromo" => $article[pctpromo].' %',
  					"prix" => formaterPrix($article[prix]) . "&nbsp;&euro;",
  					"prixpublic" => formaterPrix($article[prixpublic]) . "&nbsp;&euro;",
  					"prixpro" => formaterPrix($article[prixpro]) . "&nbsp;&euro;",
  					"nombre" => $article[nombre],
  					"commandable" => $commandable,
  					"activer" => ''.$activeV1.''.$activeV2,
	  			);
	  			
	  			$sites = $DB_site->query("SELECT siteid FROM site");
	  			while($site = $DB_site->fetch_array($sites)){
	  				$activesite = $DB_site->query_first("SELECT activeV1, activeV2 FROM article_site WHERE siteid = '$site[siteid]' AND artid = '$article[artid]'");
	  				if ($activesite[activeV1] == 1)
	  					$activeV1 = '<div data-action="activer_site" data-siteid="'.$site[siteid].'" data-value="' . $article[artid] . '" data-version="V1" data-original-title="' . $multilangue[desactiver] . '" data-placement="top" class="btn default btn-sm activer_site green tooltips">V1</div>';
		  			else
		  				$activeV1 = '<div data-action="activer_site" data-siteid="'.$site[siteid].'" data-value="' . $article[artid] . '" data-version="V1" data-original-title="' . $multilangue[activer] . '" data-placement="top" class="btn default btn-sm activer_site red tooltips">V1</div>';
		  			if ($activesite[activeV2] == 1)
		  				$activeV2 = '<div data-action="activer_site" data-siteid="'.$site[siteid].'" data-value="' . $article[artid] . '" data-version="V2" data-original-title="' . $multilangue[desactiver] . '" data-placement="top" class="btn default btn-sm activer_site green tooltips">V2</div>';
		  			else
		  				$activeV2 = '<div data-action="activer_site" data-siteid="'.$site[siteid].'" data-value="' . $article[artid] . '" data-version="V2" data-original-title="' . $multilangue[activer] . '" data-placement="top" class="btn default btn-sm activer_site red tooltips">V2</div>';
	  			
		  			$tab_retour["activer$site[siteid]"] = "$activeV1 $activeV2";
	  			}
	  			$tab_retour["actions"] = $actions;
	  			$records["aaData"][] = $tab_retour;
	  		}
	  	}
	
	  	$records["iTotalRecords"] = $articlecount;
	  	$records["iTotalDisplayRecords"] = $articlecount;
	 
	  	echo json_encode($records);
	}
?>