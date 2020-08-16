<?php
//require('util.php');

if(PHP_SAPI=="cli"){

	$skm = new Sollakam();
	$skm->etru("sollakam.txt");
	echo "loading morphs...";
	$skm->kts_etru("morphs1.txt");
	echo "morphs loaded.\n";
	//echo "ml:".$skm->moolaSorkal["iyaRRinaar"]."\n";
	echo $skm->inaithodar($argv[1]);

}

class Base {
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

/*
 * சொல்லகம் - 
 * 
 *
 * 
 * 
*/
class Sollakam{
	var $sorkal = array();
	var $solEnkal = array();
	var $kootuSorkal = array(); //கூட்டுச்சொற்கள்
	var $moolaSorkal = array(); //மூலச்சொற்கள்
	
	var $con;
	
	function Sollakam(){
		$this->con = new Converter();
	}
	
	function inaithodarkal($thodar){
		$thodarkal = array();
		foreach(explode(" ",$thodar) as $sol){
			//echo "Sol-$sol";
			$inaikal = $this->inaikal($sol);
			$ms = $this->moolasol($sol);
			if($ms != null){
				$inaikal1 = $this->inaikal($ms);
				//echo "Inaikal 1: ".count($inaikal1)."\n\n";
				foreach($inaikal1 as $inai){
					//$inaikal[] = $inai;
					//echo "\nKootukal:".count($this->kootukal($inai))."\n";
					foreach($this->kootukal($inai) as $kootu){
						if($kootu != "" && !in_array($kootu,$inaikal))
							$inaikal[] = $kootu;
					}
				}
			}
			//if(count($kootukal)>0) $inaikal = array_merge($inaikal,$kootukal);
			if(count($inaikal)<=1)
				continue;

			for($i=0;$i<count($inaikal);$i++){
				$inaikal[$i] = $this->con->iits2uni($inaikal[$i]);
			}
				
			if(!in_array($sol,$inaikal))
				$inaikal[] = $sol;
				
			for($i=0;$i<count($inaikal);$i++){
				if($inaikal[$i]!=null && $inaikal[$i] != "")
					$thodarkal[] = str_replace($sol,$inaikal[$i],$thodar);
			}
		}
		return $thodarkal;
	}
	
	
	function inaithodar($thodar){
		$pThodar = "";
		foreach(explode(" ",$thodar) as $sol){
			//echo "Sol-$sol";
			$inaikal = $this->inaikal($sol);
			$ms = $this->moolasol($sol);
			if($ms != null){
				$inaikal1 = $this->inaikal($ms);
				//echo "Inaikal 1: ".count($inaikal1)."\n\n";
				foreach($inaikal1 as $inai){
					//$inaikal[] = $inai;
					//echo "\nKootukal:".count($this->kootukal($inai))."\n";
					foreach($this->kootukal($inai) as $kootu){
						if($kootu != "")
							$inaikal[] = $kootu;
					}
				}
			}
			//if(count($kootukal)>0) $inaikal = array_merge($inaikal,$kootukal);
			$inaikal[] = $sol;
			for($i=0;$i<count($inaikal);$i++){
				$inaikal[$i] = $this->con->iits2uni($inaikal[$i]);
			}
			
			$cnt = count($inaikal);
			if($cnt>0){
				$pThodar .= ($pThodar==""?"":" ");
				
				//if($cnt>1)
				//	$pThodar .= "(".implode(" OR ",$inaikal).")";
				//else 
				$pThodar .= $inaikal[0];
			}
		}
		return $pThodar;
	}
	
	function inaikal($sol){
		$uni=false;
		$inaikal = array();
		if(ord($sol)>127){
			$uni=true;
			$sol = $this->con->uni2iits($sol);
		}
		//echo "Sol:".$sol."\n";
		if(array_key_exists($sol,$this->solEnkal)){
			$en = $this->solEnkal[$sol];
			$s = $this->sorkal[$en];
			$cnt=0;
			foreach($s->uravukal as $uravu){
				if($cnt>3) break;
				if($uravu->vagai == "s"){
					$sol1 = $this->sorkal[$uravu->solEn]->patham;
					if($uni) $sol1 = $this->con->iits2uni($sol1);
					$inaikal[] = str_replace("_"," ",$sol1);
				}
				$cnt++;
			}
		}
		return $inaikal;
	}
	
