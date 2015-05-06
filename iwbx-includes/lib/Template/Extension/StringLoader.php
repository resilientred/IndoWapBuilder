<?php

/*
 * This file is part of Template.
 *
 * (c) 2012 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Template_Extension_StringLoader extends Template_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Template_SimpleFunction('template_from_string', 'template_template_from_string', array('needs_environment' => true)),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'string_loader';
    }
}

/**
 * Loads a template from a string.
 *
 * <pre>
 * {{ include(template_from_string("Hello {{ name }}")) }}
 * </pre>
 *
 * @param Template_Environment $env      A Template_Environment instance
 * @param string           $template A template as a string
 *
 * @return Template_Template A Template_Template instance
 */
function template_template_from_string(Template_Environment $env, $template)
{
    $name = sprintf('__string_template__%s', hash('sha256', uniqid(mt_rand(), true), false));

    $loader = new Template_Loader_Chain(array(
        new Template_Loader_Array(array($name => $template)),
        $current = $env->getLoader(),
    ));

    $env->setLoader($loader);
    try {
        $template = $env->loadTemplate($name);
    } catch (Exception $e) {
        $env->setLoader($current);

        throw $e;
    }
    $env->setLoader($current);

    return $template;
}
