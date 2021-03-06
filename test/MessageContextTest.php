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

        $substitutions = $context->getPlaceholdersData(
            '_PLH_1_1_ _PLH_3_ _PLH_2_1_ _PLH_2_',
            array('object' => (object)array('property' => 'value 1'))
        );

        $this->assertArrayHasKey('_PLH_1_1_', $substitutions);
        $this->assertArrayHasKey('_PLH_2_1_', $substitutions);
        $this->assertArrayHasKey('_PLH_3_', $substitutions);
        $this->assertArrayHasKey('_PLH_2_', $substitutions);
        $this->assertArrayNotHasKey('_PLH_1_', $substitutions);
        $this->assertEquals('yes', $substitutions['_PLH_1_1_']);
        $this->assertEquals('value 1', $substitutions['_PLH_3_']);
        $this->assertEquals('(none)', $substitutions['_PLH_2_']);
        $this->assertEquals('no', $substitutions['_PLH_2_1_']);
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
        $this->assertEquals('Place 1', $substitutions['_PLH_1_']['sample']);
        $this->assertEquals('Placeholder 2', $substitutions['_PLH_2_']['title']);
        $this->assertEquals('Description 2', $substitutions['_PLH_2_']['description']);
        $this->assertArrayNotHasKey('sample', $substitutions['_PLH_2_']);
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

    public function testRenderArrayTemplate()
    {
        /** @var \Mockery\MockInterface|MessageContext $context */
        $context = provideContextMock('context', 'context', $this->providePlaceholdersConfig());

        $message = $context->renderTemplate(
            array('key1' => '{_PLH_1_} =_PLH_2_= __PLH_3__', 'key2' => '_PLH_3_'),
            array('object' => (object)array('property' => 'value 1'))
        );

        $this->assertEquals(array('key1' => '{value 1} =(none)= _value 1_', 'key2' => 'value 1'), $message);
    }

    /**
     * @expectedException \Infotech\MessageRenderer\IncompleteDataException
     */
    public function testRenderTemplate_WithInsufficientData()
    {
        /** @var \Mockery\MockInterface|MessageContext $context */
        $context = provideContextMock('context', 'context', $this->providePlaceholdersConfig());

        $context->renderTemplate(
            '{_PLH_1_} =_PLH_2_= __PLH_4__',
            array('object' => (object)array('property' => 'value 1'))
        );
    }

    public function testRenderTemplate_SilentlyWithInsufficientData()
    {
        /** @var \Mockery\MockInterface|MessageContext $context */
        $context = provideContextMock('context', 'context', $this->providePlaceholdersConfig());

        $message = $context->renderTemplate(
            '{_PLH_1_} =_PLH_2_= __PLH_4__',
            array('object' => (object)array('property' => 'value 1')),
            true
        );

        $this->assertEquals('{value 1} =(none)= __PLH_4__', $message);
    }

    /**
     * @expectedException \Infotech\MessageRenderer\IncompleteDataException
     */
    public function testRenderArrayTemplate_WithInsufficientData()
    {
        /** @var \Mockery\MockInterface|MessageContext $context */
        $context = provideContextMock('context', 'context', $this->providePlaceholdersConfig());

        $context->renderTemplate(
            array('key1' => '{_PLH_1_} =_PLH_2_= __PLH_3__', 'key2' => '__PLH_4__'),
            array('object' => (object)array('property' => 'value 1'))
        );
    }

    public function testRenderPlaceholderPartiallyMatchesAnotherOne()
    {
        /** @var \Mockery\MockInterface|MessageContext $context */
        $context = provideContextMock('context', 'context', $this->providePlaceholdersConfig());

        $message = $context->renderTemplate(
            '{_PLH_1_} =_PLH_2_= _PLH_1_1_ !',
            array('object' => (object)array('property' => 'value 1'))
        );

        $this->assertEquals('{value 1} =(none)= yes !', $message);
    }

    public function testRenderSample()
    {
        /** @var \Mockery\MockInterface|MessageContext $context */
        $context = provideContextMock('context', 'context', $this->providePlaceholdersConfig());

        $message = $context->renderSample('{_PLH_1_} =_PLH_2_= __PLH_3__');

        $this->assertEquals('{Place 1} =(none)= _Place 3_', $message);
    }

    public function testRenderArraySample()
    {
        /** @var \Mockery\MockInterface|MessageContext $context */
        $context = provideContextMock('context', 'context', $this->providePlaceholdersConfig());

        $message = $context->renderSample(array('key1' => '{_PLH_1_} =_PLH_2_= __PLH_3__', 'key2' => '_PLH_3_'));

        $this->assertEquals(array('key1' => '{Place 1} =(none)= _Place 3_', 'key2' => 'Place 3'), $message);
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
                'fetcher' => 'object.property',
                'sample' => 'Place 1',
            ),
            '_PLH_2_1_' => array(
                'title' => 'Placeholder 2-1',
                'description' => 'Description 2-1',
                'fetcher' => function () { return 'no'; },
                'sample' => 'Place 2-1'
            ),
            '_PLH_2_' => array(
                'title' => 'Placeholder 2',
                'description' => 'Description 2',
                'fetcher' => 'object.not.existent.property',
                'empty' => '(none)',
            ),
            '_PLH_3_' => array(
                'title' => 'Placeholder 3',
                'description' => 'Description 3',
                'fetcher' => function (array $data) { return $data['object']->property; },
                'sample' => 'Place 3',
                'empty' => '(none)',
            ),
            '_PLH_4_' => array(
                'title' => 'Placeholder 4',
                'description' => 'Description 4',
                'fetcher' => function () { return ''; },
                'sample' => 'Place 4'
            ),
            '_PLH_1_1_' => array(
                'title' => 'Placeholder 1-1',
                'description' => 'Description 1-1',
                'fetcher' => function () { return 'yes'; },
                'sample' => 'Place 1-1'
            ),
        );
    }
}