	function moolasol($sol){
		if(ord($sol)>127){
			$sol = $this->con->uni2iits($sol);
		}
		return $this->moolaSorkal[$sol];
	}
	
	function kootukal($sol){
		$uni=false;
		$kootukal = array();
		if(ord($sol)>127){
			$uni=true;
			$sol = $this->con->uni2iits($sol);
		}
		//echo "Sol:".$sol."\n";
		if(array_key_exists($sol,$this->kootuSorkal))
		{
			foreach($this->kootuSorkal[$sol] as $ks){
				$kootukal[] = str_replace("_"," ",$ks);
			}
		}
		return $kootukal;
	}
	
	function etru($koppu){
		$handle = fopen($koppu, "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				$l = preg_split('/\t/',preg_replace("/\r|\n/","",$line),-1);
				if(count($l)>3){
					$sol = new Sol(["en"=>$l[0],"patham"=>$l[1],"vagai"=>$l[2],"uravEn"=>$l[3]]);
					$u = explode(",",$l[4]);
					foreach($u as $solEn){
						if(substr($solEn,-1)=="h"){
							$solEn = substr($solEn,0,-1);
							$vagai = "h";
						}else{
							$vagai = "s";
						}
						$sol->uravukal[] = new Uravu(["solEn"=>$solEn,"vagai"=>$vagai]);
					}
					$this->sorkal[$l[0]] = $sol;
					$this->solEnkal[$l[1]] = $l[0];
				}
			}
			fclose($handle);
		} else {
			// error opening the file.
		}
		
	}
	
	function kts_etru($koppu){
		$handle = fopen($koppu, "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				$l = preg_split('/\t/',preg_replace("/\r|\n/","",$line),-1);
				if(count($l)>1){
					if(array_key_exists($l[1],$this->kootuSorkal))
						$this->kootuSorkal[$l[1]][] = $l[0];
					else
						$this->kootuSorkal[$l[1]] = array($l[0]);
						
					$this->moolaSorkal[$l[0]] = $l[1];
				}
			}
			fclose($handle);
		} else {
			// error opening the file.
		}
	}
}

class Sol extends Base {
	var $en;
	var $patham;
	var $vagai;
	var $uravEn;
	var $uravukal = array();
}

class Uravu extends Base {
	var $solEn;
	var $vagai;
}

class Converter{

