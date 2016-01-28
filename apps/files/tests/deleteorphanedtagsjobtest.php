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

namespace Test\BackgroundJob;

use OCA\Files\Lib\DeleteOrphanedTagsJob;
use OCP\ITags;
use OCP\SystemTag\ISystemTagManager;

/**
 * Class DeleteOrphanedTagsJobTest
 *
 * @group DB
 *
 * @package Test\BackgroundJob
 */
class DeleteOrphanedTagsJobTest extends \Test\TestCase {

	/**
	 * @var bool
	 */
	private static $trashBinStatus;

	/**
	 * @var DeleteOrphanedTagsJob
	 */
	private $job;

	/**
	 * @var \OCP\IDBConnection
	 */
	private $connection;

	/**
	 * @var string
	 */
	private $user1;

    /**
     * @var ITags
     */
    private $userTagManager;

    /**
     * @var ISystemTagManager
     */
    private $systemTagManager;


	public static function setUpBeforeClass() {
		$appManager = \OC::$server->getAppManager();
		self::$trashBinStatus = $appManager->isEnabledForUser('files_trashbin');
		$appManager->disableApp('files_trashbin');

		// just in case...
		\OC\Files\Filesystem::getLoader()->removeStorageWrapper('oc_trashbin');
	}

	public static function tearDownAfterClass() {
		if (self::$trashBinStatus) {
			\OC::$server->getAppManager()->enableApp('files_trashbin');
		}
	}

	protected function setup() {
		parent::setUp();

		$this->connection = \OC::$server->getDatabaseConnection();

		$this->user1 = $this->getUniqueID('user1_');

		$userManager = \OC::$server->getUserManager();
		$userManager->createUser($this->user1, 'pass');

        $this->userTagManager = \OC::$server->getTagManager()->load('files', ['test'], false, $this->user1);
        $this->systemTagManager = \OC::$server->getSystemTagManager();
        $this->systemTagManager->createTag('test', true, true);

		$this->job = new DeleteOrphanedTagsJob();
	}

	protected function tearDown() {
        $this->userTagManager->delete('test');
        $this->systemTagManager->deleteTags($this->systemTagManager->getTag('test', true, true)->getId());

		$userManager = \OC::$server->getUserManager();
		$user1 = $userManager->get($this->user1);
		if($user1) {
			$user1->delete();
		}

		$this->logout();

		parent::tearDown();
	}

	private function getSystemTagMappings() {
		$builder = $this->connection->getQueryBuilder();
		return $builder->select('*')
			->from('systemtag_object_mapping')
            ->execute()->fetchAll();
	}

	/**
	 * Test clearing orphaned system tag mappings
	 */
	public function testClearSystemTagMappings() {
		$this->loginAsUser($this->user1);

        $root = \OC::$server->getUserFolder();
        $dir1 = $root->newFolder('test_delete');
        $dir2 = $root->newFolder('test_remain');

        $tag = $this->systemTagManager->getTag('test', true, true);
        $mapper = \OC::$server->getSystemTagObjectMapper();
        $mapper->assignTags($dir1->getId(), 'files', $tag->getId());
        $mapper->assignTags($dir2->getId(), 'files', $tag->getId());

		$this->assertCount(2, $this->getSystemTagMappings());

		$this->job->run([]);

		$this->assertCount(2, $this->getSystemTagMappings(), 'Nothing deleted before file deletion');

        $dir1->delete();

		$this->job->run([]);

        $mappings = $this->getSystemTagMappings();
		$this->assertCount(1, $mappings, 'Orphaned mappings deleted');
        $this->assertEquals($dir2->getId(), $mappings[0]['objectid']);
	}

	private function getUserTagMappings() {
		$builder = $this->connection->getQueryBuilder();
		return $builder->select('*')
			->from('vcategory_to_object')
            ->execute()->fetchAll();
	}

	/**
	 * Test clearing orphaned user tag mappings
	 */
	public function testClearUserTagMappings() {
		$this->loginAsUser($this->user1);

        $root = \OC::$server->getUserFolder();
        $dir1 = $root->newFolder('test_delete');
        $dir2 = $root->newFolder('test_remain');

        $this->userTagManager->tagAs($dir1->getId(), 'test');
        $this->userTagManager->tagAs($dir2->getId(), 'test');

		$this->assertCount(2, $this->getUserTagMappings());

		$this->job->run([]);

		$this->assertCount(2, $this->getUserTagMappings(), 'Nothing deleted before file deletion');

        $dir1->delete();

		$this->job->run([]);

        $mappings = $this->getUserTagMappings();
		$this->assertCount(1, $mappings, 'Orphaned mappings deleted');
        $this->assertEquals($dir2->getId(), $mappings[0]['objid']);
	}

}

