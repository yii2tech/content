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
 * DbStorage
 *
 * @property string $idColumnName name of the table column, which should store content ID.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class DbStorage extends Component implements StorageInterface
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the storage object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     */
    public $db = 'db';
    /**
     * @var string string name of the DB table to store content data.
     */
    public $table = '{{%Content}}';

    /**
     * @var string name of the table column, which should store content ID.
     */
    private $_idColumnName;


    /**
     * @return string name of the table column, which should store content ID.
     */
    public function getIdColumnName()
    {
        if ($this->_idColumnName === null) {
            $primaryKeys = $this->db->getTableSchema($this->table)->primaryKey;
            $this->_idColumnName = array_shift($primaryKeys);
        }
        return $this->_idColumnName;
    }

    /**
     * @param string $idColumnName name of the table column, which should store content ID.
     */
    public function setIdColumnName($idColumnName)
    {
        $this->_idColumnName = $idColumnName;
    }

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
            $data[$this->getIdColumnName()] = $id;
            $this->db->createCommand()
                ->insert($this->table, $data)
                ->execute();
        } else {
            $primaryKeys = $this->db->getTableSchema($this->table)->primaryKey;
            $condition = [];
            foreach ($primaryKeys as $columnName) {
                $condition[$columnName] = $existingRow[$columnName];
            }
            $this->db->createCommand()
                ->update($this->table, $data, $condition)
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
        unset($row[$this->getIdColumnName()]);
        return $row;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    protected function findRow($id)
    {
        $row = (new Query())
            ->from($this->table)
            ->andWhere([$this->getIdColumnName() => $id])
            ->one();
        return $row;
    }
}