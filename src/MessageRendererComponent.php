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

use CApplicationComponent;
use CException;
use Exception;
use Traversable;
use Yii;

Yii::import('CApplicationComponent');

class MessageRendererComponent extends CApplicationComponent
{
    /**
     * @var MessageContext[]
     */
    private $contexts = [];

    /**
     * Renders one message by template, context and given data
     *
     * @param string $contextType  Type of previously registered context
     * @param string $textTemplate Template text
     * @param object|array $data   Data for placeholder substitutions
     * @param boolean      $ignoreInsufficientData true - placeholders with no data will be untouched,
     *                                             false - {@see IncompleteDataException} will be thrown if
     *                                             $data is insufficient.
     *
     * @return string|mixed Default {@see MessageContext::renderTemplate()} implementation returns string, but
     *                      custom implementation may returns structure with additional data
     *
     * @throws CException if context with $contextType does not registered in the component
     */
    public function render($contextType, $textTemplate, $data, $ignoreInsufficientData = false)
    {
        return $this->getContext($contextType)->renderTemplate($textTemplate, $data, $ignoreInsufficientData);
    }

    /**
     * Renders sample message by template and context
     *
     * @param string $contextType  Type of previously registered context
     * @param string $textTemplate Template text
     *
     * @return string
     *
     * @throws CException if context with $contextType does not registered in the component
     */
    public function renderSample($contextType, $textTemplate)
    {
        return $this->getContext($contextType)->renderSample($textTemplate);
    }

    /**
     * Iterating over data set and rendering messages
     *
     * @param string      $contextType  Type of previously registered context
     * @param string      $textTemplate Template text
     * @param Traversable $dataIterator Provides data of type conformed with $contextType
     *
     * @return MessageRenderingIterator
     */
    public function renderBatch($contextType, $textTemplate, Traversable $dataIterator)
    {
        return new MessageRenderingIterator($dataIterator, $this->getContext($contextType), $textTemplate);
    }

    /**
     * @param MessageContext[]|array[] $contexts list of instances or component's config
     *
     * @see Yii::createComponent()
     *
     * @throws CException if $contexts item is neither MessageContext instance nor valid
     *                    component's config
     */
    public function setContexts(array $contexts)
    {
        $this->contexts = [];

        foreach ($contexts as $context) {
            if (!is_object($context)) {
                try {
                    $context = Yii::createComponent($context);
                } catch (CException $e) {
                    throw new CException('Malformed configuration of Message Rendering Context Component', 0, $e);
                }
            }
            if (!$context instanceof MessageContext) {
                throw new CException('Created object is not an Message Rendering Context Component');
            }

            $this->registerContext($context);
        }
    }

    /**
     * @return MessageContext[]
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        static $types = null;

        if ($types === null) {
            $types = array_map(
                function(MessageContext $context) {
                    return $context->getName();
                },
                $this->getContexts()
            );
        }

        return $types;
    }

    /**
     * @param MessageContext $context
     *
     * @throws CException if context with same type has already registered
     */
    public function registerContext(MessageContext $context)
    {
        $contextType = $context->getType();
        if ($this->hasContext($contextType)) {
            throw new CException(
                'Message Rendering Context with type "' . $contextType . '" has already registered'
            );
        }

        $this->contexts[$contextType] = $context;
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public function hasContext($type)
    {
        return isset($this->contexts[$type]);
    }

    /**
     * @param string $type
     *
     * @return MessageContext
     * @throws Exception if context does not registered in the component
     */
    public function getContext($type)
    {
        if (!$this->hasContext($type)) {
            throw new CException('Message Rendering Context with type "' . $type . '" is not registered');
        }

        return $this->contexts[$type];
    }
}