	var $uni = ["அ","ஆ","இ","ஈ","உ","ஊ","எ","ஏ","ஐ","ஒ","ஓ","ஔ","ஃ",
			"க","கா","கி","கீ","கு","கூ","கெ","கே","கை","கொ","கோ","கௌ","க்",
			"ங","ஙா","ஙி","ஙீ","ஙு","ஙூ","ஙெ","ஙே","ஙை","ஙொ","ஙோ","ஙௌ","ங்",
			"ச","சா","சி","சீ","சு","சூ","செ","சே","சை","சொ","சோ","சௌ","ச்",
			"ஞ","ஞா","ஞி","ஞீ","ஞு","ஞூ","ஞெ","ஞே","ஞை","ஞொ","ஞோ","ஞௌ","ஞ்",
			"ட","டா","டி","டீ","டு","டூ","டெ","டே","டை","டொ","டோ","டௌ","ட்",
			"ண","ணா","ணி","ணீ","ணு","ணூ","ணெ","ணே","ணை","ணொ","ணோ","ணௌ","ண்",
			"த","தா","தி","தீ","து","தூ","தெ","தே","தை","தொ","தோ","தௌ","த்",
			"ந","நா","நி","நீ","நு","நூ","நெ","நே","நை","நொ","நோ","நௌ","ந்",
			"ப","பா","பி","பீ","பு","பூ","பெ","பே","பை","பொ","போ","பௌ","ப்",
			"ம","மா","மி","மீ","மு","மூ","மெ","மே","மை","மொ","மோ","மௌ","ம்",
			"ய","யா","யி","யீ","யு","யூ","யெ","யே","யை","யொ","யோ","யௌ","ய்",
			"ர","ரா","ரி","ரீ","ரு","ரூ","ரெ","ரே","ரை","ரொ","ரோ","ரௌ","ர்",
			"ல","லா","லி","லீ","லு","லூ","லெ","லே","லை","லொ","லோ","லௌ","ல்",
			"வ","வா","வி","வீ","வு","வூ","வெ","வே","வை","வொ","வோ","வௌ","வ்",
			"ழ","ழா","ழி","ழீ","ழு","ழூ","ழெ","ழே","ழை","ழொ","ழோ","ழௌ","ழ்",
			"ள","ளா","ளி","ளீ","ளு","ளூ","ளெ","ளே","ளை","ளொ","ளோ","ளௌ","ள்",
			"ற","றா","றி","றீ","று","றூ","றெ","றே","றை","றொ","றோ","றௌ","ற்",
			"ன","னா","னி","னீ","னு","னூ","னெ","னே","னை","னொ","னோ","னௌ","ன்",
			"ஷ","ஷா","ஷி","ஷீ","ஷு","ஷூ","ஷெ","ஷே","ஷை","ஷொ","ஷோ","ஷௌ","ஷ்",
			"ஸ","ஸா","ஸி","ஸீ","ஸு","ஸூ","ஸெ","ஸே","ஸை","ஸொ","ஸோ","ஸௌ","ஸ்",
			"ஜ","ஜா","ஜி","ஜீ","ஜு","ஜூ","ஜெ","ஜே","ஜை","ஜொ","ஜோ","ஜௌ","ஜ்",
			"ஹ","ஹா","ஹி","ஹீ","ஹு","ஹூ","ஹெ","ஹே","ஹை","ஹொ","ஹோ","ஹௌ","ஹ்",
			"க்ஷ","க்ஷா","க்ஷி","க்ஷீ","க்ஷு","க்ஷூ","க்ஷெ","க்ஷே","க்ஷை","க்ஷொ","க்ஷோ","க்ஷௌ","க்ஷ்",
			"ஸ்ர"];
	
	var $iits = ["a","aa","i","ii","u","uu","e","ee","ai","o","oo","au",
		"q","ka","kaa","ki","kii","ku","kuu","ke","kee","kai","ko","koo","kau",
		"k","nga","ngaa","ngi","ngii","ngu","nguu","nge","ngee","ngai","ngo",
		"ngoo","ngau","ng","ca","caa","ci","cii","cu","cuu","ce","cee","cai",
		"co","coo","cau","c","nja","njaa","nji","njii","nju","njuu","nje","njee",
		"njai","njo","njoo","njau","nj",
		"Ta","Taa","Ti","Tii","Tu","Tuu","Te","Tee","Tai","To","Too","Tau","T",
		"Na","Naa","Ni","Nii","Nu","Nuu","Ne","Nee","Nai","No","Noo","Nau",
		"N","ta","taa","ti","tii","tu","tuu","te","tee","tai","to","too","tau","t",
		"nda","ndaa","ndi","ndii","ndu","nduu","nde","ndee","ndai","ndo","ndoo",
		"ndau","nd",
		"pa","paa","pi","pii","pu","puu","pe","pee","pai","po","poo","pau","p",
		"ma","maa","mi","mii","mu","muu","me","mee","mai","mo","moo","mau","m",
		"ya","yaa","yi","yii","yu","yuu","ye","yee","yai","yo","yoo","yau","y",
		"ra","raa","ri","rii","ru","ruu","re","ree","rai","ro","roo","rau","r",
		"la","laa","li","lii","lu","luu","le","lee","lai","lo","loo","lau","l",
		"va","vaa","vi","vii","vu","vuu","ve","vee","vai","vo","voo","vau","v",
		"zha","zhaa","zhi","zhii","zhu","zhuu","zhe","zhee","zhai","zho","zhoo",
		"zhau","zh",
		"La","Laa","Li","Lii","Lu","Luu","Le","Lee","Lai","Lo","Loo","Lau","L",
		"Ra","Raa","Ri","Rii","Ru","Ruu","Re","Ree","Rai","Ro","Roo","Rau","R",
		"na","naa","ni","nii","nu","nuu","ne","nee","nai","no","noo","nau","n",
		"Sha","Shaa","Shi","Shii","Shu","Shuu","She","Shee","Shai","Sho","Shoo",
		"Shau","Sh",
		"sa","saa","si","sii","su","suu","se","see","sai","so","soo","sau","s",
		"ja","jaa","ji","jii","ju","juu","je","jee","jai","jo","joo","jau","j",
		"ha","haa","hi","hii","hu","huu","he","hee","hai","ho","hoo","hau","h",
		"xa","xA","xi","xii","xu","xuu","xe","xee","xai","xo","xoo","xau","x",
		"Sra"];
	
