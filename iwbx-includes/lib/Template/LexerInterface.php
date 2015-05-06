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
 * Interface implemented by lexer classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @deprecated since 1.12 (to be removed in 2.0)
 */
interface Template_LexerInterface
{
    /**
     * Tokenizes a source code.
     *
     * @param string $code     The source code
     * @param string $filename A unique identifier for the source code
     *
     * @return Template_TokenStream A token stream instance
     *
     * @throws Template_Error_Syntax When the code is syntactically wrong
     */
    public function tokenize($code, $filename = null);
}
