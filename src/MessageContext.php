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

use CHtml;
use InvalidArgumentException;

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
     * @param string|array $template
     * @param object|array $data
     *
     * @return string|array String or array of strings with keys from $template array rendered with given data
     *
     * @throws IncompleteDataException if $placeholders has insufficient data for rendering the $template
     * @throws InvalidArgumentException if $template is not a string nor an array
     */
    public function renderTemplate($template, $data)
    {
        return $this->render($template, $this->getPlaceholdersData($template, $data));
    }

    /**
     * @param string|array $template
     *
     * @return string|array String or array of strings with keys from $template array rendered with sample data
     *
     * @throws IncompleteDataException if $placeholders has insufficient data for rendering the $template
     * @throws InvalidArgumentException if $template is not a string nor an array
     */
    public function renderSample($template)
    {
        return $this->render($template, $this->getPlaceholdersSamples($template));
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
     * Fetch placeholders data for given template
     *
     * @param string|array $template
     * @param array|object $data
     *
     * @return array [placeholder => value-string]
     */
    public function getPlaceholdersData($template, $data)
    {
        return array_map(
            function ($config) use ($data) {
                return trim(CHtml::value($data, $config['fetcher']))
                    ?: (isset($config['empty']) ? (string)$config['empty'] : '');
            },
            $this->getTemplatePlaceholders($template)
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

    public function getPlaceholdersSamples($template)
    {
        return array_map(
            function ($config) {
                return isset($config['sample'])
                    ? $config['sample']
                    : (isset($config['empty']) ? (string)$config['empty'] : '');
            },
            $this->getTemplatePlaceholders($template)
        );
    }

    /**
     * Fetches placeholders for given template
     *
     * @param string|array $template
     *
     * @return array subset of placeholders config
     */
    public function getTemplatePlaceholders($template)
    {
        $templatePlaceholders = array();

        $template = implode(' ', (array)$template);

        foreach ($this->placeholdersConfig as $placeholder => $definition) {
            if (false !== $pos = mb_strpos($template, $placeholder)) {
                $templatePlaceholders[$placeholder] = $definition;
            }
        }

        return $templatePlaceholders;
    }

    public function isDataSufficient($template, $data)
    {
        return false === array_search('', $this->getPlaceholdersData($template, $data));
    }

    /**
     * Renders template
     *
     * @param string|array $template
     * @param array $placeholders [placeholder => substitution string, ...]
     *
     * @return string|array
     * @throws IncompleteDataException if $placeholders has insufficient data for rendering the $template
     * @throws InvalidArgumentException if $template is not a string nor an array
     */
    protected function render($template, array $placeholders)
    {
        if (false !== array_search('', $placeholders)) {
            throw new IncompleteDataException($template, $placeholders);
        }

        if (!is_string($template) && !is_array($template)) {
            throw new InvalidArgumentException('Invalid type of $template. Expects string or array of strings');
        }

        return is_string($template)
            ? strtr($template, $placeholders)
            : array_map(function ($templ) use ($placeholders) { return strtr($templ, $placeholders); }, $template);
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
