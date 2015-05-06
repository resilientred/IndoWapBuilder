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
 * Template_NodeVisitor_Sandbox implements sandboxing.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Template_NodeVisitor_Sandbox implements Template_NodeVisitorInterface
{
    protected $inAModule = false;
    protected $tags;
    protected $filters;
    protected $functions;

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
            $this->inAModule = true;
            $this->tags = array();
            $this->filters = array();
            $this->functions = array();

            return $node;
        } elseif ($this->inAModule) {
            // look for tags
            if ($node->getNodeTag()) {
                $this->tags[] = $node->getNodeTag();
            }

            // look for filters
            if ($node instanceof Template_Node_Expression_Filter) {
                $this->filters[] = $node->getNode('filter')->getAttribute('value');
            }

            // look for functions
            if ($node instanceof Template_Node_Expression_Function) {
                $this->functions[] = $node->getAttribute('name');
            }

            // wrap print to check __toString() calls
            if ($node instanceof Template_Node_Print) {
                return new Template_Node_SandboxedPrint($node->getNode('expr'), $node->getLine(), $node->getNodeTag());
            }
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
            $this->inAModule = false;

            return new Template_Node_SandboxedModule($node, array_unique($this->filters), array_unique($this->tags), array_unique($this->functions));
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }
}
