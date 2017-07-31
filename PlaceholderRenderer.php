<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * PlaceholderRenderer performs content rendering via simple string placeholder replacement.
 * Rendering data will be inserted into corresponding placeholders, marked by brackets (`{{placeholderName}}`).
 * For example:
 *
 * ```php
 * echo $renderer->render('Hello, {{name}}', ['name' => 'John']); // outputs 'Hell, John'
 * ```
 *
 * This renderer also allows usage of multi-level arrays or objects in render data, using dot (`.`) as separator for the
 * level keys. For example:
 *
 * ```php
 * echo $renderer->render('Hello, {{user.name}}', ['user' => ['name' => 'John']]); // outputs 'Hell, John'
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class PlaceholderRenderer extends Component implements RendererInterface
{
    /**
     * @var string placeholder left delimiter.
     */
    public $leftDelimiter = '{{';
    /**
     * @var string placeholder right delimiter.
     */
    public $rightDelimiter = '}}';


    /**
     * {@inheritdoc}
     */
    public function render($content, array $data)
    {
        if (empty($data)) {
            return $content;
        }

        return preg_replace_callback('/' . preg_quote($this->leftDelimiter) . '([\\w\\.]+)' . preg_quote($this->rightDelimiter) . '/', function ($matches) use ($data) {
            $placeholderName = $matches[1];

            if (strpos($placeholderName, '.') !== false) {
                $placeholderNameParts = explode('.', $placeholderName);
                return ArrayHelper::getValue($data, $placeholderNameParts, $placeholderName);
            }

            if (isset($data[$placeholderName])) {
                return $data[$placeholderName];
            }
            return $matches[0];
        }, $content);
    }
}