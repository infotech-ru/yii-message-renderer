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

class MessageContextTest extends PHPUnit_Framework_TestCase
{
    public function testGetPlaceholdersData()
    {
        /** @var \Mockery\MockInterface|MessageContext $context */
        $context = provideContextMock('context', 'context', $this->providePlaceholdersConfig());

        $substitutions = $context->getPlaceholdersData(array('object' => (object)array('property' => 'value 1')));

        $this->assertArrayHasKey('_PLH_1_', $substitutions);
        $this->assertArrayHasKey('_PLH_2_', $substitutions);
        $this->assertEquals('value 1', $substitutions['_PLH_1_']);
        $this->assertEquals('(none)', $substitutions['_PLH_2_']);
    }

    public function testGetPlaceholdersInfo()
    {
        /** @var \Mockery\MockInterface|MessageContext $context */
        $context = provideContextMock('context', 'context', $this->providePlaceholdersConfig());

        $substitutions = $context->getPlaceholdersInfo();

        $this->assertArrayHasKey('_PLH_1_', $substitutions);
        $this->assertArrayHasKey('_PLH_2_', $substitutions);
        $this->assertEquals('Placeholder 1', $substitutions['_PLH_1_']['title']);
        $this->assertEquals('Description 1', $substitutions['_PLH_1_']['description']);
        $this->assertEquals('Placeholder 2', $substitutions['_PLH_2_']['title']);
        $this->assertEquals('Description 2', $substitutions['_PLH_2_']['description']);
    }

    public function testRenderTemplate()
    {
        /** @var \Mockery\MockInterface|MessageContext $context */
        $context = provideContextMock('context', 'context', $this->providePlaceholdersConfig());

        $message = $context->renderTemplate(
            '{_PLH_1_} =_PLH_2_= __PLH_3__',
            array('object' => (object)array('property' => 'value 1'))
        );

        $this->assertEquals('{value 1} =(none)= _value 1_', $message);
    }

    /**
     * @return array
     */
    private function providePlaceholdersConfig()
    {
        return array(
            '_PLH_1_' => array(
                'title' => 'Placeholder 1',
                'description' => 'Description 1',
                'fetcher' => '[object].property',
                'empty' => '(none)',
            ),
            '_PLH_2_' => array(
                'title' => 'Placeholder 2',
                'description' => 'Description 2',
                'fetcher' => '[object].not.existent.property',
                'empty' => '(none)',
            ),
            '_PLH_3_' => array(
                'title' => 'Placeholder 3',
                'description' => 'Description 3',
                'fetcher' => function (array $data) { return $data['object']->property; },
                'empty' => '(none)',
            ),
        );
    }
}
