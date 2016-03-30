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
     * @var string
     */
    private $template;
    
    /**
     * @var string|\Closure
     */
    private $addressFetcher;

    /**
     * @var CDataProviderIterator
     */
    private $dataProviderIterator;
    /**
     * @var bool
     */
    private $skipEmptyAddress;

    /**
     * @param CDataProvider   $dataProvider
     * @param MessageContext  $context
     * @param string          $textTemplate
     * @param string|callable $addressFetcher property path or callback function($data) : string
     * @param bool            $skipEmptyAddress should iterator skip messages without address
     */
    public function __construct(
        CDataProvider $dataProvider,
        MessageContext $context,
        $textTemplate,
        $addressFetcher = null,
        $skipEmptyAddress = true
    )
    {
        $this->template = $textTemplate;
        $this->context = $context;
        $this->addressFetcher = $addressFetcher;
        $this->skipEmptyAddress = $skipEmptyAddress;
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
        return $this->addressFetcher === null
            ? $this->dataProviderIterator->key()
            : DataFetcher::fetchData($this->addressFetcher, $this->dataProviderIterator->current());
    }

    public function next()
    {
        do {
            $this->dataProviderIterator->next();
        } while ($this->skipEmptyAddress && $this->valid() && (string)$this->key() === '');
    }

    public function valid()
    {
        return $this->dataProviderIterator->valid();
    }

    public function rewind()
    {
        $this->dataProviderIterator->rewind();

        while ($this->skipEmptyAddress && $this->valid() && (string)$this->key() === '') {
            $this->dataProviderIterator->next();
        }
    }
}
