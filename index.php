<?php
header('Content-type: text/html; charset=utf-8');
session_start ();
require_once 'Zend/Oauth/Consumer.php';
 
// Set OAuth configuration
$config = array(
	'callbackUrl' => 'http://labs.jera.com.br/kactoos-ff/index.php?access=true',
	'siteUrl' => 'http://api.kactoos.com/api/oauth/request_token',
	'requestScheme' => Zend_Oauth::REQUEST_SCHEME_QUERYSTRING,
	'requestMethod' => 'GET',
	'consumerKey' => 'ef80683dfc5c96df6cdfc9421886748604e21ecb2',
	'consumerSecret' => 'b99e142e93990f0e8ddc1a313eb57602',
	"requestTokenUrl" => 'http://api.kactoos.com/api/oauth/request_token',
	"accessTokenUrl" => 'http://api.kactoos.com/api/oauth/access_token',
	'userAuthorizationUrl' => 'http://api.kactoos.com/api/oauth/authorize'
);
 
 // Create a consumer Object
$consumer = new Zend_Oauth_Consumer( $config );
 
 // Get access value
$access = $_GET["access"];
 
 // If user is not authenticated
if( $access != "true" ) {
	$token = $consumer->getRequestToken();
	$_SESSION['kactoos_token'] = serialize( $token );
	$consumer->redirect();
	exit();
}
 
 // Get access Token
if( isset ( $_SESSION["kactoos_token"] ) ) {
	$token = $consumer->getAccessToken($_GET, unserialize($_SESSION['kactoos_token']));
	$_SESSION['kactoos_access_token'] = serialize( $token );
	unset( $_SESSION["kactoos_token"] );
}
 
 // If user is authenticated
if( isset ( $_SESSION['kactoos_access_token'] ) ) {
	$token = unserialize( $_SESSION['kactoos_access_token'] );
	$client = $token->getHttpClient( $config );
	 
	 // Get Favorites
	$client->setUri('http://api.kactoos.com/api/users/get-my-favorites/format/json');
	$client->setMethod( Zend_Http_Client::POST );
	$response = $client->request();
	$data = json_decode( $response->getBody() );
	$result = $response->getBody();
	//echo $result;
	echo '<pre>'; print_r($data); echo '</pre>';
	?>
	<body>
		<img src="img_logo_whiteh.png" />
		<div style="display: block; padding-top:42px;">
		<?	foreach ($data->products as $product) { ?>
			<div style="display: block;">
				<span class="text price"><a href="<?= $product->url ?>"><?= substr($product->product_name, 0, 20); ?></a></span>
				<span class="text price">Preço inicial: R$ <?= number_format($product->initial_price, 2, ',', '.'); ?></span>
				<span class="text price">Preço atual: R$ <?= number_format($product->actual_price, 2, ',', '.'); ?></span>
				<span class="text price">Menor possível: R$ <?= number_format($product->api_max_discount, 2, ',', '.'); ?></span>
				<? if ($product->actual_price == $product->api_max_discount) { ?><label class="text alert">(MENOR PREÇO POSSÍVEL)</label> <? } ?>
				</span>
				<span class="text">Pessoas comprando: <?= $product->api_usuarios ?></span>
				<span class="text">Produtos restantes: <?= ((int)$product->stock-(int)$product->api_usuarios); ?></span>
			</div><br><br>
		<? } ?>
	</div>
	</body>
	<?
}
?>
<style>
	body {
		margin:0;
		font-family: "Helvetica";
		font-size:12px;
		color:#959595;
		background-image: url( "bg_header.jpg" );
		background-position: top left;
		background-repeat: repeat-x;
	}
	
	.price {
		font-weight: bold;
		iolor: #69B712 !important;
	}

	.alert {
		font-size: 14px;
		font-weight: bold;
		color:#f28432 !important;
	}
	
	.text {
		font-size: 12px;	
		margin-left: 14px;		
		color: #666;
		display: block;		
	}
	
</style>
