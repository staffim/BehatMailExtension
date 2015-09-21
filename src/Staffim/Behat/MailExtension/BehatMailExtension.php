<?php

namespace Staffim\Behat\MailExtension;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class BehatMailExtension implements ExtensionInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {

    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'mail';
    }

    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     *
     * @param ExtensionManager $extensionManager
     */
    public function initialize(ExtensionManager $extensionManager)
    {

    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder = $builder->children();

        $builder->scalarNode('pop3_host')->defaultValue('localhost');
        $builder->scalarNode('pop3_port')->defaultValue(110);
        $builder->scalarNode('pop3_user')->defaultValue('anonymous');
        $builder->scalarNode('pop3_password')->defaultValue('');
        $builder->scalarNode('smtp_host')->defaultValue(null);
        $builder->scalarNode('smtp_port')->defaultValue(25);
        $builder->scalarNode('smtp_user')->defaultValue('');
        $builder->scalarNode('smtp_password')->defaultValue('');
        $builder->scalarNode('max_duration')->defaultValue(3000);
        $builder->scalarNode('failed_mail_dir')->defaultValue(null);
        $builder->scalarNode('files_path')->defaultValue(null);
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        // TODO Use Symfony\Component\DependencyInjection\Definition and refactor whole method.
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('ExtensionServices.yml');

        if (!isset($config['smtp_host'])) {
            $config['smtp_host'] = $config['pop3_host'];
        }

        foreach ($config as $complexName => $complexValue) {
            $container->setParameter("behat.mail_extension.$complexName", $complexValue);
        }
        $container->setParameter('behat.mail_extension.parameters', $config);
    }
}
