<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\Component;

/**
 * ActiveRecordStorage uses ActiveRecord class for content storage.
 * This storage allows usage of any DBMS, which have ActiveRecord interface implemented, such as
 * relational DB, MongoDB, Redis etc. However, it may lack efficiency comparing to the dedicated storages
 * like [[DbStorage]] or [[MongoDbStorage]].
 *
 * Configuration example:
 *
 * ```php
 * [
 *     'class' => 'yii2tech\content\ActiveRecordStorage',
 *     'activeRecordClass' => 'app\models\Page',
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
class ActiveRecordStorage extends Component implements StorageInterface
{
    use StorageFilterTrait;

    /**
     * @var string  name of the ActiveRecord class, which should be used for content storage.
     * This class should match [[\yii\db\ActiveRecordInterface]] interface.
     */
    public $activeRecordClass;
    /**
     * @var string name of the ActiveRecord attribute, which should store content ID.
     */
    public $idAttribute = 'id';
    /**
     * @var string[] list of ActiveRecord attributes, which should store content parts.
     */
    public $contentAttributes = [];


    /**
     * {@inheritdoc}
     */
    public function save($id, array $data)
    {
        $model = $this->findModel($id);

        if ($model === null) {
            $model = new $this->activeRecordClass();
            foreach ($this->composeFilterAttributes([$this->idAttribute => $id]) as $attribute => $value) {
                $model->{$attribute} = $value;
            }
        }

        foreach ($data as $attribute => $value) {
            $model->{$attribute} = $value;
        }
        $model->save(false);
        $this->ensureModelGc($model);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $model = $this->findModel($id);
        if ($model === null) {
            return null;
        }
        $contents = $this->extractModelContents($model);
        $this->ensureModelGc($model);
        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        /* @var $modelClass \yii\db\ActiveRecordInterface */
        $modelClass = $this->activeRecordClass;
        $models = $modelClass::find()
            ->andWhere($this->composeFilterAttributes())
            ->all();

        $rows = [];
        foreach ($models as $model) {
            $rows[$model->{$this->idAttribute}] = $this->extractModelContents($model);
            $this->ensureModelGc($model);
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $model = $this->findModel($id);
        if ($model !== null) {
            $model->delete();
            $this->ensureModelGc($model);
        }
    }

    /**
     * Finds ActiveRecord instance for specified content ID.
     * @param string $id content ID.
     * @return \yii\db\ActiveRecordInterface|null model instance, `null` - if not found.
     */
    protected function findModel($id)
    {
        /* @var $modelClass \yii\db\ActiveRecordInterface */
        $modelClass = $this->activeRecordClass;
        return $modelClass::find()
            ->andWhere($this->composeFilterAttributes([$this->idAttribute => $id]))
            ->one();
    }

    /**
     * Extracts [[contentAttributes]] values from a model instance.
     * @param \yii\db\ActiveRecordInterface $model source model instance.
     * @return array contents.
     */
    protected function extractModelContents($model)
    {
        $contents = [];
        foreach ($this->contentAttributes as $attribute) {
            $contents[$attribute] = $model->{$attribute};
        }
        return $contents;
    }

    /**
     * Ensures model garbage collection, attempting to remove cycle references produced by behaviors.
     * @param \yii\db\ActiveRecordInterface|Component $model model instance.
     */
    protected function ensureModelGc($model)
    {
        if ($model instanceof Component) {
            $model->detachBehaviors();
        }
    }
}