<?php

/*
 * This file is part of Template.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Template_Node_Expression_Array extends Template_Node_Expression
{
    protected $index;

    public function __construct(array $elements, $lineno)
    {
        parent::__construct($elements, array(), $lineno);

        $this->index = -1;
        foreach ($this->getKeyValuePairs() as $pair) {
            if ($pair['key'] instanceof Template_Node_Expression_Constant && ctype_digit((string) $pair['key']->getAttribute('value')) && $pair['key']->getAttribute('value') > $this->index) {
                $this->index = $pair['key']->getAttribute('value');
            }
        }
    }

    public function getKeyValuePairs()
    {
        $pairs = array();

        foreach (array_chunk($this->nodes, 2) as $pair) {
            $pairs[] = array(
                'key' => $pair[0],
                'value' => $pair[1],
            );
        }

        return $pairs;
    }

    public function hasElement(Template_Node_Expression $key)
    {
        foreach ($this->getKeyValuePairs() as $pair) {
            // we compare the string representation of the keys
            // to avoid comparing the line numbers which are not relevant here.
            if ((string) $key == (string) $pair['key']) {
                return true;
            }
        }

        return false;
    }

    public function addElement(Template_Node_Expression $value, Template_Node_Expression $key = null)
    {
        if (null === $key) {
            $key = new Template_Node_Expression_Constant(++$this->index, $value->getLine());
        }

        array_push($this->nodes, $key, $value);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Template_Compiler A Template_Compiler instance
     */
    public function compile(Template_Compiler $compiler)
    {
        $compiler->raw('array(');
        $first = true;
        foreach ($this->getKeyValuePairs() as $pair) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $first = false;

            $compiler
                ->subcompile($pair['key'])
                ->raw(' => ')
                ->subcompile($pair['value'])
            ;
        }
        $compiler->raw(')');
    }
}
