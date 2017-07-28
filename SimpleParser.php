<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\Component;

/**
 * SimpleParser
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class SimpleParser extends Component implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($content, array $data)
    {
        $replacePairs = [];
        foreach ($data as $name => $value) {
            $replacePairs['{' . $name . '}'] = $value;
        }
        return strtr($content, $replacePairs);
    }
}