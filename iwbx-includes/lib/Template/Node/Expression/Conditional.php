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
class Template_Node_Expression_Conditional extends Template_Node_Expression
{
    public function __construct(Template_Node_Expression $expr1, Template_Node_Expression $expr2, Template_Node_Expression $expr3, $lineno)
    {
        parent::__construct(array('expr1' => $expr1, 'expr2' => $expr2, 'expr3' => $expr3), array(), $lineno);
    }

    public function compile(Template_Compiler $compiler)
    {
        $compiler
            ->raw('((')
            ->subcompile($this->getNode('expr1'))
            ->raw(') ? (')
            ->subcompile($this->getNode('expr2'))
            ->raw(') : (')
            ->subcompile($this->getNode('expr3'))
            ->raw('))')
        ;
    }
}
