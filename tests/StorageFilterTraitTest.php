<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\StorageFilterTrait;

class StorageFilterTraitTest extends TestCase
{
    /**
     * Data provider for [[testComposeFilterAttributes()]]
     * @return array test data
     */
    public function dataProviderComposeFilterAttributes()
    {
        return [
            [
                [],
                [],
                [],
            ],
            [
                ['name' => 'value'],
                [],
                ['name' => 'value'],
            ],
            [
                ['name' => 'value'],
                ['email' => 'johndoe@example.com'],
                ['name' => 'value', 'email' => 'johndoe@example.com'],
            ],
            [
                ['name' => 'value'],
                ['name' => 'override'],
                ['name' => 'override'],
            ],
            [
                function () {
                    return ['name' => 'callback'];
                },
                [],
                ['name' => 'callback'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderComposeFilterAttributes
     *
     * @param array|callable $filter
     * @param array $condition
     * @param array $expectedResult
     */
    public function testComposeFilterAttributes($filter, $condition, $expectedResult)
    {
        $storage = new StorageFilter();
        $storage->filter = $filter;
        $this->assertEquals($expectedResult, $storage->buildFilterCondition($condition));
    }
}

class StorageFilter
{
    use StorageFilterTrait;

    public function buildFilterCondition($attributes)
    {
        return $this->composeFilterAttributes($attributes);
    }
}