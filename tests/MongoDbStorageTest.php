<?php

namespace yii2tech\tests\unit\content;

use yii\mongodb\Connection;
use yii2tech\content\MongoDbStorage;

/**
 * @group mongodb
 */
class MongoDbStorageTest extends AbstractStorageTest
{
    /**
     * @var Connection MongoDB connection used for the test running.
     */
    protected $db;

    public function setUp()
    {
        $this->mockApplication([
            'components' => [
                'mongodb' => $this->getDb()
            ],
        ]);
    }

    protected function tearDown()
    {
        $db = $this->getDb();
        try {
            $db->getCollection('Content', true)->drop();
        } catch (\yii\mongodb\Exception $e) {
            // shutdown exception
        }
        $db->close();

        parent::tearDown();
    }

    /**
     * @return Connection test database connection
     */
    protected function getDb()
    {
        if ($this->db === null) {
            if (!extension_loaded('mongodb')) {
                $this->markTestSkipped('mongodb PHP extension required.');
                return null;
            }
            if (!class_exists('yii\mongodb\Connection')) {
                $this->markTestSkipped('"yiisoft/yii2-mongodb" extension required.');
                return null;
            }

            $connectionConfig = $this->getParam('mongodb', [
                'dsn' => 'mongodb://travis:test@localhost:27017',
                'defaultDatabaseName' => 'yii2test',
                'options' => [],
            ]);

            $this->db = new Connection($connectionConfig);
            $this->db->open();
        }
        return $this->db;
    }

    /**
     * {@inheritdoc}
     */
    protected function createStorage()
    {
        $storage = new MongoDbStorage();
        $storage->collection = 'Content';
        $storage->idAttribute = 'id';
        $storage->contentAttributes = [
            'title',
            'body',
        ];
        return $storage;
    }
}