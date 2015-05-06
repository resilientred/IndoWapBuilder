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
 * Interface implemented by compiler classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @deprecated since 1.12 (to be removed in 2.0)
 */
interface Template_CompilerInterface
{
    /**
     * Compiles a node.
     *
     * @param Template_NodeInterface $node The node to compile
     *
     * @return Template_CompilerInterface The current compiler instance
     */
    public function compile(Template_NodeInterface $node);

    /**
     * Gets the current PHP code after compilation.
     *
     * @return string The PHP code
     */
    public function getSource();
}
