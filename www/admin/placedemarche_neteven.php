<?
set_time_limit(14400); // 20 minutes
ini_set("memory_limit","512M");

if($onestdansuncron != 1 && $backgroundLunch != 1) {
	require_once $_SERVER['DOCUMENT_ROOT']."admin/includes/admin_global.php";
}else{
	if($backgroundLunch != 1) {
		
		$pathToInclude = "V2/";
		
		if($plateforme == "V1") {
			$pathToInclude = "";
		}
		
		require_once $_SERVER['DOCUMENT_ROOT'].$pathToInclude."include/fonction_divers.php";
		require_once $_SERVER['DOCUMENT_ROOT'].$pathToInclude."include/fonction_stock.php";
		require_once $_SERVER['DOCUMENT_ROOT'].$pathToInclude."include/fonction_panier.php";
	}
	require_once $_SERVER['DOCUMENT_ROOT'].$pathToInclude."include/fonction_catalogue.php";
}

//inclusion du fichier global avec connection � neteven et inclusion du fichier de fonction propre
require $_SERVER['DOCUMENT_ROOT']."neteven/neteven_global.php";

$arrArticles = array();
$i = 0;
$y = 0;
$z = 0;
$where = "";
if ($liste_artid != "") {
	$where = " AND artid IN ($liste_artid) ";
}

//dev spé provisoire pour pas que les articles non actif en v1 soit pas transmis
//$where .= " AND activeV1FR = '1' ";

$articles = $DB_site->query("SELECT artid FROM article WHERE 1 $where ORDER BY artid"); 
while ($article = $DB_site->fetch_array($articles)) 	{
	$table = netevenConstructArticle($DB_site, $article[artid]);
	foreach($table as $key => $value){
		$i++;
		$y++;
		array_push($arrArticles, $value);
	}
	//si 50 articles ou plus on envoi
	if($i >= 1) {
		//echo "<h2>PostItems()</h2><pre>";
		$packArticle = array("items" => $arrArticles);
		try {
			$response = $client->PostItems($packArticle);
			$itemsStatus = $response->PostItemsResult->InventoryItemStatusResponse;
		} catch (Exception $e) {
			$itemsStatus = null;
		}
		//control
		if (!is_null($itemsStatus)) {
			if (!is_array($itemsStatus)) { $itemsStatus = array($itemsStatus); }
			foreach ($itemsStatus as $itemStatus) {
				
				if($itemStatus->StatusResponse != "Updated" && $itemStatus->StatusResponse != "Inserted") {
					$z++;
					if($backgroundLunch != 1) {
						echo '<hr />';
						echo $itemStatus->StatusResponse . "\n";
						echo '<hr />';
						print_r($arrArticles);
						echo '<hr />';
						echo "<pre>Last request:\n" . html_entity_decode($client->__getLastRequest()). "</pre>\n";
						echo "<pre>Last response:\n" . html_entity_decode($client->__getLastResponse()). "</pre>\n";
					}
				}
			}
		}else{
			$z++;
			if($backgroundLunch != 1) {
				echo '<hr />';
				print_r($arrArticles);
				echo '<hr />';
				echo "<pre>Last request:\n" . html_entity_decode($client->__getLastRequest()). "</pre>\n";
				echo "<pre>Last response:\n" . html_entity_decode($client->__getLastResponse()). "</pre>\n";
			}
		}
		$arrArticles = array();
		$i = 0;
	}
}

//il reste des articles pas envoy�
if($i != 0) {
	//echo "<h2>PostItems()</h2><pre>";
	$packArticle = array("items" => $arrArticles);
	try {
		$response = $client->PostItems($packArticle);
		$itemsStatus = $response->PostItemsResult->InventoryItemStatusResponse;
	} catch (Exception $e) {
		$itemsStatus = null;
	}
	//control
	if (!is_null($itemsStatus)) {
		if (!is_array($itemsStatus)) { $itemsStatus = array($itemsStatus); }
		foreach ($itemsStatus as $itemStatus) {
			if($itemStatus->StatusResponse != "Updated" && $itemStatus->StatusResponse != "Inserted") {
				$z++;
				if($backgroundLunch != 1) {
					echo '<hr />';
					echo $itemStatus->StatusResponse . "\n";
					echo '<hr />';
					print_r($arrArticles);
					echo '<hr />';
					echo "<pre>Last request:\n" . html_entity_decode($client->__getLastRequest()). "</pre>\n";
					echo "<pre>Last response:\n" . html_entity_decode($client->__getLastResponse()). "</pre>\n";
				}
			}
		}
	}else{
		$z++;
		if($backgroundLunch != 1) {
			echo '<hr />';
			print_r($arrArticles);
			echo '<hr />';
			echo "<pre>Last request:\n" . html_entity_decode($client->__getLastRequest()). "</pre>\n";
			echo "<pre>Last response:\n" . html_entity_decode($client->__getLastResponse()). "</pre>\n";
		}
	}
	$arrArticles = array();
	$i = 0;
}

if($backgroundLunch != 1) {
	echo "<br><br>====================================================================<br><br>";
	echo date("d-m-Y / H:i:s")." : L'export vers neteven � bien �t� effectu� (nb article : $y / nb error : $z)";
	echo "<br><br>====================================================================<br><br>";
}

?>