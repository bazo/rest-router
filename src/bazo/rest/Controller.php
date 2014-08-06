<?php

namespace Rest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



/**
 * @author Martin Bažík <martin@bazik.sk>
 */
abstract class Controller
{

	/** @var Request */
	protected $req;

	/** @var Response */
	protected $res;


	public function setReq(Request $req)
	{
		$this->req = $req;
		return $this;
	}


	public function setRes(Response $res)
	{
		$this->res = $res;
		return $this;
	}


	protected function respond($code, $payload = NULL)
	{
		$this->res->setStatusCode($code);
		$this->sendResponse($payload);
	}


	protected function sendResponse($payload = NULL)
	{
		$this->res->setExpiration(FALSE);
		if (is_null($payload)) {
			$this->res->setStatusCode(204);
		} else {
			$this->res->setCharset('utf8');
			$this->res->headers->set('Content-Type', 'application/json');
			$content = json_encode($payload, JSON_PRETTY_PRINT);
			$this->res->setContent($content);
		}
		$this->res->send();
	}


}
