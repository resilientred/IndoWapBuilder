<?php

/*
 * This file is part of Template.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Template_Node_Expression_Function extends Template_Node_Expression_Call
{
    public function __construct($name, Template_NodeInterface $arguments, $lineno)
    {
        parent::__construct(array('arguments' => $arguments), array('name' => $name), $lineno);
    }

    public function compile(Template_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $function = $compiler->getEnvironment()->getFunction($name);

        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'function');
        $this->setAttribute('thing', $function);
        $this->setAttribute('needs_environment', $function->needsEnvironment());
        $this->setAttribute('needs_context', $function->needsContext());
        $this->setAttribute('arguments', $function->getArguments());
        if ($function instanceof Template_FunctionCallableInterface || $function instanceof Template_SimpleFunction) {
            $this->setAttribute('callable', $function->getCallable());
        }

        $this->compileCallable($compiler);
    }
}
