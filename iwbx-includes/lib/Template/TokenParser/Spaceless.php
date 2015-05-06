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
 * Remove whitespaces between HTML tags.
 *
 * <pre>
 * {% spaceless %}
 *      <div>
 *          <strong>foo</strong>
 *      </div>
 * {% endspaceless %}
 *
 * {# output will be <div><strong>foo</strong></div> #}
 * </pre>
 */
class Template_TokenParser_Spaceless extends Template_TokenParser
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
        $lineno = $token->getLine();

        $this->parser->getStream()->expect(Template_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideSpacelessEnd'), true);
        $this->parser->getStream()->expect(Template_Token::BLOCK_END_TYPE);

        return new Template_Node_Spaceless($body, $lineno, $this->getTag());
    }

    public function decideSpacelessEnd(Template_Token $token)
    {
        return $token->test('endspaceless');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'spaceless';
    }
}
