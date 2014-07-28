<?php

namespace Bazo\Rest;

/**
 * @author Martin Bažík <martin@bazik.sk>
 */
class Response
{

	/** Ordered chunks of the output buffer * */
	public $chunks = array();
	public $code = 200;
	private $format;
	private $req;
	private $headers = array();


	/** Public constructor * */
	function __construct($request = null)
	{
		$this->req = $request;
	}


	/**
	 * Add string to output buffer.
	 */
	public function add($out)
	{
		$this->chunks[] = $out;
	}


	/**
	 * Flush output buffer to http client and end request
	 *
	 *  @param $code
	 *      HTTP response Code. Defaults to 200
	 *  @param $format
	 *      Output mime type. Defaults to request format
	 */
	public function send($code = null, $format = null)
	{
		$this->flush($code, $format);
		exit(); //prevent any further output
	}


	/**
	 * Send output to client without ending the script
	 *
	 *  @param $code
	 *      HTTP response Code. Defaults to 200
	 *  @param $format
	 *      Output mime type. Defaults to request format
	 *
	 *  @return current respons eobject, so you can chain method calls on a response object.
	 */
	public function flush($code = null, $format = null)
	{

		if (!empty($code)) {
			if (headers_sent()) {
				throw new InvalidResponseStateException("Response code already sent: {$this->code}");
			}

			$codes = $this->codes();
			if (array_key_exists($code, $codes)) {
				$resp_text = $codes[$code];
				$protocol = $this->req->protocol;
				$this->code = $code;
			} else {
				throw new InvalidResponseCodeException("Invalid Response Code: $code");
			}
		}

		// If no format was set explicitely, use the request format for response.
		if (!empty($format)) {
			if (headers_sent()) {
				throw new InvalidResponseStateException("Response format already sent: {$this->format}");
			}
			$this->setFormat($format);
		}

		// Set default values (200 and request format) if nothing was set explicitely
		if (empty($this->format)) {
			$this->format = $this->req->format;
		}
		if (empty($this->code)) {
			$this->code = 200;
		}

		$this->sendHeaders();

		/* Call preprocessors on each middleware impl */
		foreach (Router::$middleware as $m) {
			if ($m->shouldRun('prerender')) {
				$m->prerender($this->chunks);
			}
		}

		$out = implode('', $this->chunks);
		$this->chunks = array(); // reset
		echo ($out);
		return $this;
	}


	/**
	 * Set output format. Common aliases like: xml, json, html and txt are supported and
	 * automatically converted to proper HTTP content type definitions.
	 */
	public function setFormat($format)
	{
		$aliases = $this->req->common_aliases();
		if (array_key_exists($format, $aliases)) {
			$format = $aliases[$format];
		}
		$this->format = $format;
	}


	public function getFormat()
	{
		return $this->format;
	}


	/**
	 * Send headers to instruct browser not to cache this content
	 * See http://stackoverflow.com/a/2068407
	 */
	public function disableBrowserCache()
	{
		$this->headers[] = 'Cache-Control: no-cache, no-store, must-revalidate'; // HTTP 1.1.
		$this->headers[] = 'Pragma: no-cache'; // HTTP 1.0.
		$this->headers[] = 'Expires: Thu, 26 Feb 1970 20:00:00 GMT'; // Proxies.
	}


	/**
	 *  Send entire collection of headers if they haven't already been sent
	 */
	private function sendHeaders()
	{
		if (!headers_sent()) {
			foreach ($this->headers as $header) {
				header($header);
			}
			header("Content-Type: $this->format;", true, $this->code);
		}
	}


	private function codes()
	{
		return array(
			'100'	 => 'Continue',
			'101'	 => 'Switching Protocols',
			'200'	 => 'OK',
			'201'	 => 'Created',
			'202'	 => 'Accepted',
			'203'	 => 'Non-Authoritative Information',
			'204'	 => 'No Content',
			'205'	 => 'Reset Content',
			'206'	 => 'Partial Content',
			'300'	 => 'Multiple Choices',
			'301'	 => 'Moved Permanently',
			'302'	 => 'Found',
			'303'	 => 'See Other',
			'304'	 => 'Not Modified',
			'305'	 => 'Use Proxy',
			'307'	 => 'Temporary Redirect',
			'400'	 => 'Bad Request',
			'401'	 => 'Unauthorized',
			'402'	 => 'Payment Required',
			'403'	 => 'Forbidden',
			'404'	 => 'Not Found',
			'405'	 => 'Method Not Allowed',
			'406'	 => 'Not Acceptable',
			'407'	 => 'Proxy Authentication Required',
			'408'	 => 'Request Timeout',
			'409'	 => 'Conflict',
			'410'	 => 'Gone',
			'411'	 => 'Length Required',
			'412'	 => 'Precondition Failed',
			'413'	 => 'Request Entity Too Large',
			'414'	 => 'Request-URI Too Long',
			'415'	 => 'Unsupported Media Type',
			'416'	 => 'Requested Range Not Satisfiable',
			'417'	 => 'Expectation Failed',
			'500'	 => 'Internal Server Error',
			'501'	 => 'Not Implemented',
			'502'	 => 'Bad Gateway',
			'503'	 => 'Service Unavailable',
			'504'	 => 'Gateway Timeout',
			'505'	 => 'HTTP Version Not Supported',
		);
	}


}
