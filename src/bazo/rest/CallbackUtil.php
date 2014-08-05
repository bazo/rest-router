<?php

namespace Bazo\Rest;

/**
 * @author Martin Bažík <martin@bazik.sk>
 */
class CallbackUtil
{

	/**
	 * @var \SystemContainer
	 */
	public static $container;


	private static function loadFile($file)
	{

		if (file_exists($file)) {
			include_once($file);
		} else {
			throw new CallbackFileNotFoundException('Controller file not found');
		}
	}


	public static function getCallback($callback, $file = null)
	{

		if ($file) {
			self::loadFile($file);
		}

		if (is_array($callback)) {
			$originalClass = array_shift($callback);
			$method = new \ReflectionMethod($originalClass, array_shift($callback));

			$callback = array(self::$container->getByType($originalClass), $method->name);
		}

		if (is_callable($callback)) {
			return $callback;
		}

		throw new InvalidCallbackException('Invalid callback');
	}


}
