<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\mail\MailerContentBehavior;
use yii2tech\content\Manager;
use yii2tech\content\PhpStorage;
use yii2tech\tests\unit\content\data\mail\Mailer;
use yii2tech\tests\unit\content\data\mail\Message;

/**
 * @group mail
 */
class MailerContentBehaviorTest extends TestCase
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
        ]);
    }

    /**
     * @param Manager $manager
     */
    protected function createTestSource(Manager $manager)
    {
        $storage = $manager->getSourceStorage();
        $storage->save('item1', [
            'from' => 'from1@example.com',
            'subject' => 'Subject 1',
            'body' => 'Body 1',
        ]);
        $storage->save('item2', [
            'from' => '{{from}}',
            'subject' => 'Subject: {{subject}}',
            'body' => 'Body: {{body}}',
        ]);
    }

    /**
     * @param Manager $contentManager
     * @return Mailer|MailerContentBehavior test mailer instance.
     */
    protected function createMailer($contentManager)
    {
        $mailer = new Mailer();

        $mailer->attachBehavior('content', [
            'class' => MailerContentBehavior::className(),
            'contentManager' => $contentManager,
            'messagePopulationMap' => [
                'subject' => 'subject',
                'from' => 'setFrom()',
                'body' => function (Message $message, $content) {
                    $message->setHtmlBody($content);
                },
            ],
        ]);

        return $mailer;
    }

    // Tests :

    public function testComposeFromContent()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);
        $mailer = $this->createMailer($manager);

        /* @var $message Message */
        $message = $mailer->composeFromContent('item1');

        $this->assertSame('from1@example.com', $message->getFrom());
        $this->assertSame('Subject 1', $message->getSubject());
        $this->assertSame('Body 1', $message->data['htmlBody']);
    }

    /**
     * @depends testComposeFromContent
     */
    public function testComposeFromContentWithData()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);
        $mailer = $this->createMailer($manager);

        /* @var $message Message */
        $message = $mailer->composeFromContent('item2', [
            'from' => 'data@example.com',
            'subject' => 'test',
            'body' => 'Test Body',
        ]);

        $this->assertSame('data@example.com', $message->getFrom());
        $this->assertSame('Subject: test', $message->getSubject());
        $this->assertSame('Body: Test Body', $message->data['htmlBody']);
    }
}