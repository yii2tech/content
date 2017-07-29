<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\Component;

/**
 * PlaceholderRenderer performs content rendering via simple string placeholder replacement.
 * Rendering data will be inserted into corresponding placeholders, marked by brackets (`{}`).
 * For example:
 *
 * ```php
 * echo $renderer->render('Hello, {name}', ['name' => 'John']); // outputs 'Hell, John'
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class PlaceholderRenderer extends Component implements RendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render($content, array $data)
    {
        $replacePairs = [];
        foreach ($data as $name => $value) {
            $replacePairs['{' . $name . '}'] = $value;
        }
        return strtr($content, $replacePairs);
    }
}