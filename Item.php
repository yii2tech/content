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
 * @property array $contents content parts in format: `[id => content]`.
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
     * @var array related contents in format: `[id => content]`.
     */
    private $_contents = [];


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
     * Parses specified content part.
     * @param string $id content part ID.
     * @param array $data parsing data.
     * @return string parsed content.
     */
    public function parse($id, array $data = [])
    {
        return $this->manager->getParser()->parse($this->get($id), $data);
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