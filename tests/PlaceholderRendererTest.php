<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\PlaceholderRenderer;

class PlaceholderRendererTest extends TestCase
{
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
            [
                'CamelCase {{camelCase}} content',
                [
                    'camelCase' => 'foo',
                ],
                'CamelCase foo content',
            ],
            [
                'Underscore {{underscore_name}} content',
                [
                    'underscore_name' => 'foo',
                ],
                'Underscore foo content',
            ],
            [
                'Multi-level {{person.name}} content',
                [
                    'person' => [
                        'name' => 'John'
                    ],
                ],
                'Multi-level John content',
            ],
            [
                'Un existing {{unExisting}} content',
                [
                    'name' => 'foo',
                ],
                'Un existing {{unExisting}} content',
            ],
            [
                'Multi-level un existing {{person.name}} content',
                [
                    'name' => 'foo',
                ],
                'Multi-level un existing {{person.name}} content',
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
        $parser = new PlaceholderRenderer();
        $this->assertEquals($expectedResult, $parser->render($content, $data));
    }
}