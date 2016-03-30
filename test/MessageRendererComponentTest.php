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
use Infotech\MessageRenderer\MessageRendererComponent;

class MessageRendererComponentTest extends PHPUnit_Framework_TestCase
{

    public function testRegisterContext()
    {
        $component = new MessageRendererComponent();

        $context1 = provideContextMock('context1');
        $component->registerContext($context1);

        $context2 = provideContextMock('context2');
        $component->registerContext($context2);

        $this->assertTrue($component->hasContext($context1->getType()));
        $this->assertSame($context1, $component->getContext($context1->getType()));
        $this->assertTrue($component->hasContext($context2->getType()));
        $this->assertSame($context2, $component->getContext($context2->getType()));
    }

    /**
     * @expectedException CException
     * @expectedExceptionMessage already registered
     */
    public function testRegisterContext_ConflictType()
    {
        $context = provideContextMock('context');
        $component = new MessageRendererComponent();
        $component->registerContext($context);
        $component->registerContext($context);
    }

    public function testGetAvailableTypes()
    {
        $expectedTypes = ['context10' => 'Context 10', 'context2' => 'Context 2', 'context5' => 'Context 5'];
        $component = new MessageRendererComponent();

        foreach ($expectedTypes as $type => $name) {
            $context = provideContextMock($type, $name);
            $component->registerContext($context);
        }

        $this->assertEmpty(array_diff_assoc($component->getAvailableTypes(), $expectedTypes));
        $this->assertEmpty(array_diff_assoc($expectedTypes, $component->getAvailableTypes()));
    }

    public function testSetContexts()
    {
        $component = new MessageRendererComponent();

        $component->setContexts(array(
            provideContextMock('context1'),
            provideContextMock('context2'),
            self::createContextStubClass(),
            array('class' => self::createContextStubClass()),
        ));

        $this->assertCount(4, $component->getContexts());
    }

    /**
     * @expectedException CException
     * @expectedExceptionMessage object is not
     */
    public function testSetContexts_ObjectIsNotContext()
    {
        $component = new MessageRendererComponent();
        $component->setContexts(array(new stdClass()));
    }

    /**
     * @dataProvider provideSetContexts_MalformedConfig
     * @expectedException CException
     * @expectedExceptionMessage Malformed configuration
     */
    public function testSetContexts_MalformedConfig($config)
    {
        $component = new MessageRendererComponent();
        $component->setContexts(array($config));
    }

    public function provideSetContexts_MalformedConfig()
    {
        return array(
            array(array(self::createContextStubClass())),
            array(false),
            array(true),
        );
    }

    public function testRender()
    {
        $component = new MessageRendererComponent();

        $template = '%template%';
        $data = array();

        $context = provideContextMock('context', 'Context');

        $component->registerContext($context);
        $component->render('context', $template, $data);

        $context->shouldHaveReceived('renderTemplate', array($template, $data))->once();
    }

    /**
     * @return string
     */
    private static function createContextStubClass()
    {
        $stubClassName = 'ContextStub' . md5(microtime());
        eval(<<<PHP
            class {$stubClassName}  extends \Infotech\MessageRenderer\MessageContext
            { 
                public function getName() { return __CLASS__; } 
                public function getType() { return __CLASS__; } 
                public function placeholdersConfig() { return array(); } 
            }
PHP
        );

        return $stubClassName;
    }
}
