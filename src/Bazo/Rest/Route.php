<?php

namespace Bazo\Rest;

/**
 * @author Martin Bažík <martin@bazik.sk>
 */
class Route
{

	/** @var Template */
	private $template;
	private $handlers = [];


	function __construct($path, $handlers)
	{
		if (!is_array($path)) {
			$path = [$path, []];
		}

		$expr = $path[0];
		$validators = $path[1];

		$this->template = new Template($expr, $validators);
		$this->handlers = $handlers;
	}


	public function getTemplate()
	{
		return $this->template;
	}


	public function getHandlers()
	{
		return $this->handlers;
	}


	public function getMethods()
	{
		return array_keys($this->handlers);
	}


	public function getHandler($method)
	{
		return $this->handlers[$method];
	}


}
