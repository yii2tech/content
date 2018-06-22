<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\MustacheRenderer;

/**
 * @group mustache
 */
class MustacheRendererTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (!class_exists('Mustache_Engine')) {
            $this->markTestSkipped('"mustache/mustache" package required.');
        }
    }

    /**
     * Data provider for [[testParse()]].
     * @return array test data.
     */
    public function dataProviderParse()
    {
        return [
            [
                'Some {{name}} content',
                [
                    'name' => 'foo',
                ],
                'Some foo content',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderParse
     *
     * @param string $content
     * @param array $data
     * @param string $expectedResult
     */
    public function testParse($content, $data, $expectedResult)
    {
        $parser = new MustacheRenderer();
        $this->assertEquals($expectedResult, $parser->render($content, $data));
    }
}