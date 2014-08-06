<?php

namespace Bazo\Rest;

use Bazo\Rest\Middleware\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



/**
 * @author Martin Bažík <martin@bazik.sk>
 */
class App
{

	/** @var LoggerInterface */
	private $logger;

	/** @var Request */
	private $req;

	/** @var Response */
	private $res;

	/** @var array */
	public $routes = [];

	/** @var array */
	private $middleware = [];


	function __construct(LoggerInterface $logger, Request $req = NULL, Response $res = NULL)
	{
		$this->logger = $logger;
		$this->req = !is_null($req) ? $req : Request::createFromGlobals();
		$this->res = !is_null($res) ? $res : new Response;
	}


	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
		return $this;
	}


	public function attach(MiddlewareInterface $middleware)
	{
		$this->middleware[] = $middleware;
		return $this;
	}


	public function run()
	{
		foreach ($this->middleware as $middleware) {
			$middleware->handle($this->req, $this->res);
		}
	}


}
