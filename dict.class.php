<?php
/**
 *  dict.class.php 采集百度词典翻译内容
 *
 * @copyright           (C) 2014  widuu
 * @lastmodify          2014-2-15
 */
 
 
header("content-type:text/html;charset=utf8");
class Dict{

	private $word;
	
	//显示的条数
	private static $num = 10;

	private $db;

	public function __construct(){}
	
	
	/**
      * 公用返回百度采集数据的方法
      * @param string 英文单词
      * retun  array(
	  *				symbol" =>  音标
	  *				"pro"	 => 发音
	  *				"example"=> 例句
	  *				"explain"=> 简明释义
	  *				"synonym"=> 同反义词
	  *				"phrase" => 短语数组
	  *			)
      *
	  */
	
	public function content($word){
		 $this -> word = $word;
		 $symbol = $this -> Pronounced();
		 $pro	 = $this->getSay();
		 $example = $this -> getExample();
		 $explain = $this -> getExplain();
		 $synonym = $this -> getSynonym();
		 $phrase = $this -> getPhrase();
		 $result = array(
				"symbol" => $symbol,		//音标
				"pro"	 => $pro,			//发音
				"example"=> $example,		//例句
				"explain"=> $explain,		//简明释义
				"synonym"=> $synonym,		//同反义词
				"phrase" => $phrase 		//短语数组
			);
		return $result;
	}


	/**
      * 远程获取百度翻译内容
      * get function curl
      * retun string
      *
	  */

