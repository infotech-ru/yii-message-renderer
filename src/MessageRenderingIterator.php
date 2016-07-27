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

use CDataProviderIterator;
use CDataProvider;
use Iterator;

/**
 * Data stream render iterator
 */
class MessageRenderingIterator implements Iterator
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
     * @var CDataProviderIterator
     */
    private $dataProviderIterator;

    /**
     * @param CDataProvider  $dataProvider
     * @param MessageContext $context
     * @param string|array   $template
     */
    public function __construct(CDataProvider $dataProvider, MessageContext $context, $template)
    {
        $this->template = $template;
        $this->context = $context;
        $this->dataProviderIterator = new CDataProviderIterator($dataProvider, self::DEFAULT_PAGE_SIZE);
    }

    /**
     * {@inheritdoc}
     *
     * @return string|mixed Default {@see MessageContext::renderTemplate()} implementation returns string, but
     *                      custom implementation may return structure with additional data
     */
    public function current()
    {
        return $this->context->renderTemplate($this->template, $this->dataProviderIterator->current());
    }

    public function key()
    {
        return $this->dataProviderIterator->key();
    }

    public function next()
    {
        $this->dataProviderIterator->next();
    }

    public function valid()
    {
        while ($this->dataProviderIterator->valid() && !$this->canBeRendered()) {
            $this->dataProviderIterator->next();
        }

        return $this->dataProviderIterator->valid();
    }

    public function rewind()
    {
        $this->dataProviderIterator->rewind();
    }

    /**
     * @return bool
     */
    private function canBeRendered()
    {
        return $this->context->isDataSufficient($this->template, $this->dataProviderIterator->current());
    }
}
