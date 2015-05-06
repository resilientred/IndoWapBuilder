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
 * Interface implemented by token parsers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Template_TokenParserInterface
{
    /**
     * Sets the parser associated with this token parser
     *
     * @param $parser A Template_Parser instance
     */
    public function setParser(Template_Parser $parser);

    /**
     * Parses a token and returns a node.
     *
     * @param Template_Token $token A Template_Token instance
     *
     * @return Template_NodeInterface A Template_NodeInterface instance
     *
     * @throws Template_Error_Syntax
     */
    public function parse(Template_Token $token);

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag();
}
