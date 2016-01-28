<?php

namespace OCP\Files\Checksum;

/**
 * Interface IManager
 *
 * @package OCP\Files\Checksum
 * @since 9.0.0
 */
interface IManager extends IProvider {

	/**
	 * Register a checksum provider
	 *
	 * $closure must return an instance of \OCP\Files\Checksum\IProvider
	 *
	 * @param \Closure $closure
	 * @since 9.0.0
	 */
	public function registerProvider(\Closure $closure);
}
