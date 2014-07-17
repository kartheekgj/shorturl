<?php

require('./config.php');

$args = explode("/",$_SERVER['PHP_SELF']);

$const_shortUrl = 'http://'.$_SERVER['HTTP_HOST'].'/'.$args[1].'/'.$args[2].'/'.$args[3].'/testmongo.php';
$cmd			=	trim($_POST['cmd']);

switch($cmd)
{
	case 'convert'	:	fnURLConvert();
						break;
	case 'getData'	: 	fnGetData();
						break;
	default			:	echo json_encode(['error'=>1,'message'=>'Invalid command']);				
						break;
	
}


function _getConfig()
{
	
	try
	{
		global $conn_url;
		$m = new MongoClient($conn_url);
		$db = $m->selectDB(DBNAME);
		return new MongoCollection($db, COLLECTION_NAME);
	}
	catch(Exception $e)
	{
		return false;
		
	}
}

function fnURLConvert()
{
	$url = trim($_POST['url']);
	try
	{
		if( ! filter_var($url,FILTER_VALIDATE_URL))	
			throw new Exception('Invalid URL');
		if( ! _verifyUrlExists($url))
			throw new Exception('URL does not appear to exist.');
		else
			$response = _insertData($url);
	}
	catch(Exception $e)
	{
		echo json_encode(['error' => 1,'message'=> $e->getMessage()]);
	}
}

function _insertData($url)
{
	try
	{
		global $const_shortUrl;
		$collection = _getConfig();
		
		if( ! $collection)
			throw new Exception('Unable to connect to database');
		else
		{
			$shortUrlCode = _randomCode();
			$insert = ['lu' => $url , 'sc'=> $shortUrlCode];
			try {
				$resp	=	$collection->insert($insert);
				if($resp["ok"])
					echo json_encode(['error' => 0,'sc'=> $const_shortUrl.'/'.$shortUrlCode]);
				else
					throw new Exception('Unable to insert Data');
				
			} catch(MongoCursorException $e) {
				echo json_encode(['error' => 1,'message'=> $e->getMessage()]);
			}
		}	
	}
	catch(Exception $e)
	{
		echo json_encode(['error' => 1,'message'=> $e->getMessage()]);
	}
}

function _extractCode($Url) {
	$matches = array();
	preg_match("/[\\?&]v=([^&#]*)/", $Url, $matches);
	return $matches[1];
}

function _randomCode($length = 8) {
	$alphabets_big = range('A','Z');
	$numbers = range('0','9');
	$alphabets_small = range('a','z');
	$final_array = array_merge($alphabets_big,$numbers,$alphabets_small);
	
	$alphanumeric = '';
	
	while($length--) {
	  $key = array_rand($final_array);
	  $alphanumeric .= $final_array[$key];
	}
	
	if(_ExistsGetData($alphanumeric))
	{
		return $alphanumeric;
	}
	else
		_randomCode(); //worst case scenario
		
}
  
function _ExistsGetData($sc)
{
	try
	{
		$collection = _getConfig();
		if( ! $collection)
			throw new Exception('Unable to connect to database');
		else
		{
			$shortCode = ['sc' => $sc];
			$data = $collection->find($shortCode);
			foreach ($data as $i=>$doc) {
				$data_coll[] = $doc;
			}
			if( ! is_array($data_coll))
				return true;
			else 
				return false;
		}
	}
	catch(Exception $e)
	{
		echo json_encode(['error' => 1,'message'=> $e->getMessage()]);
	}
}

function _verifyUrlExists($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
	curl_exec($ch);
	$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	return (!empty($response) && $response != 404);
 }
 
 /* geting data */
 
function fnGetData($data = [])
{
	try
	{
		global $const_shortUrl;
		$collection = _getConfig();
		if( ! $collection)
			throw new Exception('Unable to connect to database');
		else
		{
			if( ! is_array($data))
				throw new Exception('Invalid data parsed.');
			else if( ! empty($data))
			{
				$sc = _extractCode($url);
				$shortCode = ['sc' => $sc];
				$data = $collection->find($shortCode);
				$i = 0;
				foreach ($data as $doc) {
					$i++;
					$data_coll[$i]['longUrl'] = $doc['lu'];
					$data_coll[$i]['shortUrl'] = $const_shortUrl.'/'.$doc['sc'];
				}
			}
			else
			{
				$data = $collection->find();
				$i = 0;
				foreach ($data as $doc) {
					$i++;
					$data_coll[$i]['longUrl'] = $doc['lu'];
					$data_coll[$i]['shortUrl'] = $const_shortUrl.'/'.$doc['sc'];
				}
			}
		}	
		echo json_encode(['error'=> 0,'dbdata'=> $data_coll]);
	}
	catch(Exception $e)
	{
		echo json_encode(['error' => 1,'message'=> $e->getMessage()]);
	}
}
