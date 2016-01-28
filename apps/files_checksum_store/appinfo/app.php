<?php

namespace OCA\Files_Checksum_Store\AppInfo;

$app = new Application();
$c = $app->getContainer();

\OCA\Files_Checksum_Store\Provider::register($c->getServer()->getChecksumManager(), $c);