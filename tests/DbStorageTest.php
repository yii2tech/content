<?php

namespace yii2tech\tests\unit\content;

use Yii;
use yii2tech\content\DbStorage;

/**
 * @group db
 */
class DbStorageTest extends AbstractStorageTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setupTestDbData();
    }

    /**
     * {@inheritdoc}
     */
    protected function createStorage()
    {
        $storage = new DbStorage();
        $storage->table = 'Content';
        $storage->idAttribute = 'id';
        $storage->contentAttributes = [
            'title',
            'body',
        ];
        return $storage;
    }

    /**
     * Setup tables for test ActiveRecord
     */
    protected function setupTestDbData()
    {
        $db = Yii::$app->getDb();
        // Structure :
        $table = 'Content';
        $columns = [
            'id' => 'string',
            'title' => 'string',
            'body' => 'text',
            'PRIMARY KEY(id)'
        ];
        $db->createCommand()->createTable($table, $columns)->execute();
    }
}