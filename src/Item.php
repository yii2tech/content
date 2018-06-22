<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\InvalidArgumentException;
use yii\base\Model;

/**
 * Item represents particular content item.
 * It consists of several content parts determined by [[contents]].
 * This class is a descendant of [[Model]], which uses content parts as model attributes.
 *
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
    public $manager;
    /**
     * @var string this item ID.
     */
    public $id;
    /**
     * @var array|\Closure validation rules in format matching return type of [[rules()]].
     * It could be also specified as a PHP callback, which should return actual validation rules.
     * For example:
     *
     * ```php
     * function (\yii2tech\content\Item $model) {
     *     //return [...];
     * }
     * ```
     *
     * In case not set - default validation rules will be generated making all attributes to be 'required'.
     * @see rules()
     */
    public $rules;
    /**
     * @var array|\Closure attribute labels in format matching return type of [[attributeLabels()]].
     * It could be also specified as a PHP callback, which should return actual attribute labels.
     * For example:
     *
     * ```php
     * function (\yii2tech\content\Item $model) {
     *     //return [...];
     * }
     * ```
     *
     * @see attributeLabels()
     */
    public $labels;
    /**
     * @var array|\Closure attribute hints in format matching return type of [[attributeHints()]].
     * It could be also specified as a PHP callback, which should return actual attribute hints.
     * For example:
     *
     * ```php
     * function (\yii2tech\content\Item $model) {
     *     //return [...];
     * }
     * ```
     *
     * @see attributeHints()
     */
    public $hints;

    /**
     * @var array related contents in format: `[id => content]`.
     */
    private $_contents = [];
    /**
     * @var array meta data associated with this item.
     */
    private $_metaData;


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
            $this->_metaData = $this->manager->getMetaData($this->id);
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
     * Returns value of the specified content part.
     * @param string $id content part ID.
     * @return string content.
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException("Content part '{$id}' does not exist.");
        }
        return $this->_contents[$id];
    }

    /**
     * Checks whether this item has particular content part.
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
        $manager = $this->manager;
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
        $this->manager->save($this->id, $this->getContents());
        return true;
    }

    /**
     * Resets own content, removing its overridden value.
     * @param bool $refresh whether to refresh this item contents after reset is done. Defaults to `true`.
     */
    public function reset($refresh = true)
    {
        $this->manager->reset($this->id);
        if ($refresh) {
            $refreshItem = $this->manager->get($this->id);
            $this->setContents($refreshItem->getContents());
        }
    }

    // Model specifics :

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        $attributes = array_merge(
            parent::attributes(),
            array_keys($this->_contents)
        );
        return array_values(array_diff($attributes, ['id', 'manager', 'rules', 'labels', 'hints']));
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        if (empty($this->rules)) {
            return [
                [$this->attributes(), 'required'],
            ];
        }
        if ($this->rules instanceof \Closure) {
            return call_user_func($this->rules, $this);
        }
        return $this->rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = [
            'id' => 'ID'
        ];
        if (empty($this->labels)) {
            return $labels;
        }
        if ($this->labels instanceof \Closure) {
            return array_merge($labels, call_user_func($this->labels, $this));
        }
        return array_merge($labels, $this->labels);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        if (empty($this->hints)) {
            return [];
        }
        if ($this->hints instanceof \Closure) {
            return call_user_func($this->hints, $this);
        }
        return $this->hints;
    }

    // Magic property access :

    /**
     * PHP getter magic method.
     * This method is overridden so that content parts can be accessed like properties.
     *
     * @param string $name property name
     * @throws \yii\base\InvalidArgumentException if relation name is wrong
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