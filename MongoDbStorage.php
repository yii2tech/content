<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\Component;
use yii\di\Instance;
use yii\mongodb\Connection;
use yii\mongodb\Query;

/**
 * MongoDbStorage represents the content storage based on MongoDB collection.
 *
 * This storage requires [yiisoft/yii2-mongodb](https://github.com/yiisoft/yii2-mongodb) extension installed.
 * This can be done via composer:
 *
 * ```
 * composer require --prefer-dist yiisoft/yii2-mongodb
 * ```
 *
 * Configuration example:
 *
 * ```php
 * [
 *     'class' => 'yii2tech\content\MongoDbStorage',
 *     'collection' => 'Content',
 *     'contentAttributes' => [
 *         'title',
 *         'body',
 *     ],
 * ]
 * ```
 *
 * The collection to be used can be changed by setting [[collection]].
 * This collection is better to be pre-created with field [[idAttribute]] indexed.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class MongoDbStorage extends Component implements StorageInterface
{
    use StorageFilterTrait;

    /**
     * @var Connection|array|string the MongoDB connection object or the application component ID of the MongoDB connection.
     * After the storage object is created, if you want to change this property, you should only assign it
     * with a MongoDB connection object.
     */
    public $db = 'mongodb';
    /**
     * @var string|array name of the MongoDB collection, which should store account records.
     */
    public $collection;
    /**
     * @var string name of the document column, which should store content ID.
     */
    public $idAttribute = 'id';
    /**
     * @var string[] list of document columns, which should store content parts.
     */
    public $contentAttributes = [];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, array $data)
    {
        $rowExists = (new Query())
            ->from($this->collection)
            ->andWhere($this->composeFilterAttributes([$this->idAttribute => $id]))
            ->exists($this->db);

        if ($rowExists) {
            $this->db->getCollection($this->collection)->update($this->composeFilterAttributes([$this->idAttribute => $id]), $data);
        } else {
            $data[$this->idAttribute] = $id;
            $this->db->getCollection($this->collection)->insert($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $row = (new Query())
            ->select(array_merge($this->contentAttributes, ['_id' => false]))
            ->from($this->collection)
            ->andWhere($this->composeFilterAttributes([$this->idAttribute => $id]))
            ->one($this->db);

        if ($row === false) {
            return null;
        }
        return $row;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $rows = (new Query())
            ->select(array_merge($this->contentAttributes, ['_id' => false], [$this->idAttribute => true]))
            ->from($this->collection)
            ->andWhere($this->composeFilterAttributes())
            ->indexBy($this->idAttribute)
            ->all($this->db);

        foreach ($rows as &$row) {
            unset($row[$this->idAttribute]);
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $this->db->getCollection($this->collection)->remove($this->composeFilterAttributes([$this->idAttribute => $id]));
    }
}