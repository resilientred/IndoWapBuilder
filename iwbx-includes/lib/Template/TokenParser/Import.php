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
 * Imports macros.
 *
 * <pre>
 *   {% import 'forms.html' as forms %}
 * </pre>
 */
class Template_TokenParser_Import extends Template_TokenParser
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
        $macro = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect('as');
        $var = new Template_Node_Expression_AssignName($this->parser->getStream()->expect(Template_Token::NAME_TYPE)->getValue(), $token->getLine());
        $this->parser->getStream()->expect(Template_Token::BLOCK_END_TYPE);

        $this->parser->addImportedSymbol('template', $var->getAttribute('name'));

        return new Template_Node_Import($macro, $var, $token->getLine(), $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'import';
    }
}
