<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\PhpStorage;

class PhpStorageTest extends AbstractStorageTest
{
    /**
     * {@inheritdoc}
     */
    protected function createStorage()
    {
        $storage = new PhpStorage();
        $storage->filePath = $this->getTestFilePath();
        return $storage;
    }

    public function testSave()
    {
        $storage = $this->createStorage();

        $data = [
            'title' => 'Some Title',
            'body' => 'Some Body',
        ];
        $storage->save('test-save', $data);

        $fileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . 'test-save.php';
        $this->assertFileExists($fileName);

        $this->assertEquals($data, require $fileName);
    }
}