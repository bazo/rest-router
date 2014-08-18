<?php

namespace Bazo\Rest\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



/**
 * @author Martin Bažík <martin@bazik.sk>
 */
interface MiddlewareInterface
{

	public function handle(Request $req, Response $res);
}
