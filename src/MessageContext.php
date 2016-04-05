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
     * @param string $template
     *
     * @return string
     */
    public function renderSample($template)
    {
        return strtr($template, $this->getPlaceholdersSamples());
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
                return trim(DataFetcher::fetchData($config['fetcher'], $data))
                    ?: (isset($config['empty']) ? (string)$config['empty'] : '');
            },
            $this->placeholdersConfig
        );
    }

    /**
     * Returns description of placeholders
     * @return array [placeholder => ['title' => string, 'description' => string, 'sample' => string], ...]
     */
    public function getPlaceholdersInfo()
    {
        return array_map(
            function ($config) {
                return array_intersect_key($config, array('title' => true, 'description' => true, 'sample' => true));
            },
            $this->placeholdersConfig
        );
    }

    public function getPlaceholdersSamples()
    {
        return array_map(
            function ($config) {
                return isset($config['sample'])
                    ? $config['sample']
                    : (isset($config['empty']) ? (string)$config['empty'] : '');
            },
            $this->placeholdersConfig
        );
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
     *         'sample' => 'Substitution 1 Sample', // for testing of template rendering
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
