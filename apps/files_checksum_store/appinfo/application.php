<?php

namespace OCA\Files_Checksum_Store\AppInfo;

use OCP\AppFramework\App;
use OCA\Files_Checksum_Store\Provider;
use OCP\IContainer;

class Application extends App {

	public function __construct (array $urlParams = array()) {
		parent::__construct('files_checksum_store', $urlParams);
		$container = $this->getContainer();

		$container->registerService('Provider', function(IContainer $c) {
			return new Provider();
		});
	}

}
