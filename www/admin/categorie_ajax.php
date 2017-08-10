<?php
	include "includes/header.php";
	
	$scriptcourant = "categorie.php";
	
	if (isset($action) and $action == "deplacer"){
		if($admin_droit[$scriptcourant][ecriture]){
			if ($parentid != $catid)
				$DB_site->query("UPDATE categorie SET parentid = '$parentid' WHERE catid = '$catid'");
			$tabcatidsEnfants = array();
			compter_produits($DB_site);
			compter_produits_actifs($DB_site);
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if (isset($action) and $action == "copier"){
		if($admin_droit[$scriptcourant][ecriture]){
			$categorie = $DB_site->query_first("SELECT * FROM categorie WHERE catid = '$copcatid'");
			if ($copcatid && $copcatid != $catid && $catid != $categorie[parentid])
				copier_rayon($DB_site, $catid, $copcatid);
			$tabcatidsEnfants = array();
			compter_produits($DB_site);
			compter_produits_actifs($DB_site);
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if (isset($action) and $action == "jstree"){
		header('Content-Type: application/json');
		$records = array();
		$records[] = array(
				"id" => $version . "_" . 0,
				"parent" => "#",
				"text" => $titleFR,
				"state" => array("opened" => true)
		);
		$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '1' ORDER BY position");
		while ($categorie = $DB_site->fetch_array($categories)){
			$records[] = array(
				"id" => $version . "_" . $categorie[catid],
				"parent" =>  $version . "_" . $categorie[parentid],
				"text" => $categorie[libelle]
			);
		}
		echo json_encode($records);
	}
	
	if (!isset($action) or $action == ""){
	  	$records = array();
	  	$records["aaData"] = array(); 
	
	  	$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$catid' AND siteid = '1' ORDER BY position");
	  	$categoriecount = $DB_site->num_rows($categories);
	  	
	  	if ($categoriecount > 0) {
	  		$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE parentid = '$catid' AND siteid = '1' ORDER BY position");
	  		while ($categorie = $DB_site->fetch_array($categories)){
	  			
	  			// Article actifs :	  			  			
	  			//$enfants = $categorie[catid];
	  			//catid_enfants($DB_site, $categorie[catid]);	 	
	  			//$count_categorieV1 = $DB_site->query_first("SELECT SUM(articles_actifsV1) AS totalV1 FROM categorie_site WHERE catid IN ($enfants) AND visible_treeviewV1='1' AND siteid='1'");
	  			//$count_categorieV2 = $DB_site->query_first("SELECT SUM(articles_actifsV2) AS totalV2 FROM categorie_site WHERE catid IN ($enfants) AND visible_treeviewV2='1' AND siteid='1'");
	  			
	  			if ($categorie[visible_treeviewV1] == 1)
	  				$visibleV1 = '<div data-original-title="' . $multilangue[passer_invisible] . '" data-placement="top" data-value="' . $categorie[catid] . '" data-version="V1" class="btn default btn-sm green tooltips"><i class="fa fa-check-square-o"></i></div>';
	  			else
	  				$visibleV1 = '<div data-original-title="' . $multilangue[passer_visible] . '" data-placement="top" data-value="' . $categorie[catid] . '" data-version="V1" class="btn default btn-sm red tooltips"><i class="fa fa-square-o"></i></div>';
	  			if ($categorie[visible_treeviewV2] == 1)
	  				$visibleV2 = '<div data-original-title="' . $multilangue[passer_invisible] . '" data-placement="top" data-value="' . $categorie[catid] . '" data-version="V2" class="btn default btn-sm green tooltips"><i class="fa fa-check-square-o"></i></div>';
	  			else
	  				$visibleV2 = '<div data-original-title="' . $multilangue[passer_visible] . '" data-placement="top" data-value="' . $categorie[catid] . '" data-version="V2" class="btn default btn-sm red tooltips"><i class="fa fa-square-o"></i></div>';
	  			$action = '<a href="categorie.php?action=modifier&catid=' . $categorie[catid] . '" data-original-title="' . $multilangue[modifier] . '" data-placement="top" class="btn tooltips">';
	  			$action .= '<i class="fa fa-edit fs-18 font-blue"></i></a>';
	  			$action .= '<div data-value="' . $categorie[catid] . '" data-original-title="' . $multilangue[copier] . '" data-placement="top" class="btn tooltips btn-copier">';
	  			$action .= '<i class="fa fa-copy fs-18 font-blue"></i></div>';
	  			
	  			$nb_articles = $DB_site->query_first("SELECT COUNT(*) FROM position WHERE catid = '$categorie[catid]'");
	  			if($nb_articles[0] > 0){
		  			$action .= '<a href="ordre_affichage.php?catid=' . $categorie[catid] . '" data-original-title="' . $multilangue[odre_affichage] . '" data-placement="top" class="btn tooltips">';
		  			$action .= '<i class="fa fa-list-ol fs-18 font-blue"></i></a>';
	  			}	  			
	  			
	  			$action .= '<a href="#myModal' . $categorie[catid] . '" data-original-title="' . $multilangue[supprimer] . '" data-placement="top" data-toggle="modal" role="button" class="btn tooltips">';
	  			$action .= '<i class="fa fa-trash-o fs-18 font-red"></i></a>';
	  			$action .= '<div aria-hidden="true" aria-labelledby="myModalLabe' . $categorie[catid] . '" role="dialog" tabindex="-1" class="modal fade" id="myModal' . $categorie[catid] . '" style="display: none;">';
	  			$action .= '<div class="modal-dialog">';
	  			$action .= '<div class="modal-content">';
	  			$action .= '<div class="modal-header">';
	  			$action .= '<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>';
	  			$action .= '<h4 class="modal-title">'.$multilangue[suppression_categorie] .'"' . $categorie[libelle] . '" ?</h4></div>';
	  			$action .= '<div class="modal-body">';
	  			$action .= $multilangue[suppression_categorie_infos].'</div>';
	  			$action .= '<div class="modal-footer">';
	  			$action .= '<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>';
	  			$action .= '<a href="categorie.php?action=supprimer&catid=' . $categorie[catid] . '" class="btn blue">'.$multilangue[oui_supprimer].'</a>';
	  			$action .= '</div>';
	  			$action .= '</div>';
	  			$action .= '</div>';
	  			$action .= '</div>';
				$categorie[totalcommandesttc] = formaterPrix($categorie[totalcommandesttc]) . " $devise[symbole]";
	  			$records["aaData"][] = array(
	  				"DT_RowId" => $categorie[catid],
					"libelle" => '<a href="categorie.php?catid=' . $categorie[catid] .'">' . $categorie[libelle] . '</a>',
	  				"actifsV1" => "$categorie[articles_actifsV1] $multilangue[sur] $categorie[nb_articles]",
	  				"actifsV2" => "$categorie[articles_actifsV2] $multilangue[sur] $categorie[nb_articles]",
					"visibleV1" => $visibleV1,
	  				"visibleV2" => $visibleV2,
					"action" => $action
		   		);
	  		}
	  	}
	
	  	$records["iTotalRecords"] = $categoriecount;
	  	$records["iTotalDisplayRecords"] = $categoriecount;
	 
	  	echo json_encode($records);
	}
?>