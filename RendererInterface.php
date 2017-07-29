<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

/**
 * RendererInterface is the interface that must be implemented by content renderer classes.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface RendererInterface
{
    /**
     * Renders given content applying particular data.
     * @param string $content raw content to be rendered.
     * @param array $data content data in format: `[name => value]`
     * @return string rendered content.
     */
    public function render($content, array $data);
}