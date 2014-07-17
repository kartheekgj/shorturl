<?php

require('./config.php');

$args = explode("/",$_SERVER['REQUEST_URI']);

$p1 = trim($args[1]) ;
$p3 = trim($args[3]) ;

$sc	=	'';
$message	=	'';
$sc	=	trim($args[(count($args)-1)]);


if(empty($sc))
	$message = 'No Short url found with this shortcode';
else
{
	
	$m = new MongoClient($conn_url);
	$db = $m->selectDB(DBNAME);
	$collection = new MongoCollection($db, COLLECTION_NAME);
	if( ! $collection)
	{
		echo 'Unable to connect to Database';
		exit;
	}
		
	$search = ['sc'=> $sc];
	$data = $collection->find($search);
	
	$i = 0;
	foreach ($data as $doc) {
		$i++;
		$data_coll[$i]['longUrl'] = $doc['lu'];
		$data_coll[$i]['shorUrl'] = $doc['sc'];
	}
	if(is_array($data_coll) && count($data_coll) > 0)
	{
		$blnLink = true;
		$message = '<p>The long URL for the ShortURL <strong> http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'</strong> is <a href="'.$data_coll[1]['longurl'].'">'.$data_coll[$i]['longUrl'].'</a></p>';
	}
	else 
		$message = 'No Short url found with this shortcode';
}
?>
<html>
	<head>
		<title>Redirection Page</title>
	</head>
	<body>
		<?php if($blnLink)?>
			Please click the Link in the message to redirect to the long url
		<?php 
		echo $message;
		?>	
		
	</body>
</html>
