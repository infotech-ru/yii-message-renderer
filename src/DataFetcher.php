<?php
/*
 * This file is part of the infotech/yii-message-renderer package.
 *
 * (c) Infotech, Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infotech\MessageRenderer;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DataFetcher
{

    /**
     * Fetch a data element from an object or an array
     *
     * @param callable|string $fetcher property path or callback
     * @param object|array $data
     *
     * @return mixed
     */
    public static function fetchData($fetcher, $data)
    {
        return is_callable($fetcher)
            ? call_user_func($fetcher, $data)
            : self::fetchDataByPropertyPath((string)$fetcher, $data);
    }

    /**
     * Fetch a data element from an object or an array by property path
     *
     * @param string $path property path or callback
     * @param object|array $data
     *
     * @return mixed
     */
    public static function fetchDataByPropertyPath($path, $data)
    {
        try {
            return PropertyAccess::createPropertyAccessor()->getValue($data, $path);
        } catch (UnexpectedTypeException $e) {
             return null;
        } catch (NoSuchPropertyException $e) {
             return null;
        }
    }
}
