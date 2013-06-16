<?php

namespace Staffim\Behat\MailExtension;

use Behat\Behat\Extension\ExtensionInterface;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Extension extends \Behat\Behat\Extension\Extension implements ExtensionInterface
{
    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('ExtensionServices.yml');

        foreach ($config as $ns => $tlValue) {
            if (!is_array($tlValue)) {
                $container->setParameter("behat.mail_extension.$ns", $tlValue);
            } else {
                foreach ($tlValue as $name => $value) {
                    $container->setParameter("behat.mail_extension.$ns.$name", $value);
                }
            }
        }
        $container->setParameter('behat.mail_extension.parameters', $config);
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder->
            children()->
                scalarNode('pop3Server')->
                    defaultValue(isset($config['pop3Server']) ? $config['pop3Server'] : 'localhost')->
                end()->
                arrayNode('pop3Auth')->
                    children()->
                        scalarNode('login')->
                            defaultValue(isset($config['pop3Auth']['login']) ? $config['pop3Auth']['login'] : 'anonymous')->
                        end()->
                        scalarNode('password')->
                            defaultValue(isset($config['pop3Auth']['password']) ? $config['pop3Auth']['password'] : '')->
                        end()->
                    end()->
                end()->
                scalarNode('smtpServer')->
                    defaultValue(isset($config['smtpServer']) ? $config['smtpServer'] : 'localhost')->
                end()->
                arrayNode('smtpAuth')->
                    children()->
                        scalarNode('login')->
                            defaultValue(isset($config['smtpAuth']['login']) ? $config['smtpAuth']['login'] : '')->
                        end()->
                        scalarNode('password')->
                            defaultValue(isset($config['smtpAuth']['password']) ? $config['smtpAuth']['password'] : '')->
                        end()->
                    end()->
                end()->
                scalarNode('baseAddress')->
                    defaultValue(isset($config['baseAddress']) ? $config['baseAddress'] : '')->
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
