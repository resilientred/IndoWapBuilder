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
 * Template_NodeVisitor_Escaper implements output escaping.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Template_NodeVisitor_Escaper implements Template_NodeVisitorInterface
{
    protected $statusStack = array();
    protected $blocks = array();
    protected $safeAnalysis;
    protected $traverser;
    protected $defaultStrategy = false;
    protected $safeVars = array();

    public function __construct()
    {
        $this->safeAnalysis = new Template_NodeVisitor_SafeAnalysis();
    }

    /**
     * Called before child nodes are visited.
     *
     * @param Template_NodeInterface $node The node to visit
     * @param Template_Environment   $env  The Template environment instance
     *
     * @return Template_NodeInterface The modified node
     */
    public function enterNode(Template_NodeInterface $node, Template_Environment $env)
    {
        if ($node instanceof Template_Node_Module) {
            if ($env->hasExtension('escaper') && $defaultStrategy = $env->getExtension('escaper')->getDefaultStrategy($node->getAttribute('filename'))) {
                $this->defaultStrategy = $defaultStrategy;
            }
            $this->safeVars = array();
        } elseif ($node instanceof Template_Node_AutoEscape) {
            $this->statusStack[] = $node->getAttribute('value');
        } elseif ($node instanceof Template_Node_Block) {
            $this->statusStack[] = isset($this->blocks[$node->getAttribute('name')]) ? $this->blocks[$node->getAttribute('name')] : $this->needEscaping($env);
        } elseif ($node instanceof Template_Node_Import) {
            $this->safeVars[] = $node->getNode('var')->getAttribute('name');
        }

        return $node;
    }

    /**
     * Called after child nodes are visited.
     *
     * @param Template_NodeInterface $node The node to visit
     * @param Template_Environment   $env  The Template environment instance
     *
     * @return Template_NodeInterface The modified node
     */
    public function leaveNode(Template_NodeInterface $node, Template_Environment $env)
    {
        if ($node instanceof Template_Node_Module) {
            $this->defaultStrategy = false;
            $this->safeVars = array();
        } elseif ($node instanceof Template_Node_Expression_Filter) {
            return $this->preEscapeFilterNode($node, $env);
        } elseif ($node instanceof Template_Node_Print) {
            return $this->escapePrintNode($node, $env, $this->needEscaping($env));
        }

        if ($node instanceof Template_Node_AutoEscape || $node instanceof Template_Node_Block) {
            array_pop($this->statusStack);
        } elseif ($node instanceof Template_Node_BlockReference) {
            $this->blocks[$node->getAttribute('name')] = $this->needEscaping($env);
        }

        return $node;
    }

    protected function escapePrintNode(Template_Node_Print $node, Template_Environment $env, $type)
    {
        if (false === $type) {
            return $node;
        }

        $expression = $node->getNode('expr');

        if ($this->isSafeFor($type, $expression, $env)) {
            return $node;
        }

        $class = get_class($node);

        return new $class(
            $this->getEscaperFilter($type, $expression),
            $node->getLine()
        );
    }

    protected function preEscapeFilterNode(Template_Node_Expression_Filter $filter, Template_Environment $env)
    {
        $name = $filter->getNode('filter')->getAttribute('value');

        $type = $env->getFilter($name)->getPreEscape();
        if (null === $type) {
            return $filter;
        }

        $node = $filter->getNode('node');
        if ($this->isSafeFor($type, $node, $env)) {
            return $filter;
        }

        $filter->setNode('node', $this->getEscaperFilter($type, $node));

        return $filter;
    }

    protected function isSafeFor($type, Template_NodeInterface $expression, $env)
    {
        $safe = $this->safeAnalysis->getSafe($expression);

        if (null === $safe) {
            if (null === $this->traverser) {
                $this->traverser = new Template_NodeTraverser($env, array($this->safeAnalysis));
            }

            $this->safeAnalysis->setSafeVars($this->safeVars);

            $this->traverser->traverse($expression);
            $safe = $this->safeAnalysis->getSafe($expression);
        }

        return in_array($type, $safe) || in_array('all', $safe);
    }

    protected function needEscaping(Template_Environment $env)
    {
        if (count($this->statusStack)) {
            return $this->statusStack[count($this->statusStack) - 1];
        }

        return $this->defaultStrategy ? $this->defaultStrategy : false;
    }

    protected function getEscaperFilter($type, Template_NodeInterface $node)
    {
        $line = $node->getLine();
        $name = new Template_Node_Expression_Constant('escape', $line);
        $args = new Template_Node(array(new Template_Node_Expression_Constant((string) $type, $line), new Template_Node_Expression_Constant(null, $line), new Template_Node_Expression_Constant(true, $line)));

        return new Template_Node_Expression_Filter($node, $name, $args, $line);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }
}
