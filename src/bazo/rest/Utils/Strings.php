<?php

namespace Bazo\Rest\Utils;

/**
 * @author Martin Bažík <martin@bazik.sk>
 */
class Strings
{

	/**
	 * Starts the $haystack string with the prefix $needle?
	 * @param  string
	 * @param  string
	 * @return bool
	 * @author David Grudl
	 * @see https://github.com/nette/utils/blob/master/src/Utils/Strings.php#L83
	 */
	public static function startsWith($haystack, $needle)
	{
		return strncmp($haystack, $needle, strlen($needle)) === 0;
	}


}
