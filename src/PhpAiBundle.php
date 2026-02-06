<?php

namespace OneToMany\PhpAiBundle;

use OneToMany\AI\Contract\Client\FileClientInterface;
use OneToMany\AI\Contract\Client\QueryClientInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class PhpAiBundle extends AbstractBundle
{
    /**
     * @var array{
     *   gemini: non-empty-list<class-string>,
     *   openai: non-empty-list<class-string>,
     * }
     */
    private array $clients = [
        'gemini' => [
            \OneToMany\AI\Client\Gemini\FileClient::class,
            \OneToMany\AI\Client\Gemini\QueryClient::class,
        ],
        'openai' => [
            \OneToMany\AI\Client\OpenAi\FileClient::class,
            \OneToMany\AI\Client\OpenAi\QueryClient::class,
        ],
    ];

    public function configure(DefinitionConfigurator $definition): void
    {
        /**
         * @disregard P1013 Undefined method rootNode()
         */
        $definition
            ->rootNode()
                ->children()
                    ->arrayNode('gemini')
                        ->canBeEnabled()
                        ->children()
                            ->scalarNode('api_key')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('openai')
                        ->canBeEnabled()
                        ->children()
                            ->scalarNode('api_key')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end();
    }

    /**
     * @param array{
     *   gemini: array{
     *     api_key?: non-empty-string,
     *     enabled: bool,
     *   },
     *   openai: array{
     *     api_key?: non-empty-string,
     *     enabled: bool,
     *   }
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(FileClientInterface::class)
            ->addTag('1tomany.ai.file_client');

        $builder
            ->registerForAutoconfiguration(QueryClientInterface::class)
            ->addTag('1tomany.ai.query_client');

        foreach ($config as $platform => $settings) {
            $apiKey = $settings['api_key'] ?? null;

            if ($settings['enabled'] && null !== $apiKey) {
                foreach ($this->clients[$platform] as $clientClass) {
                    $builder
                        ->register($clientClass)
                        ->setAutowired(true)
                        ->setAutoconfigured(true)
                        ->setArgument('$apiKey', $apiKey);
                }
            }
        }

        $configurator->import('../config/services.yaml');
    }
}
