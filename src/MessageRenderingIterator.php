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

use IteratorIterator;
use Traversable;

/**
 * Data stream render iterator
 */
class MessageRenderingIterator extends IteratorIterator
{
    const DEFAULT_PAGE_SIZE = 100;

    /**
     * @var MessageContext
     */
    private $context;

    /**
     * @var string|array
     */
    private $template;

    /**
     * @param Traversable    $iterator
     * @param MessageContext $context
     * @param string|array   $template
     */
    public function __construct(Traversable $iterator, MessageContext $context, $template)
    {
        parent::__construct($iterator);
        $this->template = $template;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     *
     * @return string|mixed Default {@see MessageContext::renderTemplate()} implementation returns string, but
     *                      custom implementation may return structure with additional data
     */
    public function current()
    {
        return $this->context->renderTemplate($this->template, parent::current());
    }

    public function valid()
    {
        while (parent::valid() && !$this->canBeRendered()) {
            parent::next();
        }

        return parent::valid();
    }

    /**
     * @return bool
     */
    private function canBeRendered()
    {
        return $this->context->isDataSufficient($this->template, parent::current());
    }
}
