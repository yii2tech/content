<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\Manager;
use yii2tech\content\PhpStorage;
use yii2tech\content\SimpleParser;

class ManagerTest extends TestCase
{
    public function testSetupParser()
    {
        $manager = new Manager();

        $manager->setParser(['class' => SimpleParser::className()]);
        $this->assertTrue($manager->getParser() instanceof SimpleParser);

        $parser = new SimpleParser();
        $manager->setParser($parser);
        $this->assertSame($parser, $manager->getParser());
    }

    public function testSetupSourceStorage()
    {
        $manager = new Manager();

        $manager->setSourceStorage(['class' => PhpStorage::className()]);
        $this->assertTrue($manager->getSourceStorage() instanceof PhpStorage);

        $storage = new PhpStorage();
        $manager->setSourceStorage($storage);
        $this->assertSame($storage, $manager->getSourceStorage());
    }

    public function testSetupOverrideStorage()
    {
        $manager = new Manager();

        $manager->setOverrideStorage(['class' => PhpStorage::className()]);
        $this->assertTrue($manager->getOverrideStorage() instanceof PhpStorage);

        $storage = new PhpStorage();
        $manager->setOverrideStorage($storage);
        $this->assertSame($storage, $manager->getOverrideStorage());
    }
}