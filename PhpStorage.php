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
use yii\helpers\VarDumper;

/**
 * PhpStorage performs data storage inside PHP code files.
 * Files are stored under [[filePath]]. Each particular content record represented by separated file,
 * which should return array of content parts.
 * For example:
 *
 * ```php
 * <?php
 *
 * return [
 *     'title' => 'About',
 *     'body' => 'About page content',
 * ];
 * ```
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
        $fileName = $this->composeFileName($id);
        $content = "<?php\n\nreturn " . VarDumper::export($data) . ";";

        if (file_exists($fileName)) {
            unlink($fileName);
        } else {
            FileHelper::createDirectory(dirname($fileName));
        }

        $bytesWritten = file_put_contents($fileName, $content);
        if ($bytesWritten <= 0) {
            throw new Exception("Unable to write file '{$fileName}'.");
        }
        $this->invalidateScriptCache($fileName);
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
        return require $fileName;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $path = Yii::getAlias($this->filePath);
        $files = FileHelper::findFiles($path, [
            'only' => ['*.php']
        ]);
        $items = [];
        foreach ($files as $file) {
            $id = substr($file, strlen($path) + 1, -4);
            $items[$id] = require $file;
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
            $this->invalidateScriptCache($fileName);
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
        return Yii::getAlias($this->filePath) . DIRECTORY_SEPARATOR . $id . '.php';
    }

    /**
     * Invalidates pre-compiled script cache (such as OPCache or APC) for the given file.
     * @param string $fileName file name.
     */
    protected function invalidateScriptCache($fileName)
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($fileName, true);
        }
        if (function_exists('apc_delete_file')) {
            @apc_delete_file($fileName);
        }
    }
}