	var $iitk = ["a","A","i","I","u","U","eV","e","E","oV","o","O","H",
	"ka","kA","ki","kI","ku","kU","keV","ke","kE","koV","ko","kO","k","fa",
	"fA","fi","fI","fu","fU","feV","fe","fE","foV","fo","fO","f","ca",
	"cA","ci","cI","cu","cU","ceV","ce","cE","coV","co","cO","c","Fa",
	"FA","Fi","FI","Fu","FU","FeV","Fe","FE","FoV","Fo","FO","F","ta",
	"tA","ti","tI","tu","tU","teV","te","tE","toV","to","tO","t","Na",
	"NA","Ni","NI","Nu","NU","NeV","Ne","NE","NoV","No","NO","N","wa",
	"wA","wi","wI","wu","wU","weV","we","wE","woV","wo","wO","w","na",
	"nA","ni","nI","nu","nU","neV","ne","nE","noV","no","nO","n","pa",
	"pA","pi","pI","pu","pU","peV","pe","pE","poV","po","pO","p","ma",
	"mA","mi","mI","mu","mU","meV","me","mE","moV","mo","mO","m","ya",
	"yA","yi","yI","yu","yU","yeV","ye","yE","yoV","yo","yO","y","ra",
	"rA","ri","rI","ru","rU","reV","re","rE","roV","ro","rO","r","la",
	"lA","li","lI","lu","lU","leV","le","lE","loV","lo","lO","l","va",
	"vA","vi","vI","vu","vU","veV","ve","vE","voV","vo","vO","v","lYYa",
	"lYYA","lYYi","lYYI","lYYu","lYYU","lYYeV","lYYe","lYYE","lYYoV",
	"lYYo","lYYO","lYY","lYa","lYA","lYi","lYI","lYu","lYU","lYeV","lYe",
	"lYE","lYoV","lYo","lYO","lY","rYa","rYA","rYi","rYI","rYu","rYU","rYeV",
	"rYe","rYE","rYoV","rYo","rYO","rY","nYa","nYA","nYi","nYI","nYu","nYU",
	"nYeV","nYe","nYE","nYoV","nYo","nYO","nY","Ra","RA","Ri","RI","Ru","RU",
	"ReV","Re","RE","RoV","Ro","RO","R","sa","sA","si","sI","su","sU","seV",
	"se","sE","soV","so","sO","s","ja","jA","ji","jI","ju","jU","jeV","je",
	"jE","joV","jo","jO","j","ha","hA","hi","hI","hu","hU","heV","he","hE",
	"hoV","ho","hO","h","KRa","KRA","KRi","KRI","KRu","KRU","KReV","KRe",
	"KRE","KRoV","KRo","KRO","KR","Sra"];
	
