<?php

/*
 * This file is part of Template.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class Template_Test_NodeTestCase extends PHPUnit_Framework_TestCase
{
    abstract public function getTests();

    /**
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        $this->assertNodeCompilation($source, $node, $environment);
    }

    public function assertNodeCompilation($source, Template_Node $node, Template_Environment $environment = null)
    {
        $compiler = $this->getCompiler($environment);
        $compiler->compile($node);

        $this->assertEquals($source, trim($compiler->getSource()));
    }

    protected function getCompiler(Template_Environment $environment = null)
    {
        return new Template_Compiler(null === $environment ? $this->getEnvironment() : $environment);
    }

    protected function getEnvironment()
    {
        return new Template_Environment();
    }

    protected function getVariableGetter($name)
    {
        if (version_compare(phpversion(), '5.4.0RC1', '>=')) {
            return sprintf('(isset($context["%s"]) ? $context["%s"] : null)', $name, $name);
        }

        return sprintf('$this->getContext($context, "%s")', $name);
    }

    protected function getAttributeGetter()
    {
        if (function_exists('template_template_get_attributes')) {
            return 'template_template_get_attributes($this, ';
        }

        return '$this->getAttribute(';
    }
}
