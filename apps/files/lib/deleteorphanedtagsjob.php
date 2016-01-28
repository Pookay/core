<?php
/**
 * @author Vincent Petry <pvince81@owncloud.com>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files\Lib;

use OC\BackgroundJob\TimedJob;

/**
 * Delete all share entries that have no matching entries in the file cache table.
 */
class DeleteOrphanedTagsJob extends TimedJob {

	/**
	 * Default interval in minutes
	 *
	 * @var int $defaultIntervalMin
	 **/
	protected $defaultIntervalMin = 60;

	/**
	 * sets the correct interval for this timed job
	 */
	public function __construct(){
		$this->interval = $this->defaultIntervalMin * 60;
	}

	/**
	 * Makes the background job do its work
	 *
	 * @param array $argument unused argument
	 */
	public function run($argument) {
		$connection = \OC::$server->getDatabaseConnection();
		$logger = \OC::$server->getLogger();

        // deleting orphaned system tag mappings
        $query = $connection->getQueryBuilder();
        $query->select($query->expr()->literal('1'))
            ->from('filecache', 'f')
            ->where($query->expr()->eq('objectid', 'f.fileid'));
        $subQuery = $query;

        $query = $connection->getQueryBuilder();
		$deletedEntries = $connection->getQueryBuilder()->delete('systemtag_object_mapping')
			->where($query->expr()->eq('objecttype', $query->expr()->literal('files')))
            ->andWhere($query->expr()->isNull($query->createFunction('(' . $subQuery->getSql() . ')')))
			->execute();

		$logger->debug("$deletedEntries orphaned system tag relations deleted", ['app' => 'DeleteOrphanedTagsJob']);

        // deleting orphaned user tag mappings
        $query = $connection->getQueryBuilder();
        $query->select($query->expr()->literal('1'))
            ->from('filecache', 'f')
            ->where($query->expr()->eq('objid', 'f.fileid'));
        $subQuery = $query;

        $query = $connection->getQueryBuilder();
		$deletedEntries = $connection->getQueryBuilder()->delete('vcategory_to_object')
			->where($query->expr()->eq('type', $query->expr()->literal('files')))
            ->andWhere($query->expr()->isNull($query->createFunction('(' . $subQuery->getSql() . ')')))
			->execute();

		$logger->debug("$deletedEntries orphaned user tag relations deleted", ['app' => 'DeleteOrphanedTagsJob']);
	}

}
