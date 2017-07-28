<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\content;

/**
 * StorageInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface StorageInterface
{
    /**
     * Saves data for the particular content item.
     * @param string $id content item ID.
     * @param array $data data to be saved.
     */
    public function save($id, array $data);

    /**
     * Finds data for particular content item.
     * @param string $id content item ID.
     * @return array|null content item data, `null` if no data found.
     */
    public function find($id);

    /**
     * Deletes data for particular content item.
     * @param string $id content item ID.
     */
    public function delete($id);
}