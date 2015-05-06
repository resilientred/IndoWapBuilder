<?php

/*
 * This file is part of Template.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a set node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Template_Node_Set extends Template_Node
{
    public function __construct($capture, Template_NodeInterface $names, Template_NodeInterface $values, $lineno, $tag = null)
    {
        parent::__construct(array('names' => $names, 'values' => $values), array('capture' => $capture, 'safe' => false), $lineno, $tag);

        /*
         * Optimizes the node when capture is used for a large block of text.
         *
         * {% set foo %}foo{% endset %} is compiled to $context['foo'] = new Template_Markup("foo");
         */
        if ($this->getAttribute('capture')) {
            $this->setAttribute('safe', true);

            $values = $this->getNode('values');
            if ($values instanceof Template_Node_Text) {
                $this->setNode('values', new Template_Node_Expression_Constant($values->getAttribute('data'), $values->getLine()));
                $this->setAttribute('capture', false);
            }
        }
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Template_Compiler A Template_Compiler instance
     */
    public function compile(Template_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        if (count($this->getNode('names')) > 1) {
            $compiler->write('list(');
            foreach ($this->getNode('names') as $idx => $node) {
                if ($idx) {
                    $compiler->raw(', ');
                }

                $compiler->subcompile($node);
            }
            $compiler->raw(')');
        } else {
            if ($this->getAttribute('capture')) {
                $compiler
                    ->write("ob_start();\n")
                    ->subcompile($this->getNode('values'))
                ;
            }

            $compiler->subcompile($this->getNode('names'), false);

            if ($this->getAttribute('capture')) {
                $compiler->raw(" = ('' === \$tmp = ob_get_clean()) ? '' : new Template_Markup(\$tmp, \$this->env->getCharset())");
            }
        }

        if (!$this->getAttribute('capture')) {
            $compiler->raw(' = ');

            if (count($this->getNode('names')) > 1) {
                $compiler->write('array(');
                foreach ($this->getNode('values') as $idx => $value) {
                    if ($idx) {
                        $compiler->raw(', ');
                    }

                    $compiler->subcompile($value);
                }
                $compiler->raw(')');
            } else {
                if ($this->getAttribute('safe')) {
                    $compiler
                        ->raw("('' === \$tmp = ")
                        ->subcompile($this->getNode('values'))
                        ->raw(") ? '' : new Template_Markup(\$tmp, \$this->env->getCharset())")
                    ;
                } else {
                    $compiler->subcompile($this->getNode('values'));
                }
            }
        }

        $compiler->raw(";\n");
    }
}
