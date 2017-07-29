<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\PhpStorage;

class PhpStorageTest extends TestCase
{
    public function testSave()
    {
        $storage = new PhpStorage();
        $storage->filePath = $this->getTestFilePath();

        $data = [
            'title' => 'Some Title',
            'body' => 'Some Body',
        ];
        $storage->save('test-save', $data);

        $fileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . 'test-save.php';
        $this->assertFileExists($fileName);

        $this->assertEquals($data, require $fileName);
    }

    /**
     * @depends testSave
     */
    public function testFind()
    {
        $storage = new PhpStorage();
        $storage->filePath = $this->getTestFilePath();

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
        $storage = new PhpStorage();
        $storage->filePath = $this->getTestFilePath();

        $data = [
            'title' => 'Some Title',
            'body' => 'Some Body',
        ];
        $storage->save('test-delete', $data);

        $storage->delete('test-delete');

        $this->assertNull($storage->find('test-delete'));
    }
}