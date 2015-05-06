<?php

/*
 * This file is part of Template.
 *
 * (c) 2011 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Returns the value or the default value when it is undefined or empty.
 *
 * <pre>
 *  {{ var.foo|default('foo item on var is not defined') }}
 * </pre>
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Template_Node_Expression_Filter_Default extends Template_Node_Expression_Filter
{
    public function __construct(Template_NodeInterface $node, Template_Node_Expression_Constant $filterName, Template_NodeInterface $arguments, $lineno, $tag = null)
    {
        $default = new Template_Node_Expression_Filter($node, new Template_Node_Expression_Constant('default', $node->getLine()), $arguments, $node->getLine());

        if ('default' === $filterName->getAttribute('value') && ($node instanceof Template_Node_Expression_Name || $node instanceof Template_Node_Expression_GetAttr)) {
            $test = new Template_Node_Expression_Test_Defined(clone $node, 'defined', new Template_Node(), $node->getLine());
            $false = count($arguments) ? $arguments->getNode(0) : new Template_Node_Expression_Constant('', $node->getLine());

            $node = new Template_Node_Expression_Conditional($test, $default, $false, $node->getLine());
        } else {
            $node = $default;
        }

        parent::__construct($node, $filterName, $arguments, $lineno, $tag);
    }

    public function compile(Template_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('node'));
    }
}
