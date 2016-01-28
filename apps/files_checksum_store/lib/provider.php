<?php

namespace OCA\Files_Checksum_Store;

use OCP\Files\Checksum\IProvider;
use OCP\Files\Checksum\IManager;
use OCP\AppFramework\IAppContainer;
use OCP\Files\File;

class Provider implements IProvider {

	public static function register(IManager $cm, IAppContainer $container) {
		$cm->registerProvider(function() use ($container) {
			return $container->query('Provider');
		});
	}

	public function addChecksum(File $file, $type, $checksum) {
		// TODO: Implement addChecksum() method.
	}

	public function getChecksum(File $file, $type) {
		// TODO: Implement getChecksum() method.
	}

	public function getChecksums(File $file) {
		// TODO: Implement getChecksums() method.
	}
}