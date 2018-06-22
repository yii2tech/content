<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Json;

/**
 * JsonStorage performs data storage inside local files in JSON format.
 * Files are stored under [[filePath]]. Each particular content record represented by separated file,
 * which should hold JSON for content parts.
 * For example:
 *
 * ```json
 * {
 *     "title": "About",
 *     "body": "About page content"
 * }
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class JsonStorage extends Component implements StorageInterface
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
        $fileName = $this->composeFileName($id);
        $content = Json::encode($data);

        if (file_exists($fileName)) {
            unlink($fileName);
        } else {
            FileHelper::createDirectory(dirname($fileName));
        }

        $bytesWritten = file_put_contents($fileName, $content);
        if ($bytesWritten <= 0) {
            throw new Exception("Unable to write file '{$fileName}'.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $fileName = $this->composeFileName($id);
        if (!file_exists($fileName)) {
            return null;
        }
        return Json::decode(file_get_contents($fileName));
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $path = Yii::getAlias($this->filePath);
        if (!file_exists($path)) {
            return [];
        }

        $files = FileHelper::findFiles($path, [
            'only' => ['*.json']
        ]);
        $items = [];
        foreach ($files as $file) {
            $id = substr($file, strlen($path) + 1, -5);
            $items[$id] = Json::decode(file_get_contents($file));
        }
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $fileName = $this->composeFileName($id);
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    /**
     * Composes full name of the file, which should store specified content item.
     * @param string $id content item ID.
     * @return string name of the file.
     */
    protected function composeFileName($id)
    {
        $id = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $id);
        return Yii::getAlias($this->filePath) . DIRECTORY_SEPARATOR . $id . '.json';
    }
}