<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\InvalidParamException;
use yii\base\Model;

/**
 * Item represents particular content item.
 * It consists of several content parts determined by [[contents]].
 *
 * @property Manager $manager related content manager reference.
 * @property string $id this item ID.
 * @property array $contents content parts in format: `[id => content]`.
 * @property array $metaData  meta data associated with this item.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Item extends Model
{
    /**
     * @var Manager related content manager reference.
     */
    private $_manager;
    /**
     * @var string this item ID.
     */
    private $_id;
    /**
     * @var array related contents in format: `[id => content]`.
     */
    private $_contents = [];
    /**
     * @var array meta data associated with this item.
     */
    private $_metaData;


    /**
     * @return Manager related content manager reference.
     */
    public function getManager()
    {
        return $this->_manager;
    }

    /**
     * @param Manager $manager related content manager reference.
     */
    public function setManager($manager)
    {
        $this->_manager = $manager;
    }

    /**
     * @return string this item ID
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param string $id this item ID
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return array contents in format: `[id => content]`.
     */
    public function getContents()
    {
        return $this->_contents;
    }

    /**
     * @param array $contents contents in format: `[id => content]`.
     */
    public function setContents(array $contents)
    {
        $this->_contents = $contents;
    }

    /**
     * @return array meta data associated with this item.
     */
    public function getMetaData()
    {
        if ($this->_metaData === null) {
            $this->_metaData = $this->getManager()->getMetaData($this->getId());
        }
        return $this->_metaData;
    }

    /**
     * @param array $metaData meta data associated with this item.
     */
    public function setMetaData($metaData)
    {
        $this->_metaData = $metaData;
    }

    /**
     * @param string $id content part ID.
     * @return string content.
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new InvalidParamException("Content part '{$id}' does not exist.");
        }
        return $this->_contents[$id];
    }

    /**
     * @param string $id content part ID.
     * @return bool whether content part exists or not.
     */
    public function has($id)
    {
        return array_key_exists($id, $this->_contents);
    }

    /**
     * Renders specified content part.
     * @param string $id content part ID.
     * @param array $data content data.
     * @return string parsed content.
     */
    public function render($id, array $data = [])
    {
        $manager = $this->getManager();
        if (!empty($manager->defaultRenderData)) {
            $data = array_merge(
                $manager->defaultRenderData instanceof \Closure ? call_user_func($manager->defaultRenderData) : $manager->defaultRenderData,
                $data
            );
        }
        return $manager->getRenderer()->render($this->get($id), $data);
    }

    /**
     * Saves this item data, creating an override.
     * @param bool $runValidation whether to perform validation (calling [[validate()]])
     * before saving the item. Defaults to `true`.
     * @return bool whether the saving succeeded (i.e. no validation errors occurred).
     */
    public function save($runValidation = true)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }
        $this->getManager()->save($this->getId(), $this->getContents());
        return true;
    }

    /**
     * Resets own content, removing its overridden value.
     * @param bool $refresh whether to refresh this item contents after reset is done. Defaults to `true`.
     */
    public function reset($refresh = true)
    {
        $this->getManager()->reset($this->getId());
        if ($refresh) {
            $refreshItem = $this->getManager()->get($this->getId());
            $this->setContents($refreshItem->getContents());
        }
    }

    // Model specifics :

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            array_keys($this->_contents)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [$this->attributes(), 'required'],
        ];
    }

    // Magic property access :

    /**
     * PHP getter magic method.
     * This method is overridden so that content parts can be accessed like properties.
     *
     * @param string $name property name
     * @throws \yii\base\InvalidParamException if relation name is wrong
     * @return mixed property value
     * @see getAttribute()
     */
    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        }
        return parent::__get($name);
    }

    /**
     * PHP setter magic method.
     * This method is overridden so content parts can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     */
    public function __set($name, $value)
    {
        if ($this->has($name)) {
            $this->_contents[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * Checks if a property value is null.
     * This method overrides the parent implementation by checking if the content part is `null` or not.
     * @param string $name the property name or the event name
     * @return bool whether the property value is null
     */
    public function __isset($name)
    {
        if ($this->has($name)) {
            return $this->get($name) !== null;
        }
        return parent::__isset($name);
    }

    /**
     * Sets a component property to be null.
     * This method overrides the parent implementation by clearing
     * the specified content part value.
     * @param string $name the property name or the event name
     */
    public function __unset($name)
    {
        if ($this->has($name)) {
            $this->_contents[$name] = null;
        } else {
            parent::__unset($name);
        }
    }
}