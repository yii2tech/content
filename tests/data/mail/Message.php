<?php

namespace yii2tech\tests\unit\content\data\mail;

use yii\mail\BaseMessage;

/**
 * Message mock class.
 */
class Message extends BaseMessage
{
    /**
     * @var array message data.
     */
    public $data = [];


    /**
     * {@inheritdoc}
     */
    public function getCharset()
    {
        return $this->data['charset'];
    }

    /**
     * {@inheritdoc}
     */
    public function setCharset($charset)
    {
        $this->data['charset'] = $charset;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFrom()
    {
        return $this->data['from'];
    }

    /**
     * {@inheritdoc}
     */
    public function setFrom($from)
    {
        $this->data['from'] = $from;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTo()
    {
        return $this->data['to'];
    }

    /**
     * {@inheritdoc}
     */
    public function setTo($to)
    {
        $this->data['to'] = $to;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReplyTo()
    {
        return $this->data['replyTo'];
    }

    /**
     * {@inheritdoc}
     */
    public function setReplyTo($replyTo)
    {
        $this->data['replyTo'] = $replyTo;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCc()
    {
        return $this->data['cc'];
    }

    /**
     * {@inheritdoc}
     */
    public function setCc($cc)
    {
        $this->data['cc'] = $cc;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBcc()
    {
        return $this->data['bcc'];
    }

    /**
     * {@inheritdoc}
     */
    public function setBcc($bcc)
    {
        $this->data['bcc'] = $bcc;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->data['subject'];
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        $this->data['subject'] = $subject;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTextBody($text)
    {
        $this->data['textBody'] = $text;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHtmlBody($html)
    {
        $this->data['htmlBody'] = $html;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attach($fileName, array $options = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attachContent($content, array $options = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function embed($fileName, array $options = [])
    {
        return 'dummy';
    }

    /**
     * {@inheritdoc}
     */
    public function embedContent($content, array $options = [])
    {
        return 'dummy';
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return print_r($this->data, true);
    }
}