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
 * Marks a section of a template as being reusable.
 *
 * <pre>
 *  {% block head %}
 *    <link rel="stylesheet" href="style.css" />
 *    <title>{% block title %}{% endblock %} - My Webpage</title>
 *  {% endblock %}
 * </pre>
 */
class Template_TokenParser_Block extends Template_TokenParser
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
        if ($this->parser->hasBlock($name)) {
            throw new Template_Error_Syntax(sprintf("The block '$name' has already been defined line %d", $this->parser->getBlock($name)->getLine()), $stream->getCurrent()->getLine(), $stream->getFilename());
        }
        $this->parser->setBlock($name, $block = new Template_Node_Block($name, new Template_Node(array()), $lineno));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);

        if ($stream->nextIf(Template_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            if ($token = $stream->nextIf(Template_Token::NAME_TYPE)) {
                $value = $token->getValue();

                if ($value != $name) {
                    throw new Template_Error_Syntax(sprintf("Expected endblock for block '$name' (but %s given)", $value), $stream->getCurrent()->getLine(), $stream->getFilename());
                }
            }
        } else {
            $body = new Template_Node(array(
                new Template_Node_Print($this->parser->getExpressionParser()->parseExpression(), $lineno),
            ));
        }
        $stream->expect(Template_Token::BLOCK_END_TYPE);

        $block->setNode('body', $body);
        $this->parser->popBlockStack();
        $this->parser->popLocalScope();

        return new Template_Node_BlockReference($name, $lineno, $this->getTag());
    }

    public function decideBlockEnd(Template_Token $token)
    {
        return $token->test('endblock');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'block';
    }
}
