<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\Component;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;

/**
 * DbStorage represents the content storage based on database table.
 * Example migration for such table:
 *
 * ```php
 * $tableName = 'Page';
 * $columns = [
 *     'id' => 'string',
 *     'title' => 'string',
 *     'body' => 'text',
 *     'PRIMARY KEY(id)',
 * ];
 * $this->createTable($tableName, $columns);
 * ```
 *
 * Configuration example:
 *
 * ```php
 * [
 *     'class' => 'yii2tech\content\DbStorage',
 *     'table' => '{{%Page}}',
 *     'contentAttributes' => [
 *         'title',
 *         'body',
 *     ],
 * ]
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class DbStorage extends Component implements StorageInterface
{
    use StorageFilterTrait;

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the storage object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     */
    public $db = 'db';
    /**
     * @var string string name of the DB table to store content data.
     */
    public $table;
    /**
     * @var string name of the table column, which should store content ID.
     */
    public $idAttribute = 'id';
    /**
     * @var string[] list of table columns, which should store content parts.
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
        $existingRow = $this->findRow($id);

        if ($existingRow === false) {
            $data[$this->idAttribute] = $id;
            $this->db->createCommand()
                ->insert($this->table, $this->composeFilterAttributes($data))
                ->execute();
        } else {
            $this->db->createCommand()
                ->update($this->table, $data, $this->composeFilterAttributes([$this->idAttribute => $id]))
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $row = $this->findRow($id);
        if ($row === false) {
            return null;
        }
        unset($row[$this->idAttribute]);
        return $row;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $rows = (new Query())
            ->select($this->idAttribute)
            ->addSelect($this->contentAttributes)
            ->from($this->table)
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
        $this->db->createCommand()
            ->delete($this->table, $this->composeFilterAttributes([$this->idAttribute => $id]))
            ->execute();
    }

    /**
     * Finds database record for specified content ID.
     * @param string $id content ID.
     * @return array|false row data, `false` - if not found.
     */
    protected function findRow($id)
    {
        return (new Query())
            ->from($this->table)
            ->andWhere($this->composeFilterAttributes([$this->idAttribute => $id]))
            ->one($this->db);
    }
}