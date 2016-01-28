<?php

namespace OCP\Files\Checksum;

use \OCP\Files\File;

/**
 * Interface IProvider
 *
 * @package OCP\Files\Checksum
 * @since 9.0.0
 */
interface IProvider {

	/**
	 * Add the checksum for $file
	 *
	 * @param File $file
	 * @param string $type
	 * @param string $checksum
	 * @return bool
	 * @since 9.0.0
	 */
	public function addChecksum(File $file, $type, $checksum);

	/**
	 * Get the checksum for $file
	 *
	 * @param File $file
	 * @param string $type
	 * @return string
	 * @since 9.0.0
	 */
	public function getChecksum(File $file, $type);

	/**
	 * Get all the checksums for $file
	 *
	 * @param File $file
	 * @return string[] Mapping of type => checksum
	 * @since 9.0.0
	 */
	public function getChecksums(File $file);
}