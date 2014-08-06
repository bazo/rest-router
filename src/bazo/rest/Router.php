<?php

namespace Bazo\Rest;

use Bazo\Rest\Callbacks\CallbackResolverInterface;
use Bazo\Rest\Callbacks\DefaultCallbackResolver;
use Bazo\Rest\Middleware\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



/**
 * @author Martin Bažík <martin@bazik.sk>
 */
class Router implements MiddlewareInterface, \ArrayAccess
{

	/** @var CallbackResolverInterface */
	private $callbackResolver;

	/** @var array */
	private $methods = [];

	/** @var array */
	private $routes = [];


	function __construct(CallbackResolverInterface $callbackResolver = NULL)
	{
		if (is_null($callbackResolver)) {
			$this->callbackResolver = new DefaultCallbackResolver;
		} else {
			$this->callbackResolver = $callbackResolver;
		}

		$this->methods = Methods::getList();
	}


	public function setCallbackResolver(CallbackResolverInterface $callbackResolver)
	{
		$this->callbackResolver = $callbackResolver;
	}


	public function addRoute(Route $route)
	{
		$methods = array_intersect($this->methods, $route->getMethods());

		foreach ($methods as $method) {
			if (!array_key_exists($method, $this->routes)) {
				$this->routes[$method] = [];
			}

			$this->routes[$method][] = $route;
		}
		return $this;
	}


	private function getRoutes($method)
	{
		$routes = empty($this->routes[$method]) ? [] : $this->routes[$method];
		return $routes;
	}


	public function match(Request $request)
	{
		$uri = $request->getPathInfo();
		$method = $request->getMethod();

		$routes = $this->getRoutes($method);
		foreach ($routes as $route) {
			$params = $route->getTemplate()->match($uri);

			if (is_null($params)) {
				continue;
			}

			return [
				'route'	 => $route,
				'params' => $params,
			];
		}

		return NULL;
	}


	public function route(Request $req, Response $res)
	{
		$method = $req->getMethod();

		$matched = $this->match($req);

		if (is_null($matched)) {
			throw new InvalidPathException('Invalid path');
		}

		$route = $matched['route'];
		$params = $matched['params'];

		$req->params = $params;

		$handler = $route->getHandler($method);
		$callback = $this->callbackResolver->resolve($handler);

		call_user_func($callback, $req, $res);
	}


	public function handle(Request $req, Response $res)
	{
		$this->route($req, $res);
	}


	public function offsetExists($index)
	{
		$this->notSupported();
	}


	public function offsetGet($index)
	{
		$this->notSupported();
	}


	public function offsetSet($index, $route)
	{
		if (!$route instanceof Route) {
			throw new \InvalidArgumentException('Argument must be a Route');
		}
		if ($index === NULL) {
			$this->addRoute($route);
		} else {
			$this->notSupported();
		}
	}


	public function offsetUnset($index)
	{
		$this->notSupported();
	}


	private function notSupported()
	{
		throw new \RuntimeException('Not supported');
	}


}
