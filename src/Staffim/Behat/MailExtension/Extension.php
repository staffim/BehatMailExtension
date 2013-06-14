<?php

namespace Staffim\Behat\MailExtension;

//use Behat\Behat\Extension\Extension;
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
        $container->setParameter('behat.mail_extension.server', $config['mailServer']);
        $container->setParameter('behat.mail_extension.address', $config['mailAddress']);
        $container->setParameter('behat.mail_extension.auth', $config['mailAuth']);
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder->
            children()->
                scalarNode('mailServer')->
                    defaultNull()->
                end()->
                arrayNode('mailAuth')->
                    children()->
                        scalarNode('login')->
                            defaultValue('anonymous')->
                        end()->
                        scalarNode('password')->
                            defaultValue('')->
                        end()->
                    end()->
                end()->
                scalarNode('mailAddress')->
                    defaultNull()->
                end()->
            end()->
        end();
    }

    /**
     * @return array
     */
    public function getCompilerPasses()
    {
        return array();
    }
}
