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
 * Represents a template filter.
 *
 * Use Template_SimpleFilter instead.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @deprecated since 1.12 (to be removed in 2.0)
 */
interface Template_FilterInterface
{
    /**
     * Compiles a filter.
     *
     * @return string The PHP code for the filter
     */
    public function compile();

    public function needsEnvironment();

    public function needsContext();

    public function getSafe(Template_Node $filterArgs);

    public function getPreservesSafety();

    public function getPreEscape();

    public function setArguments($arguments);

    public function getArguments();
}
