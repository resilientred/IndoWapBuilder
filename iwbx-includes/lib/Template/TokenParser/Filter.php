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
 * Filters a section of a template by applying filters.
 *
 * <pre>
 * {% filter upper %}
 *  This text becomes uppercase
 * {% endfilter %}
 * </pre>
 */
class Template_TokenParser_Filter extends Template_TokenParser
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
        $name = $this->parser->getVarName();
        $ref = new Template_Node_Expression_BlockReference(new Template_Node_Expression_Constant($name, $token->getLine()), true, $token->getLine(), $this->getTag());

        $filter = $this->parser->getExpressionParser()->parseFilterExpressionRaw($ref, $this->getTag());
        $this->parser->getStream()->expect(Template_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $this->parser->getStream()->expect(Template_Token::BLOCK_END_TYPE);

        $block = new Template_Node_Block($name, $body, $token->getLine());
        $this->parser->setBlock($name, $block);

        return new Template_Node_Print($filter, $token->getLine(), $this->getTag());
    }

    public function decideBlockEnd(Template_Token $token)
    {
        return $token->test('endfilter');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'filter';
    }
}
