<?php
/*
 * This file is part of the infotech/yii-message-renderer package.
 *
 * (c) Infotech, Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Infotech\MessageRenderer\MessageRenderingIterator;

class MessageRenderingIteratorTest extends PHPUnit_Framework_TestCase
{
    public function testIterate()
    {
        $context = provideContextMock('context');

        $template = [
            'key1' => '%template%',
            'key2' => '%template%',
        ];
        $data = array(
            array('key1' => 'value1'),
            array('key1' => 'value2'),
            array('key1' => 'value3'),
        );

        iterator_to_array(new MessageRenderingIterator(new CArrayDataProvider($data), $context, $template));

        $context->shouldHaveReceived('renderTemplate', array($template, $data[0]));
        $context->shouldHaveReceived('renderTemplate', array($template, $data[1]));
        $context->shouldHaveReceived('renderTemplate', array($template, $data[2]));
    }
}
