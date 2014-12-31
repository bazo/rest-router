<?php

namespace Bazo\Rest\Bridges\HttpKernel;

use Bazo\Rest\Middleware\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;



/**
 * @author Martin Bažík <martin@bazik.sk>
 */
class HttpKernelMiddlewareAdapter implements MiddlewareInterface
{

	/** @var HttpKernelInterface */
	private $middleware;


	function __construct(HttpKernelInterface $middleware)
	{
		$this->middleware = $middleware;
	}


	public function handle(Request $req, Response $res)
	{
		$this->middleware->handle($req);
	}


}
