<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\Item;
use yii2tech\content\Manager;
use yii2tech\content\PhpStorage;
use yii2tech\content\PlaceholderRenderer;

class ItemTest extends TestCase
{
    /**
     * @return Manager test manager instance.
     */
    protected function createManager()
    {
        return new Manager([
            'sourceStorage' => [
                'class' => PhpStorage::className(),
                'filePath' => $this->getTestFilePath() . DIRECTORY_SEPARATOR . 'source'
            ],
            'overrideStorage' => [
                'class' => PhpStorage::className(),
                'filePath' => $this->getTestFilePath() . DIRECTORY_SEPARATOR . 'override'
            ],
            'renderer' => [
                'class' => PlaceholderRenderer::className(),
            ],
        ]);
    }

    /**
     * @param Manager $manager
     */
    protected function createTestSource(Manager $manager)
    {
        $storage = $manager->getSourceStorage();
        $storage->save('item1', [
            'title' => 'Item 1',
            'body' => 'Item1 {{name}} body',
        ]);
        $storage->save('item2', [
            'title' => 'Item 2',
            'body' => 'Item2 {{name}} body',
        ]);
    }

    // Tests :

    public function testSetupContents()
    {
        $item = new Item();

        $contents = [
            'title' => 'Some title',
            'body' => 'Some body',
        ];
        $item->setContents($contents);
        $this->assertEquals($contents, $item->getContents());

        $this->assertTrue($item->has('title'));
        $this->assertFalse($item->has('un-existing'));

        $this->assertEquals($contents['title'], $item->get('title'));
    }

    /**
     * @depends testSetupContents
     */
    public function testAttributes()
    {
        $item = new Item();
        $contents = [
            'title' => 'Some title',
            'body' => 'Some body',
        ];
        $item->setContents($contents);

        $this->assertEquals(['title', 'body'], $item->attributes());

        $this->assertTrue($item->load(['title' => 'new title'], ''));
        $this->assertEquals('new title', $item->get('title'));
    }

    /**
     * @depends testSetupContents
     */
    public function testContentProperties()
    {
        $item = new Item();
        $contents = [
            'title' => 'Some title',
            'body' => 'Some body',
        ];
        $item->setContents($contents);

        $item->title = 'new title';
        $this->assertEquals('new title', $item->title);

        $this->assertTrue(isset($item->title));
        $this->assertFalse(isset($item->unexisting));

        unset($item->title);
        $this->assertFalse(isset($item->title));
    }

    /**
     * @depends testAttributes
     */
    public function testValidate()
    {
        $item = new Item();

        $contents = [
            'title' => '',
            'body' => '',
        ];
        $item->setContents($contents);
        $this->assertFalse($item->validate());

        $contents = [
            'title' => 'some title',
            'body' => 'some body',
        ];
        $item->setContents($contents);
        $this->assertTrue($item->validate());
    }

    /**
     * @depends testValidate
     */
    public function testValidateCustomRules()
    {
        $item = new Item();
        $item->setContents([
            'email' => '',
        ]);
        $item->rules = [
            ['email', 'email']
        ];

        $item->email = 'some-string';
        $this->assertFalse($item->validate());

        $item->email = 'user@example.com';
        $this->assertTrue($item->validate());
    }

    /**
     * @depends testValidateCustomRules
     */
    public function testValidateCustomRulesCallback()
    {
        $item = new Item();
        $item->setContents([
            'email' => '',
        ]);
        $item->rules = function () {
            return [
                ['email', 'email']
            ];
        };

        $item->email = 'some-string';
        $this->assertFalse($item->validate());

        $item->email = 'user@example.com';
        $this->assertTrue($item->validate());
    }

    /**
     * @depends testSetupContents
     */
    public function testRender()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);

        $item = $manager->get('item1');

        $this->assertEquals('Item1 foo body', $item->render('body', ['name' => 'foo']));
    }

    /**
     * @depends testRender
     */
    public function testRenderDefaultData()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);

        $item = $manager->get('item1');
        $manager->defaultRenderData = [
            'name' => 'default'
        ];

        $this->assertEquals('Item1 default body', $item->render('body'));
        $this->assertEquals('Item1 override body', $item->render('body', ['name' => 'override']));

        $manager->defaultRenderData = function () {
            return [
                'name' => 'callback'
            ];
        };
        $this->assertEquals('Item1 callback body', $item->render('body'));
    }

    /**
     * @depends testSetupContents
     */
    public function testSave()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);

        $item = $manager->get('item1');

        $item->setContents([
            'title' => 'override title',
            'body' => 'override body',
        ]);
        $item->save(false);

        $refreshedItem = $manager->get('item1');

        $this->assertEquals($item->getContents(), $refreshedItem->getContents());
    }

    /**
     * @depends testSave
     */
    public function testReset()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);

        $item = $manager->get('item1');

        $item->setContents([
            'title' => 'override title',
            'body' => 'override body',
        ]);
        $item->save(false);

        $item->reset(false);

        $refreshedItem = $manager->get('item1');

        $this->assertNotEquals($item->getContents(), $refreshedItem->getContents());

        $item->reset(true);
        $this->assertEquals($item->getContents(), $refreshedItem->getContents());
    }

    /**
     * @depends testSetupContents
     */
    public function testMetaData()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);
        $manager->metaDataContentParts = ['body'];

        $item = $manager->get('item1');

        $this->assertFalse($item->has('body'));

        $this->assertEquals(['body' => 'Item1 {{name}} body'], $item->getMetaData());
    }

    /**
     * @depends testAttributes
     */
    public function testLabels()
    {
        $item = new Item();
        $item->setContents([
            'title' => '',
            'body' => '',
        ]);
        $this->assertEquals('ID', $item->getAttributeLabel('id'));

        $item->labels = [
            'title' => 'Title Label',
            'body' => 'Body Label',
        ];
        $this->assertEquals('Title Label', $item->getAttributeLabel('title'));
        $this->assertEquals('Body Label', $item->getAttributeLabel('body'));
        $this->assertEquals('ID', $item->getAttributeLabel('id'));
    }

    /**
     * @depends testLabels
     */
    public function testLabelsCallback()
    {
        $item = new Item();
        $item->setContents([
            'title' => '',
            'body' => '',
        ]);

        $item->labels = function () {
            return [
                'title' => 'Title Label',
                'body' => 'Body Label',
            ];
        };
        $this->assertEquals('Title Label', $item->getAttributeLabel('title'));
        $this->assertEquals('Body Label', $item->getAttributeLabel('body'));
        $this->assertEquals('ID', $item->getAttributeLabel('id'));
    }

    /**
     * @depends testAttributes
     */
    public function testHints()
    {
        $item = new Item();
        $item->setContents([
            'title' => '',
            'body' => '',
        ]);

        $item->hints = [
            'title' => 'Title Hint',
            'body' => 'Body Hint',
        ];
        $this->assertEquals('Title Hint', $item->getAttributeHint('title'));
        $this->assertEquals('Body Hint', $item->getAttributeHint('body'));
    }

    /**
     * @depends testHints
     */
    public function testHintsCallback()
    {
        $item = new Item();
        $item->setContents([
            'title' => '',
            'body' => '',
        ]);

        $item->hints = function () {
            return [
                'title' => 'Title Hint',
                'body' => 'Body Hint',
            ];
        };
        $this->assertEquals('Title Hint', $item->getAttributeHint('title'));
        $this->assertEquals('Body Hint', $item->getAttributeHint('body'));
    }
}