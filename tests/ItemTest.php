<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\Item;

class ItemTest extends TestCase
{
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
}