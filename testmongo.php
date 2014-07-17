<?php

$args = explode("/",$_SERVER['REQUEST_URI']);

$p1 = trim($args[1]) ;
$p3 = trim($args[3]) ;

$sc	=	'';
$message	=	'';
$sc	=	trim($args[(count($args)-1)]);

$conn = 


if(empty($sc))
	$message = 'No Short url found with this shortcode';
else
{
	
	$m = new MongoClient("mongodb://kartheekgj:gjkgjk@ds029837.mongolab.com:29837/gj");
	$db = $m->selectDB('gj');
	$collection = new MongoCollection($db, 'shorturl');
	$search = ['sc'=> $sc];
	$data = $collection->find($search);
	
	$i = 0;
	foreach ($data as $doc) {
		$i++;
		$data_coll[$i]['longUrl'] = $doc['lu'];
		$data_coll[$i]['shorUrl'] = $doc['sc'];
	}
	print_r($data_coll);
	if(is_array($data_coll) && count($data_coll) > 0)
	{
		$blnLink = true;
		$message = '<p>The long URL for the ShortCode <strong>'. $data_coll[$i]['shorUrl'] .'</strong> is <a href="'.$data_coll[1]['longurl'].'">'.$data_coll[$i]['longUrl'].'</a></p>';
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
