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
 * Base class for all token parsers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Template_TokenParser implements Template_TokenParserInterface
{
    /**
     * @var Template_Parser
     */
    protected $parser;

    /**
     * Sets the parser associated with this token parser
     *
     * @param $parser A Template_Parser instance
     */
    public function setParser(Template_Parser $parser)
    {
        $this->parser = $parser;
    }
}
