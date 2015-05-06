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
abstract class Template_Node_Expression_Binary extends Template_Node_Expression
{
    public function __construct(Template_NodeInterface $left, Template_NodeInterface $right, $lineno)
    {
        parent::__construct(array('left' => $left, 'right' => $right), array(), $lineno);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Template_Compiler A Template_Compiler instance
     */
    public function compile(Template_Compiler $compiler)
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('left'))
            ->raw(' ')
        ;
        $this->operator($compiler);
        $compiler
            ->raw(' ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }

    abstract public function operator(Template_Compiler $compiler);
}
