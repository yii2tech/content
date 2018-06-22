<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\Component;
use yii\di\Instance;

/**
 * MustacheRenderer performs content rendering using [Mustache](https://mustache.github.io/).
 *
 * This renderer requires [mustache/mustache](https://github.com/bobthecow/mustache.php) extension installed.
 * This can be done via composer:
 *
 * ```
 * composer require --prefer-dist mustache/mustache
 * ```
 *
 * example:
 *
 * ```php
 * echo $renderer->render('Hello, {{name}}', ['name' => 'John']); // outputs 'Hell, John'
 * ```
 *
 * @see https://mustache.github.io/
 * @see https://github.com/bobthecow/mustache.php
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class MustacheRenderer extends Component implements RendererInterface
{
    /**
     * @var \Mustache_Engine|\Closure Mustache engine instance.
     */
    private $_engine = ['class' => 'Mustache_Engine'];


    /**
     * @return \Mustache_Engine Mustache engine instance.
     */
    public function getEngine()
    {
        if (!$this->_engine instanceof \Mustache_Engine) {
            $this->_engine = Instance::ensure($this->_engine, 'Mustache_Engine');
        }
        return $this->_engine;
    }

    /**
     * @param \Closure|\Mustache_Engine $engine Mustache engine instance or its DI compatible configuration.
     */
    public function setEngine($engine)
    {
        $this->_engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function render($content, array $data)
    {
        return $this->getEngine()->render($content, $data);
    }
}