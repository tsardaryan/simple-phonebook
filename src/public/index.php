<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

session_start();

require '../vendor/autoload.php';
spl_autoload_register(function ($classname) {
    require ("../classes/" . $classname . ".php");
});

$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "root";
$config['db']['dbname'] = "phonebook";

$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();

$container['view'] = new \Slim\Views\PhpRenderer("../templates/");
$container['flash'] = new \Slim\Flash\Messages();
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$app->get('/', function (Request $request, Response $response) {
	$mapper = new UserMapper($this->db);
	
	$query = $request->getQueryParams();
	$q = null;
	if(isset($query['q']))
	{
		$q = filter_var($query['q'], FILTER_SANITIZE_STRING);
		$users = $mapper->search($q);
	}
	else
	{
		$users = $mapper->users();
	}
	$messages = $this->flash->getMessages();
	
    $response = $this->view->render($response, "users.phtml", ["q" => $q, "users" => $users, "router" => $this->router, "messages" => $messages]);
    return $response;
});

$app->get('/users/new', function (Request $request, Response $response) {
    $response = $this->view->render($response, "new_user.phtml");
    return $response;
});

$app->post('/users/new', function (Request $request, Response $response) {
	try
	{
		$data = $request->getParsedBody();
		$files = $request->getUploadedFiles();
		
		$user_data = [];
		$user_data['full_name'] = filter_var($data['full_name'], FILTER_SANITIZE_STRING);
		$user_data['phone_number'] = filter_var($data['phone_number'], FILTER_SANITIZE_STRING);
		$user_data['birthday'] = filter_var($data['birthday'], FILTER_SANITIZE_STRING);
		$user_data['address'] = filter_var($data['address'], FILTER_SANITIZE_STRING);
		
		$user_mapper = new UserMapper($this->db);
		$user = new UserEntity($user_data);
		
		if($user_mapper->isPhoneNumberExists($user->getId(), $user->getPhoneNumber()))
		{
			$this->flash->addMessage('error', 'Phone Number already exists');
		}
		else
		{
			$id_user = $user_mapper->add($user);
			if (!empty($files['avatar']))
			{
				$avatar = $files['avatar'];
				if($avatar->getError() === UPLOAD_ERR_OK)
				{
					if (!is_dir("images/"))
					{
						mkdir("images/");         
					}
					$avatar->moveTo("images/".$id_user);
				}
			}
			
			$this->flash->addMessage('success', 'User Added Successfully');
		}
	}
	catch(Exception $e)
	{
		$this->flash->addMessage('error', "Incorrect Values");
	}
	
	$response = $response->withRedirect("/");
    return $response;
});

$app->get('/users/{id}', function (Request $request, Response $response, $args) {
    $id_user = (int)$args['id'];
    $mapper = new UserMapper($this->db);
    $user = $mapper->user($id_user);
	
	$messages = $this->flash->getMessages();
	
    $response = $this->view->render($response, "user.phtml", ["user" => $user, "messages" => $messages]);
    return $response;
});

$app->put('/users/{id}', function (Request $request, Response $response, $args) {
	try
	{
		$id_user = (int)$args['id'];
		$data = $request->getParsedBody();
		$files = $request->getUploadedFiles();
		
		$user_data = [];
		$user_data['id'] = $id_user;
		$user_data['full_name'] = filter_var($data['full_name'], FILTER_SANITIZE_STRING);
		$user_data['phone_number'] = filter_var($data['phone_number'], FILTER_SANITIZE_STRING);
		$user_data['birthday'] = filter_var($data['birthday'], FILTER_SANITIZE_STRING);
		$user_data['address'] = filter_var($data['address'], FILTER_SANITIZE_STRING);
		
		$user_mapper = new UserMapper($this->db);
		$user = new UserEntity($user_data);
		
		if($user_mapper->isPhoneNumberExists($user->getId(), $user->getPhoneNumber()))
		{
			$this->flash->addMessage('error', 'Phone Number already exists');
		}
		else
		{
			$user_mapper->update($id_user, $user);
			
			if (!empty($files['avatar']))
			{
				$avatar = $files['avatar'];
				if($avatar->getError() === UPLOAD_ERR_OK)
				{
					if (!is_dir("images/"))
					{
						mkdir("images/");         
					}
					$avatar->moveTo("images/".$id_user);
				}
			}
			
			$this->flash->addMessage('success', 'User Updated Successfully');
		}
	}
	catch(Exception $e)
	{
		$this->flash->addMessage('error', "Incorrect Values");
	}
	
    $response = $response->withRedirect("/users/$id_user");
    return $response;
});

$app->delete('/users/{id}', function (Request $request, Response $response, $args) {
    try
	{
		$id_user = (int)$args['id'];
		$mapper = new UserMapper($this->db);
		$mapper->remove($id_user);
	}
	catch(Exception $e)
	{
		$this->flash->addMessage('error', "Incorrect Values");
	}
    $response = $response->withRedirect("/users/$id_user");
    return $response;
});

$app->run();