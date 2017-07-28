<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\SimpleParser;

class SimpleParserTest extends TestCase
{
    /**
     * Data provider for [[testParse()]].
     * @return array test data.
     */
    public function dataProviderParse()
    {
        return [
            [
                'Some {name} content',
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
        $parser = new SimpleParser();
        $this->assertEquals($expectedResult, $parser->parse($content, $data));
    }
}