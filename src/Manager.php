<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use Yii;
use yii\base\Component;
use yii\di\Instance;

/**
 * Manager is an application component, which provides high-level content management interface.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'pageContentManager' => [
 *             'class' => 'yii2tech\content\Manager',
 *             'sourceStorage' => [
 *                 'class' => 'yii2tech\content\PhpStorage',
 *                 'filePath' => '@app/data/pages',
 *             ],
 *             'overrideStorage' => [
 *                 'class' => 'yii2tech\content\DbStorage',
 *                 'table' => '{{%Page}}',
 *                 'contentAttributes' => [
 *                     'title',
 *                     'body',
 *                 ],
 *             ],
 *         ],
 *     ],
 *     // ...
 * ];
 * ```
 *
 * Usage example:
 *
 * ```php
 * echo Yii::$app->pageContentManager->get('about')->render('body');
 * ```
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
     * @see Item
     */
    public $itemConfig = ['class' => 'yii2tech\content\Item'];
    /**
     * @var array|callable default rendering data, which should be applied for each item rendering.
     * For example:
     *
     * ```php
     * [
     *     'appName' => 'My Application',
     *     'appEmail' => 'my.application@example.com',
     * ]
     * ```
     *
     * It can be specified as PHP callback, which should return actual render data, for example:
     *
     * ```php
     * function () {
     *     return [
     *         'appName' => Yii::$app->name,
     *         'baseUrl' => Yii::$app->request->baseUrl,
     *     ];
     * }
     * ```
     *
     * @see Item::render()
     */
    public $defaultRenderData = [];
    /**
     * @var string[] list of content parts, which are used to store meta data, which should not
     * be overridden. Meta data content parts can be used to store comments for content set,
     * description for placeholders and so on.
     * These content parts will not be returned by [[get()]] or [[getAll()]], use [[getMetaData()]]
     * to retrieve them.
     */
    public $metaDataContentParts = [];

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
     * @throws ItemNotFoundException if item not found.
     */
    public function get($id)
    {
        $data = $this->getOverrideStorage()->find($id);
        if ($data === null) {
            $data = $this->getSourceStorage()->find($id);
            if ($data === null) {
                throw new ItemNotFoundException("Content item '{$id}' does not exist.");
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
     * Returns the content item meta data from the [[sourceStorage]] according to [[metaDataContentParts]].
     * @param string $id content ID.
     * @return array meta data.
     */
    public function getMetaData($id)
    {
        if (empty($this->metaDataContentParts)) {
            return [];
        }

        $data = $this->getSourceStorage()->find($id);
        if ($data === null) {
            return [];
        }

        $metaData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->metaDataContentParts, true)) {
                $metaData[$key] = $value;
            }
        }

        return $metaData;
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
        $item->manager = $this;
        $item->id = $id;

        foreach ($this->metaDataContentParts as $contentPartName) {
            if (isset($contents[$contentPartName])) {
                unset($contents[$contentPartName]);
            }
        }

        $item->setContents($contents);
        return $item;
    }
}