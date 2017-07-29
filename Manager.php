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
 * @property RendererInterface|array|\Closure $renderer content renderer.
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
     * @var RendererInterface|array|\Closure content parser.
     */
    private $_renderer = ['class' => 'yii2tech\content\PlaceholderRenderer'];
    /**
     * @var StorageInterface|array|\Closure source content storage.
     */
    private $_sourceStorage;
    /**
     * @var StorageInterface|array|\Closure override content storage.
     */
    private $_overrideStorage;


    /**
     * @return RendererInterface renderer instance.
     */
    public function getRenderer()
    {
        if (!is_object($this->_renderer) || $this->_renderer instanceof \Closure) {
            $this->_renderer = Instance::ensure($this->_renderer, 'yii2tech\content\RendererInterface');
        }
        return $this->_renderer;
    }

    /**
     * @param RendererInterface|array|\Closure $renderer renderer instance or its DI compatible configuration.
     */
    public function setRenderer($renderer)
    {
        $this->_renderer = $renderer;
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
        return $this->createItem($id, $data);
    }

    /**
     * Returns all items present in the storage.
     * @return Item[] list of content items indexed by their IDs
     */
    public function getAll()
    {
        $rows = array_merge(
            $this->getSourceStorage()->findAll(),
            $this->getOverrideStorage()->findAll()
        );
        $items = [];
        foreach ($rows as $id => $data) {
            $items[$id] = $this->createItem($id, $data);
        }
        return $items;
    }

    /**
     * Resets the content for specified item, removing its override value.
     * @param string $id content item ID.
     */
    public function reset($id)
    {
        $this->getOverrideStorage()->delete($id);
    }

    /**
     * Saves new content item data, creating an override.
     * @param string $id content item ID.
     * @param array $data content item data.
     */
    public function save($id, array $data)
    {
        $this->getOverrideStorage()->save($id, $data);
    }

    /**
     * Creates new content item instance.
     * @param string $id item ID.
     * @param array $contents item content parts.
     * @return Item content item instance.
     * @throws \yii\base\InvalidConfigException
     */
    protected function createItem($id, array $contents)
    {
        /* @var $item Item */
        $item = Yii::createObject($this->itemConfig);
        $item->setManager($this);
        $item->setId($id);
        $item->setContents($contents);
        return $item;
    }
}