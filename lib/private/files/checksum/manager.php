<?php

namespace OC\Files\Checksum;

use OCP\Files\Checksum\IManager;
use OCP\Files\File;
use OCP\Files\Checksum\IProvider;

class Manager implements IManager {

	/** @var \Closure */
	private $providerClosure;

	/** @var IProvider */
	private $provider;

	/**
	 * TODO throw exception. We only want to allow 1 provider
	 *
	 * @param \Closure $closure
	 */
	public function registerProvider(\Closure $closure) {
		$this->providerClosure = $closure;
	}

	/**
	 * @return IProvider
	 */
	private function getProvider() {
		if ($this->provider === null) {
			$closure = $this->providerClosure;
			$p = $closure();
			if (!($p instanceof IProvider)) {
				throw \InvalidArgumentException('The given provider does not implement the \OCP\Files\Checksum\IProvider interface');
			}
			$this->provider = $p;
		}

		return $this->provider;
	}


	public function addChecksum(File $file, $type, $checksum) {
		return $this->getProvider()->addChecksum($file, $type, $checksum);
	}

	public function getChecksum(File $file, $type) {
		return $this->getProvider()->getChecksum($file, $type);
	}

	public function getChecksums(File $file) {
		return $this->getProvider()->getChecksums();
	}
}