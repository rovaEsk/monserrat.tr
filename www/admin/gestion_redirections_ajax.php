<?php
include "includes/header.php";


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
			case "1" :
				$orderby = "old_url";
				break;
			case "2" :
				$orderby = "new_url";
				break;
			case "3" :
				$orderby = "type";
				break;
			default:
				$orderby = "old_url";
				break;
		}
		
		$sensorder = $order[0][dir];
		

		$search[value] = securiserSql($search[value]);
		$where = "";
		if($search[value]!=""){
			$where = "WHERE old_url LIKE '%$search[value]%'";
			$where .= " OR new_url LIKE '%$search[value]%'";
		
		}
		
		$urls = $DB_site->query("SELECT * FROM redirections $where ORDER BY $orderby $sensorder");
		$urlscount = $DB_site->num_rows($urls);
		
		$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
		$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $urlscount);
		
		if ($urlscount > 0) {
			$urls = $DB_site->query("SELECT * FROM redirections $where ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
			
			while ($url = $DB_site->fetch_array($urls)){
				$select301 = "";
				$select302 = "";
				if($url[code] == "301"){
					$select301 = "selected = 'selected'";
				}else{
					$select302 = "selected = 'selected'";
				}
				
				
	  			$actions = '<a href="#myModalModif' . $url[id] . '" data-original-title="' . $multilangue[modifier] . '" data-placement="top" data-toggle="modal" role="button" class="btn tooltips">';
	  			$actions .= '<i class="fa fa-edit fs-18 font-blue"></i>';
	  			$actions .= '</a>';
	  			$actions .= '<div aria-hidden="true" aria-labelledby="myModalLabe' . $url[id] . '" role="dialog" tabindex="-1" class="modal fade ta-left" id="myModalModif' . $url[id] . '" style="display: none;">';
	  			$actions .= '<div class="modal-dialog">';
	  			$actions .= '<div class="modal-content">';
	  			$actions .= '<div class="modal-header ta-center">';
	  			$actions .= '<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>';
	  			$actions .= '<h4 class="modal-title">'.$multilangue[modif_redirection].' ?</h4>';
	  			$actions .= '</div>';
	  			$actions .= '<form action="gestion_redirections.php?action=modifier&id='.$url[id].'" method="post">';
	  			$actions .= '<div class="modal-body">';
	  			$actions .= '<div class="col-md-4 ta-right">
									<label class="control-label" style="line-height:27px;"><b>'.$multilangue[ancienne_url].' :</b></label>
								</div>
								<div class="col-md-8">
									<table style="width:70%;">
										<tbody>
											<tr>
												<td valign="middle" align="right"><label class="control-label">http://'.$host.'/</label></td>
												<td valign="middle" align="left" style="width:70%;">
													<input type="text" name="ancienneURL" class="form-control" value="'.$url[old_url].'">
												</td>
											</tr>
										</tbody>
									</table>					
								</div>';
	  			$actions .= '<div class="col-md-4 ta-right">
									<label class="control-label" style="line-height:27px;"><b>'.$multilangue[nouvelle_url].' :</b></label>
								</div>
								<div class="col-md-8">
									<table style="width:70%;">
										<tbody>
											<tr>
												<td valign="middle" align="right"><label class="control-label">http://'.$host.'/</label></td>
												<td valign="middle" align="left" style="width:70%;">
													<input type="text" name="nouvelleURL" class="form-control" value="'.$url[new_url].'">
												</td>
											</tr>
										</tbody>
									</table>					
								</div>';
	  			$actions .= '<div class="col-md-4 ta-right">
									<label class="control-label" style="line-height:27px;"><b>'.$multilangue[type].' :</b></label>
								</div>
								<div class="col-md-2">
									<select name="type" class="form-control">
										<option value="301" '.$select301.'>301</option>
										<option value="302" '.$select302.'>302</option>
									</select>
								</div>';
	  			$actions .= '</div><div class="clear"></div>';
	  			$actions .= '<div class="modal-footer">';
	  			$actions .= '<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>';
	  			$actions .= '<button type="submit" class="btn blue">'.$multilangue[modifier].'</a>';
	  			$actions .= '</div>';
	  			$actions .= '</form>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
	  			
	  			$actions .= '<a href="#myModalSuppr' . $url[id] . '" data-original-title="' . $multilangue[supprimer] . '" data-placement="top" data-toggle="modal" role="button" class="btn tooltips">';
	  			$actions .= '<i class="fa fa-trash-o fs-18 font-red"></i></a>';
	  			$actions .= '<div aria-hidden="true" aria-labelledby="myModalLabe' . $url[id] . '" role="dialog" tabindex="-1" class="modal fade" id="myModalSuppr' . $url[id] . '" style="display: none;">';
	  			$actions .= '<div class="modal-dialog">';
	  			$actions .= '<div class="modal-content">';
	  			$actions .= '<div class="modal-header">';
	  			$actions .= '<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>';
	  			$actions .= '<h4 class="modal-title">'.$multilangue[suppr_redirection] .' ?</h4></div>';
	  			$actions .= '<div class="modal-body">';
	  			$actions .= $multilangue[suppression_redirection].'<br> http://'.$host.''.$url[old_url].' '.$multilangue[vers].' http://'.$host.''.$url[new_url].' ?</div>';
	  			$actions .= '<div class="modal-footer">';
	  			$actions .= '<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>';
	  			$actions .= '<a href="gestion_redirections.php?action=supprimer&id=' . $url[id] . '" class="btn blue">'.$multilangue[oui_supprimer].'</a>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
	  			$actions .= '</div>';
	  			
				$records["aaData"][] = array(
						"checkbox" => "<input type='checkbox' name='chk[$url[id]]' class='chk' id='$url[id]' onclick='ligneSelect();'>",
						"ancienne" => $url[old_url],
						"nouvelle" => $url[new_url],
						"type" => $url[code],
						"actions" => $actions
				);
			}
		}
		
		$records["iTotalRecords"] = $urlscount;
		$records["iTotalDisplayRecords"] = $urlscount;
		
		echo json_encode($records);
	}
?>