	private function getContent(){
 		$useragent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:23.0) Gecko/20100101 Firefox/23.0";
 		$ch = curl_init();
 		$url = "http://dict.baidu.com/s?wd=".$this->word;
 		curl_setopt($ch, CURLOPT_URL, $url);
 		curl_setopt($ch, CURLOPT_USERAGENT,$useragent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER,1);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$result = curl_exec($ch);
		if (curl_errno($curl)) {
			echo 'Errno'.curl_error($curl);
		}
		curl_close($ch);
		return $result;
	}


	/**
      * 获取百度翻译发音
      * retun array(英，美)
      *
	  */

	private function Pronounced(){
		$data = $this -> getContent();
		preg_match_all("/\"EN\-US\"\>(.*)\<\/b\>/Ui",$data,$pronounced);
		return array(
			'en' => $pronounced[1][0],
			'us' => $pronounced[1][1]
		);
	}

	/**
	  * 获取百度翻译发音
	  * return array(英，美)
	  *
	  */

	private function getSay(){
		$data = $this -> getContent();
		preg_match_all("/url=\"(.*)\"/Ui",$data,$pronounced);
		return array(
			'en' => $pronounced[1][0],
			'us' => $pronounced[1][1]
		);	
	}

	/**
      * 获取百度翻译例句
      * return array() 多维数组 例句
      * 
	  */

	private function getExample(){
		$str = "";
		$data = $this -> getContent();
		preg_match_all("/var example_data = (.*)\]\;/Us",$data,$example);
	    $data1 = "[[[".ltrim($example[1][0],"[");
	    $data2 = explode("[[[",$data1);
	    $num = count(array_filter($data2));
		foreach($data2 as $key => $value){
		 	$data3 = explode("[[","[[".$value);
		 	foreach ($data3 as $k => $v) {
		 		preg_match_all("/\[\"(.*)\",/Us","[".$v, $match);
		 		if(!empty($match[1])){
		 			$str .= implode($match[1]," ")."@";
		 		}
		 	}
		}
		$data4 = trim($str,"@");
		$data5  = explode("@", $data4);
		$result = array_chunk($data5, 2);
		return $result;
	}

	/**
      * 获取简明释义
      * return array (x => "词性"，b => "附属")
      * 
	 **/

	private function getExplain(){
		$data = $this -> getContent();
		preg_match_all("/id\=\"en\-simple\-means\"\>(.*)\<div(\s+)class\=\"source\"\>/Us",$data,$explain);
		$r_data =  $explain[1][0];
		preg_match_all("/\<p\>\<strong\>(?P<adj>.*)\<\/strong\>\<span\>(?P<name>.*)\<\/span\>\<\/p\>/Us", $r_data, $a_data);
		preg_match_all("/\<span\>(?P<tag>[^\>]+)\：\<a(\s+)href\=\"(.*)\"\>(?P<word>.*)\<\/a\>\<\/span\>/Us", $r_data, $b_data);
		
		$result = array();
		foreach ($a_data["adj"] as $key => $value) {
			$result[$value] = $a_data["name"][$key];
		}
		
		$word_b = array();
		foreach ($b_data["tag"] as $key => $value) {
			$word_b[$value] = strip_tags($b_data["word"][$key]);
		}
		
		$result_data = array("x" => $result,"b" => $word_b);

 		return $result_data;
	}




	/**
      * 获取百科释义
      * return string
      * 
	  */

	// private function getBaike(){
	// 	$data = $this -> getContent();
	// 	preg_match_all("/id\=\"en\-baike\-mean\"\>(.*)<\/div>/Us",$data,$baike);
    // 		return strip_tags($baike[1][0]);
	// }

	/**
      * 获取同义词
      * return array(0 => "同义词", 1 => "反义词") 一般为多维数组
      * 
	  */

	private function getSynonym(){
		$data = $this -> getContent();
		preg_match_all("/id=\"en\-syn\-ant\"\>(.*)<div(\s+)class\=\"source\">/Us",$data,$synonym);
		$content = $synonym[1][0];
		$data1 = explode("</dl>", $content);
		$result = array();
		$data2 = array();
		foreach ($data1 as $key => $value) {
			preg_match_all("/\<strong\>(?P<adj>.*)\&nbsp\;\<\/strong\>\<\/div\>\<div(\s+)class\=\"syn\-ant\-list\"\>\<ul\>(?<content>.*)\<\/ul\>/Us", $value, $r_data);
			$data2[$key]["adj"] = $r_data["adj"];
			$data2[$key]["content"] = $r_data["content"];
		}

		foreach ($data2 as $key => $value) {
			foreach ($value["content"] as $k => $v) {
				if(!empty($v)){
					preg_match_all("/\<li\>\<p\>(?P<title>.*)\<\/p\>(?P<value>.*)\<\/li>/Us", $v, $v_data);
					foreach ($v_data['title'] as $m => $d) {
						$data = strip_tags(preg_replace("<</a>>"," ", $v_data["value"][$m]));
						$result[$key][$value["adj"][$k]][$d] = $data;
					}
				}
			}
		}
 		return $result;
	}

	/**
      * 获取柯林斯高阶英汉词典
      * return string
      * 
	  */

	// private function getCollins(){
	// 	$data = $this -> getContent();
	// 	preg_match_all("/id\=\"en\-collins\"\>(.*)\<div class\=\"source\"\>/Us",$data,$collins);
    // 		return strip_tags($collins[1][0]);
	// }

	/**
      * 获取短语词组
      * return array (key => value) 一维或者多维数组
      * 
	  */

	private function getPhrase(){
		$num = self::$num;
		$data = $this -> getContent();
		preg_match_all("/id=\"en\-phrase\"\>(.*)\<div class\=\"source\"\>/Us",$data,$phrase);
		$data = explode("</dd>",$phrase[1][0]);
		$data1 = array_slice($data,0,$num);
		$result = array();
		foreach ($data1 as $key => $value) {
			$data2 = explode("</p>", $value);
			$n = count($data2);
			if($n<=3){
				$result[str_replace("&nbsp;","",strip_tags($data2[0]))] = strip_tags($data2[1]);
			}else{
				$data3 = array_slice($data2,0,$n-1);
				$data4 = array_slice($data2,0,2);
				$res = array_diff($data3,$data4);
				$data5 = array_chunk($res,2);
				$key_value = trim(str_replace("&nbsp;","",strip_tags($data4[0])));
				$result[$key_value] = strip_tags($data4[1]);
				foreach ($data5 as $key => $value) {
					foreach ($value as $k => $v) {
						$value[$k] = strip_tags($v);
					}
					$array = array($result[$key_value],$value);
					if (array_key_exists($key_value, $result)){
						$result[$key_value] = $array;
					}
				}
				
			}
		}
		return $result;
	}

	/**
	  * 将数组转换为字符串
	  *
	  * @param    array   $data       数组
	  * @param    bool    $isformdata 如果为0，则不使用new_stripslashes处理，可选参数，默认为1
	  * @return   string  返回字符串，如果，data为空，则返回空
	  */
	private function array2string($data, $isformdata = 1) {
	    if($data == '') return '';
	    if($isformdata) $data = $this->new_stripslashes($data);
	    return addslashes(var_export($data, TRUE));
	}

	/**
	 * 返回经stripslashes处理过的字符串或数组
	 * @param $string 需要处理的字符串或数组
	 * @return mixed
	 */
	private function new_stripslashes($string) {
	    if(!is_array($string)) return stripslashes($string);
	    foreach($string as $key => $val) $string[$key] = $this->new_stripslashes($val);
	    return $string;
	}

}

// $word = new dict("express");
// $word ->content();