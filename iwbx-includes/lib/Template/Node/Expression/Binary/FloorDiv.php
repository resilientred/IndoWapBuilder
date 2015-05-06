<?php

/*
 * This file is part of Template.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Template_Node_Expression_Binary_FloorDiv extends Template_Node_Expression_Binary
{
    /**
     * Compiles the node to PHP.
     *
     * @param Template_Compiler A Template_Compiler instance
     */
    public function compile(Template_Compiler $compiler)
    {
        $compiler->raw('intval(floor(');
        parent::compile($compiler);
        $compiler->raw('))');
    }

    public function operator(Template_Compiler $compiler)
    {
        return $compiler->raw('/');
    }
}
