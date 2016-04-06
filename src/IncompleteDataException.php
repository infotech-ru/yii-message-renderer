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

use RuntimeException;

class IncompleteDataException extends RuntimeException
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $placeholders;

    public function __construct($template, array $placeholders)
    {
        parent::__construct('Trying to render template with incomplete data');

        $this->template = $template;
        $this->placeholders = $placeholders;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

}
