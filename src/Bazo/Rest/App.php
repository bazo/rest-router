<?php

namespace Bazo\Rest;


use Bazo\Rest\Middleware\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
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

	/** @var array */
	public $onException = [];

	function __construct(LoggerInterface $logger = NULL, Request $req = NULL, Response $res = NULL)
	{
		$this->logger	 = $logger;
		$this->req		 = !is_null($req) ? $req : Request::createFromGlobals();
		$this->res		 = !is_null($res) ? $res : new Response;
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
		$this->log('Processing uri: ' . $this->req->getUri());

		try {
			foreach ($this->middleware as $middleware) {
				$middleware->handle($this->req, $this->res);
			}
		} catch (\Exception $e) {
			$this->log($e->getMessage(), [], LogLevel::ERROR);
			foreach ($this->onException as $exceptionCallback) {
				call_user_func($exceptionCallback, $e);
			}
		}
		$this->log('Processed uri: ' . $this->req->getUri());
	}


	public function log($message, $context = [], $level = LogLevel::INFO)
	{
		if (!is_null($this->logger)) {
			$this->logger->log($level, $message, $context);
		}
	}


}
