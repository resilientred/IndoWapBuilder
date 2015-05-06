<?php

/*
 * This file is part of Template.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Template_Extension_Sandbox extends Template_Extension
{
    protected $sandboxedGlobally;
    protected $sandboxed;
    protected $policy;

    public function __construct(Template_Sandbox_SecurityPolicyInterface $policy, $sandboxed = false)
    {
        $this->policy            = $policy;
        $this->sandboxedGlobally = $sandboxed;
    }

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Template_TokenParserInterface or Template_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array(new Template_TokenParser_Sandbox());
    }

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return Template_NodeVisitorInterface[] An array of Template_NodeVisitorInterface instances
     */
    public function getNodeVisitors()
    {
        return array(new Template_NodeVisitor_Sandbox());
    }

    public function enableSandbox()
    {
        $this->sandboxed = true;
    }

    public function disableSandbox()
    {
        $this->sandboxed = false;
    }

    public function isSandboxed()
    {
        return $this->sandboxedGlobally || $this->sandboxed;
    }

    public function isSandboxedGlobally()
    {
        return $this->sandboxedGlobally;
    }

    public function setSecurityPolicy(Template_Sandbox_SecurityPolicyInterface $policy)
    {
        $this->policy = $policy;
    }

    public function getSecurityPolicy()
    {
        return $this->policy;
    }

    public function checkSecurity($tags, $filters, $functions)
    {
        if ($this->isSandboxed()) {
            $this->policy->checkSecurity($tags, $filters, $functions);
        }
    }

    public function checkMethodAllowed($obj, $method)
    {
        if ($this->isSandboxed()) {
            $this->policy->checkMethodAllowed($obj, $method);
        }
    }

    public function checkPropertyAllowed($obj, $method)
    {
        if ($this->isSandboxed()) {
            $this->policy->checkPropertyAllowed($obj, $method);
        }
    }

    public function ensureToStringAllowed($obj)
    {
        if (is_object($obj)) {
            $this->policy->checkMethodAllowed($obj, '__toString');
        }

        return $obj;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sandbox';
    }
}
