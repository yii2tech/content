<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\Component;

/**
 * PhpStorage
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class PhpStorage extends Component implements StorageInterface
{
    /**
     * @var string file path to save data files.
     * Yii path aliases can be used here.
     */
    public $filePath = '@app/data/content';


    /**
     * {@inheritdoc}
     */
    public function save($id, array $data)
    {
        // TODO: Implement save() method.
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        // TODO: Implement delete() method.
    }
}