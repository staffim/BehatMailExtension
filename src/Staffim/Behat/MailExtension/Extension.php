<?php

namespace Staffim\Behat\MailExtension;

use Behat\Behat\Extension\ExtensionInterface;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Extension implements ExtensionInterface
{
    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(array $config, ContainerBuilder $container)
    {

    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {

    }

    /**
     * @return array
     */
    public function getCompilerPasses()
    {
        return array();
    }
}
