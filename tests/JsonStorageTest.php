<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\JsonStorage;

class JsonStorageTest extends AbstractStorageTest
{
    /**
     * {@inheritdoc}
     */
    protected function createStorage()
    {
        $storage = new JsonStorage();
        $storage->filePath = $this->getTestFilePath();
        return $storage;
    }
}