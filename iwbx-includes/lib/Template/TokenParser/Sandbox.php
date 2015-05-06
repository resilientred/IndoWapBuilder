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
 * Marks a section of a template as untrusted code that must be evaluated in the sandbox mode.
 *
 * <pre>
 * {% sandbox %}
 *     {% include 'user.html' %}
 * {% endsandbox %}
 * </pre>
 *
 * @see http://www.template-project.org/doc/api.html#sandbox-extension for details
 */
class Template_TokenParser_Sandbox extends Template_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Template_Token $token A Template_Token instance
     *
     * @return Template_NodeInterface A Template_NodeInterface instance
     */
    public function parse(Template_Token $token)
    {
        $this->parser->getStream()->expect(Template_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $this->parser->getStream()->expect(Template_Token::BLOCK_END_TYPE);

        // in a sandbox tag, only include tags are allowed
        if (!$body instanceof Template_Node_Include) {
            foreach ($body as $node) {
                if ($node instanceof Template_Node_Text && ctype_space($node->getAttribute('data'))) {
                    continue;
                }

                if (!$node instanceof Template_Node_Include) {
                    throw new Template_Error_Syntax('Only "include" tags are allowed within a "sandbox" section', $node->getLine(), $this->parser->getFilename());
                }
            }
        }

        return new Template_Node_Sandbox($body, $token->getLine(), $this->getTag());
    }

    public function decideBlockEnd(Template_Token $token)
    {
        return $token->test('endsandbox');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'sandbox';
    }
}
