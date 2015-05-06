<?php

/*
 * This file is part of Template.
 *
 * (c) 2011 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Imports blocks defined in another template into the current template.
 *
 * <pre>
 * {% extends "base.html" %}
 *
 * {% use "blocks.html" %}
 *
 * {% block title %}{% endblock %}
 * {% block content %}{% endblock %}
 * </pre>
 *
 * @see http://www.template-project.org/doc/templates.html#horizontal-reuse for details.
 */
class Template_TokenParser_Use extends Template_TokenParser
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
        $template = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();

        if (!$template instanceof Template_Node_Expression_Constant) {
            throw new Template_Error_Syntax('The template references in a "use" statement must be a string.', $stream->getCurrent()->getLine(), $stream->getFilename());
        }

        $targets = array();
        if ($stream->nextIf('with')) {
            do {
                $name = $stream->expect(Template_Token::NAME_TYPE)->getValue();

                $alias = $name;
                if ($stream->nextIf('as')) {
                    $alias = $stream->expect(Template_Token::NAME_TYPE)->getValue();
                }

                $targets[$name] = new Template_Node_Expression_Constant($alias, -1);

                if (!$stream->nextIf(Template_Token::PUNCTUATION_TYPE, ',')) {
                    break;
                }
            } while (true);
        }

        $stream->expect(Template_Token::BLOCK_END_TYPE);

        $this->parser->addTrait(new Template_Node(array('template' => $template, 'targets' => new Template_Node($targets))));
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'use';
    }
}