	var $iscii = ["€","¥","Š","§","š","©","«","¬","­","¯","°",
	"±","£",
	"³","³Ú","³Û","³Ü","³Ý","³Þ","³à","³á","³â","³ä","³å","³æ",
	"³è",
	"·","·Ú","·Û","·Ü","·Ý","·Þ","·à","·á","·â","·ä","·å","·æ",
	"·è",
	"ž","žÚ","žÛ","žÜ","žÝ","žÞ","žà","žá","žâ","žä","žå","žæ","žè",
	"Œ","ŒÚ","ŒÛ","ŒÜ","ŒÝ","ŒÞ","Œà","Œá","Œâ","Œä","Œå","Œæ",
	"Œè",
	"œ","œÚ","œÛ","œÜ","œÝ","œÞ","œà","œá","œâ","œä","œå","œæ","œè",
	"Á","ÁÚ","ÁÛ","ÁÜ","ÁÝ","ÁÞ","Áà","Áá","Áâ","Áä",
	"Áå","Áæ","Áè",
	"Â","ÂÚ","ÂÛ","ÂÜ","ÂÝ","ÂÞ","Âà","Âá","Ââ","Âä","Âå","Âæ",
	"Âè",
	"Æ","ÆÚ","ÆÛ","ÆÜ","ÆÝ","ÆÞ","Æà","Æá","Æâ","Æä","Æå","Ææ","Æè",
	"È","ÈÚ","ÈÛ","ÈÜ","ÈÝ","ÈÞ","Èà","Èá","Èâ","Èä","Èå","Èæ","Èè",
	"Ì","ÌÚ","ÌÛ","ÌÜ","ÌÝ","ÌÞ","Ìà","Ìá","Ìâ","Ìä","Ìå","Ìæ",
	"Ìè",
	"Í","ÍÚ","ÍÛ","ÍÜ","ÍÝ","ÍÞ","Íà","Íá","Íâ","Íä","Íå","Íæ",
	"Íè",
	"Ï","ÏÚ","ÏÛ","ÏÜ","ÏÝ","ÏÞ","Ïà","Ïá","Ïâ","Ïä","Ïå","Ïæ","Ïè",
	"Ñ","ÑÚ","ÑÛ","ÑÜ","ÑÝ","ÑÞ","Ñà","Ñá","Ñâ","Ñä","Ñå","Ñæ",
	"Ñè",
	"Ô","ÔÚ","ÔÛ","ÔÜ","ÔÝ","ÔÞ","Ôà","Ôá","Ôâ","Ôä","Ôå","Ôæ",
	"Ôè",
	"Ó","ÓÚ","ÓÛ","ÓÜ","ÓÝ","ÓÞ","Óà","Óá","Óâ","Óä","Óå","Óæ",
	"Óè",
	"Ò","ÒÚ","ÒÛ","ÒÜ","ÒÝ","ÒÞ","Òà","Òá","Òâ","Òä","Òå",
	"Òæ","Òè",
	"Ð","ÐÚ","ÐÛ","ÐÜ","ÐÝ","ÐÞ","Ðà","Ðá","Ðâ","Ðä","Ðå","Ðæ",
	"Ðè",
	"Ç","ÇÚ","ÇÛ","ÇÜ","ÇÝ","ÇÞ","Çà","Çá","Çâ","Çä","Çå",
	"Çæ","Çè",
	"Ö","ÖÚ","ÖÛ","ÖÜ","ÖÝ","ÖÞ","Öà","Öá","Öâ","Öä","Öå",
	"Öæ","Öè",
	"×","×Ú","×Û","×Ü","×Ý","×Þ","×à","×á","×â","×ä","×å",
	"×æ","×è",
	"º","ºÚ","ºÛ","ºÜ","ºÝ","ºÞ","ºà","ºá","ºâ","ºä","ºå","ºæ",
	"ºè",
	"Ø","ØÚ","ØÛ","ØÜ","ØÝ","ØÞ","Øà","Øá","Øâ","Øä","Øå",
	"Øæ","Øè",
	"³èÖ","³èÖÚ","³èÖÛ","³èÖÜ","³èÖÝ","³èÖÞ","³èÖà","³èÖá","³èÖâ",
	"³èÖä","³èÖå","³èÖæ","³èÖè",
	"ÕèÏ"];
	
