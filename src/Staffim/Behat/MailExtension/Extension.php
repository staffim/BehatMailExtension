<?php

namespace Staffim\Behat\MailExtension;

use Behat\Behat\Console\BehatApplication;
use Behat\Behat\Extension\ExtensionInterface;
use Behat\Behat\Extension\Extension as BehatExtension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Extension extends BehatExtension implements ExtensionInterface
{
    /**
     * @param array                                                   $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('ExtensionServices.yml');

        if (!isset($config['smtpServer'])) {
            $config['smtpServer'] = $config['pop3Server'];
        }

        foreach ($config as $complexName => $complexValue) {
            if (!is_array($complexValue)) {
                $container->setParameter("behat.mail_extension.$complexName", $complexValue);
            } else {
                foreach ($complexValue as $name => $value) {
                    $container->setParameter("behat.mail_extension.$complexName.$name", $value);
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
                    defaultValue(isset($config['smtpServer']) ? $config['smtpServer'] : null)->
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
                    defaultValue(isset($config['baseAddress']) ? $config['baseAddress'] : null)->
                end()->
                scalarNode('maxSleepTime')->
                    defaultValue(isset($config['maxSleepTime']) ? $config['maxSleepTime'] : 2000)->
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
