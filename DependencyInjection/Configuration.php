<?php

namespace WidgetsNL\DoctrineEncryptionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('widgetsnl');

        $rootNode
            ->children()
            ->arrayNode('doctrine_encryptor')
            ->children()
            ->scalarNode('secret_key')
            ->end()
            ->scalarNode('algorithm')
            ->defaultValue('WidgetsNL\\DoctrineEncryptionBundle\\Algorithm\\Aes')
            ->end()
            ->end()
            ->end();


        return $treeBuilder;
    }
}
