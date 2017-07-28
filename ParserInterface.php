<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

/**
 * ParserInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface ParserInterface
{
    /**
     * Parses given content applying particular data.
     * @param string $content raw content to be parsed.
     * @param array $data content data in format: `[name => value]`
     * @return string parsed content
     */
    public function parse($content, array $data);
}