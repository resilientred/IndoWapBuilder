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
 * Interface implemented by parser classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @deprecated since 1.12 (to be removed in 2.0)
 */
interface Template_ParserInterface
{
    /**
     * Converts a token stream to a node tree.
     *
     * @param Template_TokenStream $stream A token stream instance
     *
     * @return Template_Node_Module A node tree
     *
     * @throws Template_Error_Syntax When the token stream is syntactically or semantically wrong
     */
    public function parse(Template_TokenStream $stream);
}