	var $tab = ["Ü","Ý","Þ","ß","à","á","â","ã","ä","å","æ","å÷","ç",
	"è","è£","è€","èŠ","°","Ã","ªè","«è","¬è","ªè£","«è£","ªè÷","è¢" ,
	"é","é£","é€","éŠ","±","Ä","ªé","«é","¬é","ªé£","«é£","ªé÷","é¢",
	"ê","ê£","ê€","êŠ","²","Å","ªê","«ê","¬ê","ªê£","«ê£","ªê÷","ê¢",
	"ë","ë£","ë€","ëŠ","³","Æ","ªë","«ë","¬ë","ªë£","«ë£","ªë÷","ë¢",
	"ì","ì£","®","¯","Ž","Ç","ªì","«ì","¬ì","ªì£","«ì£","ªì÷","ì¢" ,
	"í","í£","í€","íŠ","µ","È","ªí","«í","¬í","ªí£","«í£","ªí÷","í¢" ,
	"î","î£","î€","îŠ","¶","É","ªî","«î","¬î","ªî£","«î£","ªî÷","î¢" ,
	"ï","ï£","ï€","ïŠ","ž","Ë","ªï","«ï","¬ï","ªï£","«ï£","ªï÷","ï¢",
	"ð","ð£","ð€","ðŠ","¹","Ì","ªð","«ð","¬ð","ªð£","«ð£","ªð÷","ð¢",
	"ñ","ñ£","ñ€","ñŠ","º","Í","ªñ","«ñ","¬ñ","ªñ£","«ñ£","ªñ÷","ñ¢" ,
	"ò","ò£","ò€","òŠ","»","Î","ªò","«ò","¬ò","ªò£","«ò£","ªò÷","ò¢",
	"ó","ó£","ó€","óŠ","Œ","Ï","ªó","«ó","¬ó","ªó£","«ó£","ªó÷","ó¢",
	"ô","ô£","ô€","ôŠ","œ","Ö","ªô","«ô","¬ô","ªô£","«ô£","ªô÷","ô¢" ,
	"õ","õ£","õ€","õŠ","Ÿ","×","ªõ","«õ","¬õ","ªõ£","«õ£","ªõ÷","õ¢" ,
	"ö","ö£","ö€","öŠ","¿","Ø","ªö","«ö","¬ö","ªö£","«ö£","ªö÷","ö¢",
	"÷","÷£","÷€","÷Š","À","Ù","ª÷","«÷","¬÷","ª÷£","«÷£","ª÷÷","÷¢" ,
	"ø","ø£","ø€","øŠ","Á","Ú","ªø","«ø","¬ø","ªø£","«ø£","ªø÷","ø¢" ,
	"ù","ù£","ù€","ùŠ","Â","Û","ªù","«ù","¬ù","ªù£","«ù£","ªù÷","ù¢" ,
	"û","û£","û€","ûŠ","û§","ûš","ªû","«û","¬û","ªû£","«û£","ªû÷","û¢" ,
	"ú","ú£","ú€","úŠ","ú§","úš","ªú","«ú","¬ú","ªú£","«ú£","ªú÷","ú¢",
	"ü","ü£","ü€","üŠ","ü§","üš","ªü","«ü","¬ü","ªü£","«ü£","ªü÷","ü¢" ,
	"ý","ý£","ý€","ýŠ","ý§","ýš","ªý","«ý","¬ý","ªý£","«ý£","ªý÷","ý¢" ,
	"þ","þ£","þ€","þŠ","þ§","þš","ªþ","«þ","¬þ","ªþ£","«þ£","ªþ÷","þ¢" ,
	"ÿ"];

	function iits2uni($text){
		return $this->convert($text,$this->iits,$this->uni);
	}
	
	function uni2iits($text){
		return $this->convert($text,$this->uni,$this->iits);
	}
	
	function convert($text, $from, $to) {
		$out = "";
		$l = strlen ( $text );
		$i = 0;
		while ( $i < $l ) {
			for($k = ($l - $i > 6 ? 6 : $l - $i); $k > 0; $k --) {
				$str = substr ( $text, $i, $k );
				$key = array_search ( $str, $from );
				if ($key !== false) {
					break;
				}
			}

			if($key===false || $k==0){
				$out .= substr($text,$i,1);
				$i ++;
			}else{
				$out .= $to [$key];
				$i += $k;
			}
		}
		return $out;
	}
}

?>

