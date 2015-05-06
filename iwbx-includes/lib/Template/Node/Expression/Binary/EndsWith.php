<?php

/*
 * This file is part of Template.
 *
 * (c) 2013 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Template_Node_Expression_Binary_EndsWith extends Template_Node_Expression_Binary
{
    public function compile(Template_Compiler $compiler)
    {
        $compiler
            ->raw('(0 === substr_compare(')
            ->subcompile($this->getNode('left'))
            ->raw(', ')
            ->subcompile($this->getNode('right'))
            ->raw(', -strlen(')
            ->subcompile($this->getNode('right'))
            ->raw(')))')
        ;
    }

    public function operator(Template_Compiler $compiler)
    {
        return $compiler->raw('');
    }
}
