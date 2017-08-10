<?php
	include "includes/header.php";
	
	if (isset($action) && $action == "enregistrer"){
		if ($artid != "" && $mcid != ""){
			$rueducommerce = $DB_site->query_first("SELECT * FROM rueducommerce WHERE artid = '$artid'");
			if ($rueducommerce[artid]){
				$DB_site->query("UPDATE rueducommerce SET MCID = '" . securiserSql($mcid) . "' WHERE artid = '$artid'");
			}else{
				$DB_site->query("INSERT INTO rueducommerce(artid, MCID) VALUES ('" . securiserSql($artid) . "', '" . securiserSql($mcid) . "')");
			}
			echo $artid;
		}
	}
	
	if (isset($action) && $action == "MCID"){
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
	  			$orderby = "artcode";
	  		break;
	  		case "2" :
	  			$orderby = "catid";
	  		break;
	  		default:
	  			$orderby = "libelle";
	  		break;
	  	}
	  	
	  	$sensorder = $order[0][dir];
	  	$search[value] = securiserSql($search[value]);
		$where = "article_site.libelle LIKE '%$search[value]%'";
	  	$where .= " OR artcode LIKE '%$search[value]%'";
	  	$where .= " OR catid IN(SELECT catid FROM categorie INNER JOIN categorie_site USING(catid) WHERE libelle LIKE '%$search[value]%')";
	  	$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE $where ORDER BY $orderby $sensorder");
	  	$articlecount = $DB_site->num_rows($articles);
	  	
	  	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
	  	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $articlecount);
	  	
	  	if ($articlecount > 0) {
	  		$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE $where ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
	  		while ($article = $DB_site->fetch_array($articles)){
	  			$cheminsanshref = "";
	  			$mcid = $DB_site->query_first("SELECT * FROM rueducommerce WHERE artid = '$article[artid]'");
	  			$records["aaData"][] = array(
					"libelle" => $article[libelle],
					"artcode" => $article[artcode],
	  				"categorie" => cheminsanshref($article[catid], $DB_site),
					"mcid" => '<input class="form-control input-inline" value="' . $mcid[MCID] . '" data-value="' . $article[artid] . '"><div class="btn blue mcid" data-value="' . $article[artid] . '">' . $multilangue[enregistrer] . '</div>'
		   		);
	  		}
	  	}
	
	  	$records["iTotalRecords"] = $articlecount;
	  	$records["iTotalDisplayRecords"] = $articlecount;
	 
	  	echo json_encode($records);
	}
	
	if (isset($action) && $action == "attribut"){
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
				$orderby = "MCID";
				break;
			case "2" :
				$orderby = "categorie";
				break;
			default:
				$orderby = "MCID";
				break;
		}
	
		$sensorder = $order[0][dir];
		$search[value] = securiserSql($search[value]);
		$where = "MCID LIKE '%$search[value]%'";
		$where .= " OR categorie LIKE '%$search[value]%'";
		$attributs = $DB_site->query("SELECT * FROM rueducommerce_attribut WHERE $where GROUP BY MCID ORDER BY $orderby $sensorder");
		$attributcount = $DB_site->num_rows($attributs);
	
		$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
		$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $attributcount);
	
		if ($attributcount > 0) {
			$attributs = $DB_site->query("SELECT * FROM rueducommerce_attribut WHERE $where GROUP BY MCID ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
			while ($attribut = $DB_site->fetch_array($attributs)){
				$contenu = '<table class="table table-bordered mb-0"><thead><tr>';
				$contenu .= '<th class="bg-white ta-center col-md-6">' . $multilangue[libelle] . '</th>';
				$contenu .= '<th class="bg-white ta-center col-md-6">' . $multilangue[code] . '</th></tr></thead><tbody>';
				$details = $DB_site->query("SELECT * FROM rueducommerce_attribut WHERE MCID = '$attribut[MCID]'");
				while ($detail = $DB_site->fetch_array($details)){
					$contenu .= '<tr style="border-top: 1px solid #EEEEEE;"><td class="bg-white ta-center col-md-4">' . $detail[libelle] . '</td>';
					$contenu .= '<td class="bg-white ta-center col-md-4">' . $detail[code] . '</td></tr>';
				}
				$contenu .= '</tbody></table>';
				$records["aaData"][] = array(
						"details" => '<span class="row-details row-details-close"></span>',
						"mcid" => $attribut[MCID],
						"categorie" => $attribut[categorie],
						"contenu" => $contenu
				);
			}
		}
	
		$records["iTotalRecords"] = $attributcount;
		$records["iTotalDisplayRecords"] = $attributcount;
	
		echo json_encode($records);
	}
?>