<?php

namespace Bazo\Rest;

/**
 * @author Martin Bažík <martin@bazik.sk>
 */
abstract class Methods
{

	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	const PATCH = 'PATCH';
	const DELETE = 'DELETE';
	const HEAD = 'HEAD';
	const OPTIONS = 'OPTIONS';


	private static $list = [
		self::GET,
		self::POST,
		self::PUT,
		self::PATCH,
		self::DELETE,
		self::HEAD,
		self::OPTIONS
	];


	public static function getList()
	{
		return self::$list;
	}


}
