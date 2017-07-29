<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\StorageInterface;

abstract class AbstractStorageTest extends TestCase
{
    /**
     * @return StorageInterface test storage instance.
     */
    abstract protected function createStorage();

    public function testSave()
    {
        $storage = $this->createStorage();

        $data = [
            'title' => 'Some Title',
            'body' => 'Some Body',
        ];
        $storage->save('test-save', $data);
    }

    /**
     * @depends testSave
     */
    public function testFind()
    {
        $storage = $this->createStorage();

        $this->assertNull($storage->find('test-find'));

        $data = [
            'title' => 'Some Title',
            'body' => 'Some Body',
        ];
        $storage->save('test-find', $data);

        $this->assertEquals($data, $storage->find('test-find'));
    }

    /**
     * @depends testFind
     */
    public function testDelete()
    {
        $storage = $this->createStorage();

        $data = [
            'title' => 'Some Title',
            'body' => 'Some Body',
        ];
        $storage->save('test-delete', $data);

        $storage->delete('test-delete');

        $this->assertNull($storage->find('test-delete'));
    }

    /**
     * @depends testFind
     */
    public function testSaveSubDir()
    {
        $storage = $this->createStorage();

        $data = [
            'title' => 'Some Title',
            'body' => 'Some Body',
        ];
        $storage->save('test/sub-dir', $data);

        $this->assertEquals($data, $storage->find('test/sub-dir'));
    }

    /**
     * @depends testSave
     */
    public function testFindAll()
    {
        $storage = $this->createStorage();

        $storage->save('item1', [
            'title' => 'title1',
            'body' => 'body1',
        ]);
        $storage->save('item2', [
            'title' => 'title2',
            'body' => 'body2',
        ]);

        $expectedAll = [
            'item1' => [
                'title' => 'title1',
                'body' => 'body1',
            ],
            'item2' => [
                'title' => 'title2',
                'body' => 'body2',
            ],
        ];
        $this->assertEquals($expectedAll, $storage->findAll());
    }
}