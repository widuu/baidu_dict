<?php
/**
 *  dictdemo.php 	   百度翻译采集简单举例
 *
 * @copyright           (C) 2014  widuu
 * @license             http://www.widuu.com
 * @lastmodify          2014-2-15
 */

	/**
	  * 将数组转换为字符串
	  *
	  * @param    array   $data       数组
	  * @param    bool    $isformdata 如果为0，则不使用new_stripslashes处理，可选参数，默认为1
	  * @return   string  返回字符串，如果，data为空，则返回空
	  */
	function array2string($data, $isformdata = 1) {
	    if($data == '') return '';
	    if($isformdata) $data = new_stripslashes($data);
	    return addslashes(var_export($data, TRUE));
	}

	/**
	 * 返回经stripslashes处理过的字符串或数组
	 * @param $string 需要处理的字符串或数组
	 * @return mixed
	 */
	function new_stripslashes($string) {
	    if(!is_array($string)) return stripslashes($string);
	    foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
	    return $string;
	}
 
 	$dbname = "dict_word";
 	$hostname = "localhost";
 	$username = "root";
 	$password = "dgj99349";
	$conn = mysql_connect($hostname,$username,$password);
	mysql_select_db($dbname,$conn);
	mysql_query("set names utf8"); 
	$url = "http://www.test.com/";	//你放dictdemo.php的网站地址
	ignore_user_abort();
	set_time_limit(0);
	$filename = "./dict_num.txt";
	include("dict.class.php");
	$data = include("word_data.php");
	$dict = new dict();
	if (isset($_GET["num"])){
		$key = intval($_GET["num"]); 
		$word = $data[$key];
		$result = $dict -> content($word);
		if(empty($result["symbol"]["en"])){
		  	$result = file_put_contents("noword.txt", $word."\n", FILE_APPEND); //有的个性的单词啥都没有 写入文件中
		 }else{
		 	$symbol = array2string(array_filter($result['symbol']));
		 	$pro = array2string(array_filter($result['pro']));
		 	$example = array2string(array_filter($result['example']));
		 	$explain = array2string(array_filter($result['explain']));
		 	$synonym = array2string(array_filter($result['synonym']));
		 	$phrase = array2string(array_filter($result['phrase']));
		 	echo "<pre>";
		 	print_r($symbol);
		 	print_r($pro);
		 	print_r($example);
		 	print_r($explain);
		 	print_r($synonym);
		 	print_r($phrase);
		 	echo "</pre>";
		 	//mysql_query("insert into dict_word (`word`,`symbol`,`pro`,`example`,`explain`,`synonym`,`phrase`) values ('{$word}')")

		 	 	//your 逻辑采集入库
		 }
		$num = $key+1;
		file_put_contents($filename,$num);
	}

	if(file_exists($filename)){
		$key = file_get_contents($filename);
	}else{
		$fp =fopen("$filename", "w+");
	}
	
	$key = empty($key) ? 0 : intval($key);

	echo "<script language='javascript' type='text/javascript'>";  
	echo "window.location.href='{$url}dictdemo.php?num={$key}'";  
	echo "</script>";
	


	