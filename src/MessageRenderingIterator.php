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

/**
 * Data stream render iterator
 */
class MessageRenderingIterator extends CDataProviderIterator
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
     * @param CDataProvider  $dataProvider
     * @param MessageContext $context
     * @param string         $textTemplate
     */
    public function __construct(CDataProvider $dataProvider, MessageContext $context, $textTemplate)
    {
        $this->template = $textTemplate;
        $this->context = $context;
        parent::__construct($dataProvider, self::DEFAULT_PAGE_SIZE);
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
}
