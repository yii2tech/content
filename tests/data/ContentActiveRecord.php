<?php

namespace yii2tech\tests\unit\content\data;

use yii\db\ActiveRecord;

/**
 * @property string $id
 * @property string $title
 * @property string $body
 */
class ContentActiveRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Content';
    }
}