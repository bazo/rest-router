<?php

namespace Bazo\Rest;

/**
 * @author Martin Bažík <martin@bazik.sk>
 */
class Template
{

	private static $globalQueryParams = [];
	private $patterns = [];
	private $template = null;
	private $params = [];
	private $callbacks = [];


	public function __construct($path)
	{
		if ($path{0} != '/') {
			$path = "/$path";
		}
		$this->template = rtrim($path, '\/');
	}


	public function getTemplate()
	{
		return $this->template;
	}


	public function getExpression()
	{
		$expression = $this->template;

		$matches = [];
		if (preg_match_all('~(?P<match>\{(?P<name>.+?)\})~', $expression, $matches)) {
			$expressions = array_map(array($this, 'pattern'), $matches['name']);
			$expression = str_replace($matches['match'], $expressions, $expression);
		}

		return sprintf('~^%s$~', $expression);
	}


	public function pattern($token, $pattern = null)
	{

		if ($pattern) {
			if (!isset($this->patterns[$token])) {
				$this->patterns[$token] = $pattern;
			}
		} else {
			if (isset($this->patterns[$token])) {
				$pattern = $this->patterns[$token];
			} else {
				$pattern = Constants::PATTERN_ANY;
			}

			if ((is_string($pattern) && is_callable($pattern)) || is_array($pattern)) {
				$this->callbacks[$token] = $pattern;
				$this->patterns[$token] = $pattern = Constants::PATTERN_ANY;
			}

			return sprintf($pattern, $token);
		}
	}


	public function addQueryParam($name, $pattern = '', $defaultValue = null)
	{
		if (!$pattern) {
			$pattern = Constants::PATTERN_ANY;
		}
		$this->params[$name] = (object) array(
					'pattern'	 => sprintf($pattern, $name),
					'value'		 => $defaultValue
		);
	}


	public static function addGlobalQueryParam($name, $pattern = '', $defaultValue = null)
	{
		if (!$pattern) {
			$pattern = Constants::PATTERN_ANY;
		}
		self::$globalQueryParams[$name] = (object) array(
					'pattern'	 => sprintf($pattern, $name),
					'value'		 => $defaultValue
		);
	}


	public function match($uri)
	{
		try {
			$uri = rtrim($uri, '\/');

			if (preg_match($this->getExpression(), $uri, $matches)) {

				foreach ($matches as $k => $v) {
					if (is_numeric($k)) {
						unset($matches[$k]);
					} else {

						if (isset($this->callbacks[$k])) {
							$callback = Callback_Util::getCallback($this->callbacks[$k]);
							$value = call_user_func($callback, $v);
							if ($value) {
								$matches[$k] = $value;
							} else {
								throw new InvalidURIParameterException('Invalid parameters detected');
							}
						}

						if (strpos($v, '/') !== FALSE) {
							$matches[$k] = explode('/', trim($v, '\/'));
						}
					}
				}

				$params = array_merge(self::$globalQueryParams, $this->params);

				if (!empty($params)) {

					$matched = FALSE;

					foreach ($params as $name => $param) {

						if (!isset($_GET[$name]) && $param->value) {
							$_GET[$name] = $param->value;
							$matched = TRUE;
						} else if ($param->pattern && isset($_GET[$name])) {
							$result = preg_match(sprintf('~^%s$~', $param->pattern), $_GET[$name]);
							if (!$result && $param->value) {
								$_GET[$name] = $param->value;
								$result = TRUE;
							}
							$matched = $result;
						} else {
							$matched = FALSE;
						}

						if ($matched == FALSE) {
							throw new Exception('Request does not match');
						}
					}
				}

				return $matches;
			}
		} catch (Exception $ex) {
			throw $ex;
		}
	}


	public static function regex($pattern)
	{
		return "(?P<%s>$pattern)";
	}


}
