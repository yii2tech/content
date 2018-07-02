<?php

namespace yii2tech\tests\unit\content\data\mail;

use yii\mail\BaseMailer;

/**
 * Mailer mock class.
 */
class Mailer extends BaseMailer
{
    /**
     * {@inheritdoc}
     */
    public $messageClass = 'yii2tech\tests\unit\content\data\mail\Message';


    /**
     * {@inheritdoc}
     */
    protected function sendMessage($message)
    {
        return true;
    }
}