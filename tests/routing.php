<?php

use Bazo\Rest\App;
use Bazo\Rest\Methods;
use Bazo\Rest\Patterns;
use Bazo\Rest\Route;
use Bazo\Rest\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tester\Environment;
use Tester\TestCase;



require_once '../vendor/autoload.php';

Environment::setup();

class ClosureRoutingTest extends TestCase
{

	/** @var Router */
	private $router;
	private $baseDomain = 'http://example.com';
	private $callResult = NULL;


	function __construct()
	{
		$router = new Router;

		$router[] = new Route('/test/', [
			Methods::POST => function(Request $req, Response $res) {
		$this->callResult = 'POST';
	},
		]);

		$router[] = new Route(['/test/{id}', [
				'id' => Patterns::PATTERN_ALPHA,
			]], [

			Methods::GET => function(Request $req, Response $res) {
		$this->callResult = 'GET 1';
	},
			Methods::PATCH	 => function(Request $req, Response $res) {
		$this->callResult = 'PATCH 1';
	},
			Methods::DELETE => function(Request $req, Response $res) {
		$this->callResult = 'DELETE 1';
	},
		]);

		$this->router = $router;
	}


	public function setUp()
	{
		$this->callResult = NULL;
	}


	public function testPost()
	{
		$req = Request::create($this->baseDomain . '/test', Methods::POST);
		$app = new App(NULL, $req);

		$app->attach($this->router);

		$app->run();

		\Tester\Assert::equal('POST', $this->callResult);
	}


	public function testGet()
	{
		$req = Request::create($this->baseDomain . '/test/1', Methods::GET);
		$app = new App(NULL, $req);

		$app->attach($this->router);

		$app->run();

		\Tester\Assert::equal('GET 1', $this->callResult);
	}


	public function testPatch()
	{
		$req = Request::create($this->baseDomain . '/test/1', Methods::PATCH);
		$app = new App(NULL, $req);

		$app->attach($this->router);

		$app->run();

		\Tester\Assert::equal('PATCH 1', $this->callResult);
	}


	public function testDelete()
	{
		$req = Request::create($this->baseDomain . '/test/1', Methods::DELETE);
		$app = new App(NULL, $req);

		$app->attach($this->router);

		$app->run();

		\Tester\Assert::equal('DELETE 1', $this->callResult);
	}


	/*
	  public function testInvalidPath()
	  {
	  $req = Request::create($this->baseDomain . '/test', Methods::DELETE);
	  $app = new App(NULL, $req);

	  $app->attach($this->router);

	  ob_start();
	  $app->run();
	  $output = ob_get_clean();

	  $response = [
	  'error' => 'Invalid path'
	  ];
	  //$response = json_encode($response, JSON_PRETTY_PRINT);
	  //echo $response;
	  //echo $output;

	  var_dump($response);
	  var_dump($output);

	  \Tester\Assert::equal($response, json_decode($output));
	  } */
}

$test = new ClosureRoutingTest();
$test->run();
