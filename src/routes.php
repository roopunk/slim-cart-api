<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// Routes

$app->get('/test', function(Request $request, Response $response, $args) {
	$res = $this->db->query("select * from users");
	// $res = $st->execute();
	var_dump($res->fetch()); exit;
});

// add an item to the cart
// POST "/cart/{user_id}/add" and POST data as the item details
$app->post('/cart/{user_id}/add', function(Request $request, Response $response, $args) {
	$user_id = (int)$args['user_id'];
	if(empty($user_id))
		return $response->withJson(['error' => 'Invalid request'], 400);

	$request_body = $request->getParsedBody();
	$item_id = isset($request_body['item_id']) ? $request_body['item_id'] : null;
	$item_quanity = isset($request_body['quantity']) ? (int)$request_body['quantity'] : 1;
	$item = new ItemModel($this->db);
	$item->init($item_id);
	if(empty($item->getDetails()))
		return $response->withJson(['error' => 'Invalid request'], 400);

	$cart = new CartModel($this->db);
	$status = $cart->addToCart($user_id, $item_id, $item_quanity);

	return $status ? $response->withJson(['success' => true]) : $response->withJson(['error' => "something went wrong"], 500);
});

// removing the item from the cart
// DELETE "/cart/{user_id}/remove" item id in the body
$app->post('/cart/{user_id}/remove', function(Request $request, Response $response, $args) {
	$user_id = (int)$args['user_id'];
	if(empty($user_id))
		return $response->withJson(['error' => 'Invalid request'], 400);

	$request_body = $request->getParsedBody();
	$item_id = isset($request_body['item_id']) ? $request_body['item_id'] : null;
	$item = new ItemModel($this->db);
	$item->init($item_id);
	if(empty($item->getDetails()))
		return $response->withJson(['error' => 'Invalid request'], 400);

	$cart = new CartModel($this->db);
	$status = $cart->remvoveFromCart($user_id, $item_id);

	return $status ? $response->withJson(['success' => true]) : $response->withJson(['error' => "something went wrong"], 500);
});

// updating the quantity of an item in the cart
// PUT "cart/{user_id}/update" item id and new quantity in the body
$app->post('/cart/{user_id}/update', function(Request $request, Response $response, $args) {
	$user_id = (int)$args['user_id'];
	if(empty($user_id))
		return $response->withJson(['error' => 'Invalid request'], 400);

	$request_body = $request->getParsedBody();
	$item_id = isset($request_body['item_id']) ? (int)$request_body['item_id'] : null;
	$item_quanity = !empty($request_body['quantity']) ? (int)$request_body['quantity'] : null;
	$item = new ItemModel($this->db);
	$item->init($item_id);
	if(empty($item->getDetails()) || empty($item_quanity))
		return $response->withJson(['error' => 'Invalid request'], 400);

	$cart = new CartModel($this->db);
	$status = $cart->modifyCart($user_id, $item_id, $item_quanity);

	return $status ? $response->withJson(['success' => true]) : $response->withJson(['error' => "something went wrong"], 500);
});

// getting all the items in the cart
// GET "cart/{user_id}/get/items"
$app->get('/cart/{user_id}/get/items', function(Request $request, Response $response, $args) {
	$user_id = (int)$args['user_id'];
	if(empty($user_id))
		return $response->withJson(['error' => 'Invalid request'], 400);

	$cart = new CartModel($this->db);
	$items = $cart->getItems($user_id, 10);

	return $response->withJson(['items' => $items]);
});

// getting user information - billing address from the cart
// GET "cart/{user_id}/get/user"
$app->get('/cart/{user_id}/get/user', function(Request $request, Response $response, $args) {
	$user_id = (int)$args['user_id'];
	if(empty($user_id))
		return $response->withJson(['error' => 'Invalid request'], 400);

  $user = new UserModel($this->db);
  $user->init($user_id);

  $user_details = $user->getDetails();
	$response = $response->withJson($user_details);
	return $response;
});
