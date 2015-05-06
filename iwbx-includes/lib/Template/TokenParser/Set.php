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
 * Defines a variable.
 *
 * <pre>
 *  {% set foo = 'foo' %}
 *
 *  {% set foo = [1, 2] %}
 *
 *  {% set foo = {'foo': 'bar'} %}
 *
 *  {% set foo = 'foo' ~ 'bar' %}
 *
 *  {% set foo, bar = 'foo', 'bar' %}
 *
 *  {% set foo %}Some content{% endset %}
 * </pre>
 */
class Template_TokenParser_Set extends Template_TokenParser
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
        $names = $this->parser->getExpressionParser()->parseAssignmentExpression();

        $capture = false;
        if ($stream->nextIf(Template_Token::OPERATOR_TYPE, '=')) {
            $values = $this->parser->getExpressionParser()->parseMultitargetExpression();

            $stream->expect(Template_Token::BLOCK_END_TYPE);

            if (count($names) !== count($values)) {
                throw new Template_Error_Syntax("When using set, you must have the same number of variables and assignments.", $stream->getCurrent()->getLine(), $stream->getFilename());
            }
        } else {
            $capture = true;

            if (count($names) > 1) {
                throw new Template_Error_Syntax("When using set with a block, you cannot have a multi-target.", $stream->getCurrent()->getLine(), $stream->getFilename());
            }

            $stream->expect(Template_Token::BLOCK_END_TYPE);

            $values = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            $stream->expect(Template_Token::BLOCK_END_TYPE);
        }

        return new Template_Node_Set($capture, $names, $values, $lineno, $this->getTag());
    }

    public function decideBlockEnd(Template_Token $token)
    {
        return $token->test('endset');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'set';
    }
}
