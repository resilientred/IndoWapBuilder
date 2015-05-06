<?php

/*
 * This file is part of Template.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Template_Node_Expression_Binary_NotEqual extends Template_Node_Expression_Binary
{
    public function operator(Template_Compiler $compiler)
    {
        return $compiler->raw('!=');
    }
}
