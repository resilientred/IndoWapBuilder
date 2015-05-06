<?php

/*
 * This file is part of Template.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents an import node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Template_Node_Import extends Template_Node
{
    public function __construct(Template_Node_Expression $expr, Template_Node_Expression $var, $lineno, $tag = null)
    {
        parent::__construct(array('expr' => $expr, 'var' => $var), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Template_Compiler A Template_Compiler instance
     */
    public function compile(Template_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('')
            ->subcompile($this->getNode('var'))
            ->raw(' = ')
        ;

        if ($this->getNode('expr') instanceof Template_Node_Expression_Name && '_self' === $this->getNode('expr')->getAttribute('name')) {
            $compiler->raw("\$this");
        } else {
            $compiler
                ->raw('$this->env->loadTemplate(')
                ->subcompile($this->getNode('expr'))
                ->raw(")")
            ;
        }

        $compiler->raw(";\n");
    }
}
