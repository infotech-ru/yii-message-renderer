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
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;

/**
 * Abstract Message Render Context class
 */
abstract class MessageContext
{
    /**
     * @var array
     */
    private $placeholdersConfig;

    public function __construct()
    {
        $this->setPlaceholdersConfig($this->placeholdersConfig());
    }

    /**
     * @param string $template
     * @param object|array $data
     *
     * @return string
     */
    public function renderTemplate($template, $data)
    {
        return strtr($template, $this->getPlaceholdersData($data));
    }

    /**
     * @param array $config
     *
     * @see MessageContext::placeholdersConfig()
     *
     * @return MessageContext
     */
    public function setPlaceholdersConfig($config)
    {
        /* @todo normalize configuration array */
        $this->placeholdersConfig = $config;

        return $this;
    }

    /**
     * Fetch placeholders data
     * @return array [placeholder => value-string]
     */
    public function getPlaceholdersData($data)
    {
        return array_map(
            function ($config) use ($data) {
                return trim(self::fetchData($config['fetcher'], $data))
                    ?: (isset($config['empty']) ? (string)$config['empty'] : '');
            },
            $this->placeholdersConfig
        );
    }

    /**
     * Справочные данные по подстановкам
     * @return array [подстановка => ['title' => название подстановки, 'description' => описание подстановки]]
     */
    public function getPlaceholdersInfo()
    {
        return array_map(
            function ($config) {
                return array_intersect_key($config, array('title' => true, 'description' => true));
            },
            $this->placeholdersConfig
        );
    }

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
    private static function fetchDataByPropertyPath($path, $data)
    {
        try {
            return PropertyAccess::createPropertyAccessor()->getValue($data, $path);
        } catch (UnexpectedTypeException $e) {
             return null;
        } catch (NoSuchPropertyException $e) {
             return null;
        }
    }

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * Set up method for placeholders configuration.
     *
     * <code>
     * [
     *     '%PLACEHOLDER_1%' => [
     *         'title' => 'Substitution 1', // Human readable string using as hint for template editing
     *         'description' => 'Description for Substitution 1',
     *         'fetcher' => 'property.path[0]', // propertyPath or callable (data) that returns string
     *         'empty' => '(unknown)', // used if fetcher gives an empty string
     *     ],
     *     ...
     * ]
     * </code>
     *
     * @return array
     */
    abstract protected function placeholdersConfig();
}
