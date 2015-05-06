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
 * Imports macros.
 *
 * <pre>
 *   {% from 'forms.html' import forms %}
 * </pre>
 */
class Template_TokenParser_From extends Template_TokenParser
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
        $stream = $this->parser->getStream();
        $stream->expect('import');

        $targets = array();
        do {
            $name = $stream->expect(Template_Token::NAME_TYPE)->getValue();

            $alias = $name;
            if ($stream->nextIf('as')) {
                $alias = $stream->expect(Template_Token::NAME_TYPE)->getValue();
            }

            $targets[$name] = $alias;

            if (!$stream->nextIf(Template_Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        } while (true);

        $stream->expect(Template_Token::BLOCK_END_TYPE);

        $node = new Template_Node_Import($macro, new Template_Node_Expression_AssignName($this->parser->getVarName(), $token->getLine()), $token->getLine(), $this->getTag());

        foreach ($targets as $name => $alias) {
            $this->parser->addImportedSymbol('function', $alias, 'get'.$name, $node->getNode('var'));
        }

        return $node;
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'from';
    }
}
