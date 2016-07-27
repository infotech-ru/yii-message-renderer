<?php
/*
 * This file is part of the infotech/yii-message-renderer package.
 *
 * (c) Infotech, Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Infotech\MessageRenderer\MessageContext;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii/framework/web/helpers/CHtml.php';

/**
 * @param string  $type
 * @param null    $name
 * @param array   $placeholders
 *
 * @return MessageContext|\Mockery\MockInterface
 */
function provideContextMock($type, $name = null, $placeholders = array())
{
    $expectations = array(
        'getType' => $type,
        'getName' => $name ?: $type
    );

    return Mockery::mock('Infotech\MessageRenderer\MessageContext', $expectations)->shouldDeferMissing()
        ->setPlaceholdersConfig($placeholders);
}
