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
 * Defines a macro.
 *
 * <pre>
 * {% macro input(name, value, type, size) %}
 *    <input type="{{ type|default('text') }}" name="{{ name }}" value="{{ value|e }}" size="{{ size|default(20) }}" />
 * {% endmacro %}
 * </pre>
 */
class Template_TokenParser_Macro extends Template_TokenParser
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
        $stream = $this->parser->getStream();
        $name = $stream->expect(Template_Token::NAME_TYPE)->getValue();

        $arguments = $this->parser->getExpressionParser()->parseArguments(true, true);

        $stream->expect(Template_Token::BLOCK_END_TYPE);
        $this->parser->pushLocalScope();
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        if ($token = $stream->nextIf(Template_Token::NAME_TYPE)) {
            $value = $token->getValue();

            if ($value != $name) {
                throw new Template_Error_Syntax(sprintf("Expected endmacro for macro '$name' (but %s given)", $value), $stream->getCurrent()->getLine(), $stream->getFilename());
            }
        }
        $this->parser->popLocalScope();
        $stream->expect(Template_Token::BLOCK_END_TYPE);

        $this->parser->setMacro($name, new Template_Node_Macro($name, new Template_Node_Body(array($body)), $arguments, $lineno, $this->getTag()));
    }

    public function decideBlockEnd(Template_Token $token)
    {
        return $token->test('endmacro');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'macro';
    }
}
