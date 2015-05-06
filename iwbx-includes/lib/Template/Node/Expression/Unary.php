<?php

/*
 * This file is part of Template.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class Template_Node_Expression_Unary extends Template_Node_Expression
{
    public function __construct(Template_NodeInterface $node, $lineno)
    {
        parent::__construct(array('node' => $node), array(), $lineno);
    }

    public function compile(Template_Compiler $compiler)
    {
        $compiler->raw('(');
        $this->operator($compiler);
        $compiler
            ->subcompile($this->getNode('node'))
            ->raw(')')
        ;
    }

    abstract public function operator(Template_Compiler $compiler);
}
