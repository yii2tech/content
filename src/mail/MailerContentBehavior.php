<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content\mail;

use yii\base\Behavior;
use yii\di\Instance;
use yii2tech\content\Manager;

/**
 * MailerContentBehavior is a behavior for component implementing [[\yii\mail\MailerInterface]] interface, which
 * allows mail message composition from content item.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'mailContentManager' => [
 *             'class' => 'yii2tech\content\Manager',
 *             'sourceStorage' => [
 *                 'class' => 'yii2tech\content\PhpStorage',
 *                 'filePath' => '@app/data/mail',
 *             ],
 *             'overrideStorage' => [
 *                 'class' => 'yii2tech\content\DbStorage',
 *                 'table' => '{{%MailTemplate}}',
 *                 'contentAttributes' => [
 *                     'subject',
 *                     'body',
 *                 ],
 *             ],
 *         ],
 *         'mailer' => [
 *             'class' => 'yii\swiftmailer\Mailer',
 *             'as content' => [
 *                 'class' => 'yii2tech\content\mail\MailerContentBehavior',
 *                 'contentManager' => 'mailContentManager',
 *                 'messagePopulationMap' => [
 *                     'subject' => 'setSubject()',
 *                     'body' => 'setHtmlBody()',
 *                 ],
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * Usage example:
 *
 * ```php
 * $model = new ContactForm();
 * // ...
 * Yii::$app->mailer->composeFromContent('contact', ['form' => $model])
 *     ->setTo(Yii::$app->params['appEmail'])
 *     ->setFrom(Yii::$app->params['appEmail'])
 *     ->send();
 * ```
 *
 * @see \yii\mail\MailerInterface
 * @see \yii\mail\BaseMailer
 *
 * @property \yii\mail\MailerInterface $owner owner mailer component instance.
 * @property Manager|array|string $contentManager related content manager component.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0.1
 */
class MailerContentBehavior extends Behavior
{
    /**
     * @var array map for mail message population from content item.
     * Array key matches content ID, while array value specifies the way for mail message data population.
     * Value can be specified in one of the following ways:
     *
     * - [[\Closure]] instance of the following signature: `function(\yii\mail\MessageInterface $message, string $content)`
     * - string ending with brackets, e.g. '()' - specifies setter method to pass content string to.
     * - string plain - specifies public field or property of the message instance for content string assignment.
     *
     * For example:
     *
     * ```php
     * [
     *     'from' => 'from'
     *     'subject' => 'setSubject()'
     *     'body' => function ($message, $content) {
     *         $message->setHtmlBody($content);
     *         $message->setTextBody(strip_tags($content));
     *     }
     * ]
     * ```
     */
    public $messagePopulationMap = [
        'subject' => 'setSubject()',
        'body' => 'setHtmlBody()',
    ];

    /**
     * @var Manager|array|string related content manager.
     */
    private $_contentManager = 'mailContentManager';


    /**
     * @return Manager related content manager.
     */
    public function getContentManager()
    {
        if (!$this->_contentManager instanceof Manager) {
            $this->_contentManager = Instance::ensure($this->_contentManager, Manager::className());
        }
        return $this->_contentManager;
    }

    /**
     * @param array|string|Manager $contentManager content manager instance or its DI compatible configuration.
     */
    public function setContentManager($contentManager)
    {
        $this->_contentManager = $contentManager;
    }

    /**
     * Creates a new message instance populating it from the content item specified by ID.
     * @param string $id content item ID.
     * @param array $data content data.
     * @return \yii\mail\MessageInterface message instance.
     */
    public function composeFromContent($id, array $data = [])
    {
        $contentItem = $this->getContentManager()->get($id);
        $message = $this->owner->compose();

        $this->populateMessage($message, $contentItem, $data);

        return $message;
    }

    /**
     * Populates mail message from content item instance.
     * @param \yii\mail\MessageInterface $message mail message instance.
     * @param \yii2tech\content\Item $item content item instance.
     * @param array $data content data.
     */
    protected function populateMessage($message, $item, array $data)
    {
        foreach ($this->messagePopulationMap as $contentId => $action) {
            $content = $item->render($contentId, $data);

            if ($action instanceof \Closure) {
                call_user_func($action, $message, $content);
                continue;
            }

            if (substr($action, -2) === '()') {
                call_user_func([$message, substr($action, 0, -2)], $content);
                continue;
            }

            $message->{$action} = $content;
        }
    }
}