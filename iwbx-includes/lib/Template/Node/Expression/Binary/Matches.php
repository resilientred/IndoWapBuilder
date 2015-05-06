<?php

/*
 * This file is part of Template.
 *
 * (c) 2013 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Template_Node_Expression_Binary_Matches extends Template_Node_Expression_Binary
{
    public function compile(Template_Compiler $compiler)
    {
        $compiler
            ->raw('preg_match(')
            ->subcompile($this->getNode('right'))
            ->raw(', ')
            ->subcompile($this->getNode('left'))
            ->raw(')')
        ;
    }

    public function operator(Template_Compiler $compiler)
    {
        return $compiler->raw('');
    }
}
