<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

use yii\base\Component;

/**
 * PhpEvalRenderer performs content rendering evaluating it as a PHP code.
 * Render data will be extracted as internal variables for the evaluation.
 *
 * For example:
 *
 * ```php
 * echo $renderer->render('Hello, <?= $name ?>', ['name' => 'John']); // outputs 'Hell, John'
 * ```
 *
 * > Caution: while using PHP evaluation provides greatest flexibility for content template composition,
 * it is not recommended to be used as it produces security risks, allowing  execution of arbitrary PHP code.
 *
 * @see eval()
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class PhpEvalRenderer extends Component implements RendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render($content, array $data)
    {
        return $this->evaluatePhpCode($content, $data);
    }

    /**
     * Evaluates PHP code with given data.
     * @param string $_code_ PHP code for evaluation
     * @param array $_data_ data for evaluation.
     * @return string render result.
     * @throws \Exception on error.
     * @throws \Throwable on error.
     */
    protected function evaluatePhpCode($_code_, array $_data_)
    {
        $_code_ = '?>' . $_code_;

        $_obInitialLevel_ = ob_get_level();
        ob_start();
        ob_implicit_flush(false);
        extract($_data_, EXTR_OVERWRITE);
        try {
            eval($_code_);
            return ob_get_clean();
        } catch (\Exception $e) {
            while (ob_get_level() > $_obInitialLevel_) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        } catch (\Throwable $e) {
            while (ob_get_level() > $_obInitialLevel_) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }
}