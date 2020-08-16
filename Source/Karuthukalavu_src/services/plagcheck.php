<?php
//require_once('util.php');
require_once("config.php");
require_once('db.php');
require_once('twn.php');

$debug = false;
$action = "";

if (isset($_GET["action"]) && $_GET["action"] != "") {
	$action = $_GET["action"];
}

if (isset($_POST["action"]) && $_POST["action"] != "") {
	$action = $_POST["action"];
}

switch($action){
	case "chktxt":
		$result = new Result();
		
		if (! isset($_POST["qtext"]) || $_POST["qtext"] == "") {
			$result->setstatus( "notext" );
		} else {
			
			$text = $_POST ["qtext"];
			
			if(isset($_POST ["ctype"]))
				$type = $_POST ["ctype"];
			else
				$type = "thodar_thedal";
			
			$pc = new PlagCheck ($result);
			dump ("Checking...");
			$result = $pc->Check( $text, $type );
		}
		
		echo json_encode ( $result );
		break;
	
	case "addcmt":
		try{
			$db = GetDB();
			$records = $db->ExecuteQuery("insert into kkv_karuthu(peyar,minnanjal,karuthu) values (?,?,?)", "sss", 
				array($_POST["peyar"],$_POST["minnanjal"],$_POST["karuthu"]));
			echo "success";
		}catch(Exception $ex){
			echo "error while adding comment.";
			echo $ex->getMessage();
		}
		
		break;
		
	case "gethtm":
		
		$url = "";
		if (isset($_POST["url"]) && $_POST["url"] != "") {
			$url = $_POST["url"];
		} else if (isset($_GET["url"]) && $_GET["url"] != ""){
			$url = $_GET["url"];
		}
		
		if (isset($_POST["txt"]) && $_POST["txt"] != "") {
			$txt = $_POST["txt"];
		} else if (isset($_GET["txt"]) && $_GET["txt"] != ""){
			$txt = $_GET["txt"];
		}
		
		$pc = new PlagCheck ();
		$html = $pc->GetHtml($url);

		{
			if(strpos($html,$txt) !== false){
				$html = str_ireplace($txt,"<mark id='tdathodar'>".$txt."</mark>",$html);
			}else{
				$words = explode(" ", $txt);
				for($i=0;$i<count($words);$i++){
					//$pos = strpos($html,$words[$i]);
					$html = str_ireplace($words[$i],"<mark id='tdathodar".($i==0?"":$i)."'>".$words[$i]."</mark>",$html);
				}
			}
		}
		
		echo $html;
		
		break;
}

function GetDB(){
	global $cfg;
	$db = new DataBase($cfg->server,$cfg->uid,$cfg->pwd,$cfg->db);
	return $db;
}

class PlagCheck {
	var $sapi;
	var $slkm;
	
	function PlagCheck($result=null) {
		$this->sapi = new MCSearch ( "" );
		$this->result = $result;
		$this->slkm = new Sollakam();
		$this->slkm->etru("sollakam1.txt");
		$this->slkm->kts_etru("morphs1.txt");
	}
	
	function Check($urai,$thedalVagai) {
		$result = new Result();
		dump ( "starting search..." );
		
		try {
			$katturai = new Katturai($urai);
			
			foreach($katturai->patthigal as $patthi){
				foreach($patthi->varigal as $vari){
					foreach($vari->thodargal as $thodar){
						$count = 0;
						$matches = array();
						
						if($thedalVagai=="sol_thedal")
						{
							list($count1,$matches1) = $this->Thedu($thodar->solthodar);
							$count += $count1;
							$matches = array_merge($matches,array_slice($matches1,0,10));
						}
						else if($thedalVagai=="thodar_thedal")
						{
							list($count1,$matches1) = $this->Thedu('"'.$thodar->solthodar.'"');
							$count += $count1;
							$matches = array_merge($matches,array_slice($matches1,0,10));
						}
						else if($thedalVagai=="inai_thedal")
						{
							$inaithodarkal = $this->slkm->inaithodarkal($thodar->solthodar);
							$result->msg .= "inaithodargal:";
							foreach($inaithodarkal as $ithodar){
								$result->msg .= "|" . $ithodar;
								list($count1,$matches1) = $this->Thedu('"'.$ithodar.'"');
								$count += $count1;
								$matches = array_merge($matches,array_slice($matches1,0,5));
							}
							$result->msg .= "|x|";
						}
						else 
						{
							$result->msg = "தயை கூர்ந்து ஒரு தேடல் வகையை தேர்க";
						}
						
						$thodar->kalaven = $count;
						$sorkal = $thodar->Sorkal();
						$katturai->solEn += $sorkal;
						
						if($thodar->kalaven > 0)
							$katturai->kSolEn += $sorkal;
						
						if($katturai->ptKalaven < $count) 
							$katturai->ptKalaven = $count;
						
						foreach($matches as $match){
							$thodar->kalavugal[] = new Kalavu([
									'name'=>$match->name,
									'url'=>$match->url,
									'snippet'=>$match->snippet,
									'dispUrl'=>$match->dispUrl
							]);
						}
					}
				}
			}
			
			$katturai->kalaven = number_format(($katturai->kSolEn / $katturai->solEn) * 100, 2);
			$result->katturai = $katturai;
			$result->setstatus("success");
			
		} catch (Exception $e) {
			$result->msg = $e->getMessage();
		}
		
		return $result;
	}
	
