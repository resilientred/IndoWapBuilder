<?php

/*
 * This file is part of Template.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Tests a condition.
 *
 * <pre>
 * {% if users %}
 *  <ul>
 *    {% for user in users %}
 *      <li>{{ user.username|e }}</li>
 *    {% endfor %}
 *  </ul>
 * {% endif %}
 * </pre>
 */
class Template_TokenParser_If extends Template_TokenParser
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
        $expr = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();
        $stream->expect(Template_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideIfFork'));
        $tests = array($expr, $body);
        $else = null;

        $end = false;
        while (!$end) {
            switch ($stream->next()->getValue()) {
                case 'else':
                    $stream->expect(Template_Token::BLOCK_END_TYPE);
                    $else = $this->parser->subparse(array($this, 'decideIfEnd'));
                    break;

                case 'elseif':
                    $expr = $this->parser->getExpressionParser()->parseExpression();
                    $stream->expect(Template_Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse(array($this, 'decideIfFork'));
                    $tests[] = $expr;
                    $tests[] = $body;
                    break;

                case 'endif':
                    $end = true;
                    break;

                default:
                    throw new Template_Error_Syntax(sprintf('Unexpected end of template. Template was looking for the following tags "else", "elseif", or "endif" to close the "if" block started at line %d)', $lineno), $stream->getCurrent()->getLine(), $stream->getFilename());
            }
        }

        $stream->expect(Template_Token::BLOCK_END_TYPE);

        return new Template_Node_If(new Template_Node($tests), $else, $lineno, $this->getTag());
    }

    public function decideIfFork(Template_Token $token)
    {
        return $token->test(array('elseif', 'else', 'endif'));
    }

    public function decideIfEnd(Template_Token $token)
    {
        return $token->test(array('endif'));
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'if';
    }
}
