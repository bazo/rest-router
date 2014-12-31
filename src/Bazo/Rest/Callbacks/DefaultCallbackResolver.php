<?php

namespace Bazo\Rest\Callbacks;

use Bazo\Rest\InvalidCallbackException;
use Bazo\Rest\Utils\Strings;
use Interop\Container\ContainerInterface;



/**
 * @author Martin Bažík <martin@bazik.sk>
 */
class DefaultCallbackResolver implements CallbackResolverInterface
{

	/** @var ContainerInterface */
	private $container;


	function __construct(ContainerInterface $container = NULL)
	{
		$this->container = $container;
	}


	public function resolve($callback)
	{
		if (is_array($callback)) {
			$callback = $this->resolveArrayCallback($callback);
		}

		if (is_callable($callback)) {
			return $callback;
		}

		throw new InvalidCallbackException('Invalid callback');
	}


	private function resolveArrayCallback($callback)
	{
		$originalClass = array_shift($callback);

		if (!is_null($this->container)) {
			if (Strings::startsWith($originalClass, '@')) {
				$method = current($callback);
				$serviceName = substr($originalClass, 1);
				$service = $this->container->get($serviceName);
				$callback = [$service, $method];

				return $callback;
			}
		}

		$method = new \ReflectionMethod($originalClass, array_shift($callback));

		if (!$method->isPublic()) {
			return NULL;
		}

		if ($method->isStatic()) {
			$callback = [$originalClass, $method->name];
		} else {
			$callback = [new $originalClass, $method->name];
		}

		return $callback;
	}


}