	function Thedu($urai){
		$count = 0;
		$matches = array();
		$results = json_decode( $this->sapi->Search($urai) );
		//var_dump($results);
		//$this->result->response = json_encode($results);
		
		if ($results->_type == "SearchResponse") {
			
			if(property_exists($results,"webPages")){
				
				if(property_exists($results->webPages,"value"))
					$value = $results->webPages->value;
				
				if(property_exists($results->webPages,"totalEstimatedMatches"))
					$count = $results->webPages->totalEstimatedMatches;
				
				foreach ( $value as $val ) {
					$matches[] = new Match ( $val->id, $val->name, $val->url, $val->displayUrl, $val->snippet, $val->dateLastCrawled );
				}
			}
		}
		
		return array($count,$matches);
	}
	
	function GetHtml($url){

		$ci = curl_init ();
		curl_setopt ( $ci, CURLOPT_URL, $url );
		curl_setopt ( $ci, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt ( $ci, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ci, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $ci, CURLOPT_MAXREDIRS, 5 );
		curl_setopt ( $ci, CURLINFO_HEADER_OUT, true );
		
		$resp = curl_exec ( $ci );
		
		curl_close ( $ci );
		
		return $resp;
	
	}
	
	
}




class MCSearch {
	
	var $url = "https://api.cognitive.microsoft.com/bing/v5.0/search";
	var $key = "";
	var $options;
	
	function MCSearch($key) {
		$this->key = "Ocp-Apim-Subscription-Key:" . $key;
		$this->options = "textDecorations=true&textFormat=HTML";
	}
	
	function Search($text) {
		$ci = curl_init ();
		curl_setopt ( $ci, CURLOPT_URL, $this->url . "?q=" . urlencode ( $text ) . "&" . $this->options);
		curl_setopt ( $ci, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt ( $ci, CURLOPT_HTTPHEADER, [ 
				$this->key 
		] );
		curl_setopt ( $ci, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ci, CURLINFO_HEADER_OUT, true );
		
		$resp = curl_exec ( $ci );
		
		$info = curl_getinfo ( $ci );
		curl_close ( $ci );
		
		return $resp;
	}
}

class Match {
	var $id;
	var $name;
	var $url;
	var $dispUrl;
	var $snippet;
	var $date;
	
	function Match($id, $name, $url, $dispUrl, $snippet, $date) {
		$this->id = $id;
		$this->name = $name;
		$this->url = $url;
		$this->dispUrl = $dispUrl;
		$this->snippet = $snippet;
		$this->date = $date;
	}
}

class Result {
	var $status = "";
	var $msg = "";
	var $success = false;
	var $katturai;
	
	function setstatus($status) {
		$this->status = $status;
		
		if ($status == "success")
			$this->success = true;
	}
}

class Katturai{
	var $patthigal = array();
	var $kalaven = 0;
	var $thodaren = 0;
	var $ptKalaven = 0;
	var $solEn = 0;
	var $kSolEn = 0;
	
	function Katturai($urai){
		$pl = preg_split("/(\r\n|\n|\r)/",$urai,-1,PREG_SPLIT_NO_EMPTY);
		
		foreach($pl as $p){
			$patthi = new Patthi();
			
			$vl = preg_split("/(\.|;|:|,) +/",$p,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			
			$thodar = null;
			foreach($vl as $v){
				
				if(!strpos($v," ") && $thodar != null){
					$thodar->solthodar.= $v;
					continue;
				}
				
				$vari = new Vari();
				
				$thodar = new Thodar();
				$thodar->solthodar = $tmp.$v;
				
				$vari->thodargal[] = $thodar;
				$katturai->thodaren++;
				
				$patthi->varigal[] = $vari;
			}
			$this->patthigal[] = $patthi;
		}
		
	}
}

class Patthi{
	var $varigal = array();
}

class Vari{
	var $thodargal = array();
}

class Thodar{
	var $solthodar;
	var $kalaven;
	var $kalavugal = array();
	
	function Sorkal(){
		return count(explode(" ",$this->solthodar));
	}
}

class Kalavu{
	var $name;
	var $url;
	var $snippet;
	var $dispUrl;
	
	function __construct($args = array()){
		// build all args into their corresponding class properties
		foreach($args as $key => $val) {
			// only accept keys that have explicitly been defined as class member variables
			if(property_exists($this, $key)) {
				$this->{$key} = $val;
			}
		}
	}
}

function dump($obj) {
	global $debug;
	if ($debug)
		var_dump ( $obj );
}
?>

