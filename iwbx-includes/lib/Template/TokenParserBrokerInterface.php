<?php

/*
 * This file is part of Template.
 *
 * (c) 2010 Fabien Potencier
 * (c) 2010 Arnaud Le Blanc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface implemented by token parser brokers.
 *
 * Token parser brokers allows to implement custom logic in the process of resolving a token parser for a given tag name.
 *
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 * @deprecated since 1.12 (to be removed in 2.0)
 */
interface Template_TokenParserBrokerInterface
{
    /**
     * Gets a TokenParser suitable for a tag.
     *
     * @param string $tag A tag name
     *
     * @return null|Template_TokenParserInterface A Template_TokenParserInterface or null if no suitable TokenParser was found
     */
    public function getTokenParser($tag);

    /**
     * Calls Template_TokenParserInterface::setParser on all parsers the implementation knows of.
     *
     * @param Template_ParserInterface $parser A Template_ParserInterface interface
     */
    public function setParser(Template_ParserInterface $parser);

    /**
     * Gets the Template_ParserInterface.
     *
     * @return null|Template_ParserInterface A Template_ParserInterface instance or null
     */
    public function getParser();
}
