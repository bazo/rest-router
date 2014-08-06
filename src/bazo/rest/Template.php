<?php

namespace Bazo\Rest;

/**
 * Based on Zaphpa library https://github.com/zaphpa/zaphpa
 * 
 * The MIT License (MIT)
 *
 * Copyright (c) 2011-2014 Ioseb Dzmanashvili and Irakli Nadareishvili
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the 'Software'), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
class Template
{

	/** @var array */
	private static $globalQueryParams = [];

	/** @var array */
	private $patterns = [];

	/** @var array */
	private $template = NULL;

	/** @var array */
	private $params = [];

	/** @var array */
	private $callbacks = [];


	public function __construct($path, $handlers)
	{
		if ($path{0} != '/') {
			$path = "/$path";
		}
		$this->template = rtrim($path, '\/');

		foreach ($handlers as $key => $pattern) {
			$this->pattern($key, $pattern);
		}
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
			$expressions = array_map([$this, 'pattern'], $matches['name']);
			$expression = str_replace($matches['match'], $expressions, $expression);
		}

		return sprintf('~^%s$~', $expression);
	}


	public function pattern($token, $pattern = NULL)
	{

		if ($pattern) {
			if (!isset($this->patterns[$token])) {
				$this->patterns[$token] = $pattern;
			}
		} else {
			if (isset($this->patterns[$token])) {
				$pattern = $this->patterns[$token];
			} else {
				$pattern = Patterns::PATTERN_ANY;
			}

			if ((is_string($pattern) && is_callable($pattern)) || is_array($pattern)) {
				$this->callbacks[$token] = $pattern;
				$this->patterns[$token] = $pattern = Patterns::PATTERN_ANY;
			}

			return sprintf($pattern, $token);
		}
	}


	public function addQueryParam($name, $pattern = '', $defaultValue = NULL)
	{
		if (!$pattern) {
			$pattern = Patterns::PATTERN_ANY;
		}
		$this->params[$name] = (object) [
					'pattern'	 => sprintf($pattern, $name),
					'value'		 => $defaultValue
		];
	}


	public static function addGlobalQueryParam($name, $pattern = '', $defaultValue = NULL)
	{
		if (!$pattern) {
			$pattern = Patterns::PATTERN_ANY;
		}
		self::$globalQueryParams[$name] = (object) [
					'pattern'	 => sprintf($pattern, $name),
					'value'		 => $defaultValue
		];
	}


	public function match($uri)
	{
		$uri = rtrim($uri, '\/');

		if (preg_match($this->getExpression(), $uri, $matches)) {

			foreach ($matches as $k => $v) {
				if (is_numeric($k)) {
					unset($matches[$k]);
				} else {

					if (isset($this->callbacks[$k])) {
						$callback = DefaultCallbackResolver::getCallback($this->callbacks[$k]);
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
	}


	public static function regex($pattern)
	{
		return "(?P<%s>$pattern)";
	}


}
