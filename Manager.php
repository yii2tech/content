<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\di\Instance;

/**
 * Manager
 *
 * @property ParserInterface|array|\Closure $parser content parser.
 * @property StorageInterface|array|\Closure $sourceStorage source content storage.
 * @property StorageInterface|array|\Closure $overrideStorage override content storage.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Manager extends Component
{
    /**
     * @var array|\Closure configuration for the content item creation.
     */
    public $itemConfig = ['class' => 'yii2tech\content\Item'];

    /**
     * @var ParserInterface|array|\Closure content parser.
     */
    private $_parser = ['class' => 'yii2tech\content\SimpleParser'];
    /**
     * @var StorageInterface|array|\Closure source content storage.
     */
    private $_sourceStorage;
    /**
     * @var StorageInterface|array|\Closure override content storage.
     */
    private $_overrideStorage;


    /**
     * @return ParserInterface parser instance.
     */
    public function getParser()
    {
        if (!is_object($this->_parser) || $this->_parser instanceof \Closure) {
            $this->_parser = Instance::ensure($this->_parser, 'yii2tech\content\ParserInterface');
        }
        return $this->_parser;
    }

    /**
     * @param ParserInterface|array|\Closure $parser parser instance or its DI compatible configuration.
     */
    public function setParser($parser)
    {
        $this->_parser = $parser;
    }

    /**
     * @return StorageInterface source content storage instance.
     */
    public function getSourceStorage()
    {
        if (!is_object($this->_sourceStorage) || $this->_sourceStorage instanceof \Closure) {
            $this->_sourceStorage = Instance::ensure($this->_sourceStorage, 'yii2tech\content\StorageInterface');
        }
        return $this->_sourceStorage;
    }

    /**
     * @param StorageInterface|array|\Closure $sourceStorage source content storage instance or DI compatible configuration.
     */
    public function setSourceStorage($sourceStorage)
    {
        $this->_sourceStorage = $sourceStorage;
    }

    /**
     * @return StorageInterface override content storage instance.
     */
    public function getOverrideStorage()
    {
        if (!is_object($this->_overrideStorage) || $this->_overrideStorage instanceof \Closure) {
            $this->_overrideStorage = Instance::ensure($this->_overrideStorage, 'yii2tech\content\StorageInterface');
        }
        return $this->_overrideStorage;
    }

    /**
     * @param StorageInterface|array|\Closure $overrideStorage override content storage instance or its DI compatible configuration.
     */
    public function setOverrideStorage($overrideStorage)
    {
        $this->_overrideStorage = $overrideStorage;
    }

    /**
     * Returns content Item matching given ID.
     * @param string $id content item ID.
     * @return Item content item instance.
     */
    public function get($id)
    {
        $data = $this->getOverrideStorage()->find($id);
        if ($data === null) {
            $data = $this->getSourceStorage()->find($id);
            if ($data === null) {
                throw new InvalidParamException("Content item '{$id}' does not exist.");
            }
        }
        return $this->createItem($data);
    }

    /**
     * Creates new content item instance.
     * @param array $contents item content parts.
     * @return Item content item instance.
     * @throws \yii\base\InvalidConfigException
     */
    protected function createItem(array $contents)
    {
        /* @var $item Item */
        $item = Yii::createObject($this->itemConfig);
        $item->setManager($this);
        $item->setContents($contents);
        return $item;
    }
}