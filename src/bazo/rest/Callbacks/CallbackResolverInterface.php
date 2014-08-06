<?php

namespace Bazo\Rest\Callbacks;

/**
 * @author Martin Bažík <martin@bazik.sk>
 */
interface CallbackResolverInterface
{

	public function resolve($handler);
}
