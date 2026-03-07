<?php

namespace OneToMany\LlmSdkBundle;

use OneToMany\LlmSdk\Action\Batch\CreateBatchAction;
use OneToMany\LlmSdk\Action\Batch\ReadBatchAction;
use OneToMany\LlmSdk\Action\File\DeleteFileAction;
use OneToMany\LlmSdk\Action\File\UploadFileAction;
use OneToMany\LlmSdk\Action\Query\CompileQueryAction;
use OneToMany\LlmSdk\Action\Query\ExecuteQueryAction;
use OneToMany\LlmSdk\Client\Anthropic\BaseClient as AnthropicBaseClient;
use OneToMany\LlmSdk\Client\Anthropic\BatchClient as AnthropicBatchClient;
use OneToMany\LlmSdk\Client\Anthropic\FileClient as AnthropicFileClient;
use OneToMany\LlmSdk\Client\Gemini\BaseClient as GeminiBaseClient;
use OneToMany\LlmSdk\Client\Gemini\BatchClient as GeminiBatchClient;
use OneToMany\LlmSdk\Client\Gemini\FileClient as GeminiFileClient;
use OneToMany\LlmSdk\Client\Gemini\QueryClient as GeminiQueryClient;
use OneToMany\LlmSdk\Client\Mock\BaseClient as MockBaseClient;
use OneToMany\LlmSdk\Client\Mock\BatchClient as MockBatchClient;
use OneToMany\LlmSdk\Client\Mock\FileClient as MockFileClient;
use OneToMany\LlmSdk\Client\Mock\QueryClient as MockQueryClient;
use OneToMany\LlmSdk\Client\OpenAi\BaseClient as OpenAiBaseClient;
use OneToMany\LlmSdk\Client\OpenAi\BatchClient as OpenAiBatchClient;
use OneToMany\LlmSdk\Client\OpenAi\FileClient as OpenAiFileClient;
use OneToMany\LlmSdk\Client\OpenAi\QueryClient as OpenAiQueryClient;
use OneToMany\LlmSdk\Contract\Action\Batch\CreateBatchActionInterface;
use OneToMany\LlmSdk\Contract\Action\Batch\ReadBatchActionInterface;
use OneToMany\LlmSdk\Contract\Action\File\DeleteFileActionInterface;
use OneToMany\LlmSdk\Contract\Action\File\UploadFileActionInterface;
use OneToMany\LlmSdk\Contract\Action\Query\CompileQueryActionInterface;
use OneToMany\LlmSdk\Contract\Action\Query\ExecuteQueryActionInterface;
use OneToMany\LlmSdk\Factory\BatchClientFactory;
use OneToMany\LlmSdk\Factory\FileClientFactory;
use OneToMany\LlmSdk\Factory\QueryClientFactory;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class LlmSdkBundle extends AbstractBundle
{
    protected string $extensionAlias = 'onetomany_llmsdk';

    /**
     * @param DefinitionConfigurator<'array'> $definition
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
                ->children()
                    ->arrayNode('anthropic')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->stringNode('api_key')
                                ->cannotBeEmpty()
                                ->defaultValue('@@anthropic-api-key')
                            ->end()
                            ->stringNode('api_version')
                                ->cannotBeEmpty()
                                ->defaultValue('2023-06-01')
                            ->end()
                            ->stringNode('http_client')
                                ->cannotBeEmpty()
                                ->defaultValue('http_client')
                            ->end()
                            ->stringNode('serializer')
                                ->cannotBeEmpty()
                                ->defaultValue('serializer')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('gemini')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->stringNode('api_key')
                                ->cannotBeEmpty()
                                ->defaultValue('@@gemini-api-key')
                            ->end()
                            ->stringNode('api_version')
                                ->cannotBeEmpty()
                                ->defaultValue('v1beta')
                            ->end()
                            ->stringNode('http_client')
                                ->cannotBeEmpty()
                                ->defaultValue('http_client')
                            ->end()
                            ->stringNode('serializer')
                                ->cannotBeEmpty()
                                ->defaultValue('serializer')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('openai')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->stringNode('api_key')
                                ->cannotBeEmpty()
                                ->defaultValue('@@openai-api-key')
                            ->end()
                            ->stringNode('http_client')
                                ->cannotBeEmpty()
                                ->defaultValue('http_client')
                            ->end()
                            ->stringNode('serializer')
                                ->cannotBeEmpty()
                                ->defaultValue('serializer')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array{
     *   anthropic: array{
     *     api_key: non-empty-string,
     *     http_client: non-empty-string,
     *     serializer: non-empty-string,
     *   },
     *   gemini: array{
     *     api_key: non-empty-string,
     *     http_client: non-empty-string,
     *     serializer: non-empty-string,
     *   },
     *   openai: array{
     *     api_key: non-empty-string,
     *     http_client: non-empty-string,
     *     serializer: non-empty-string,
     *   }
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container
            ->services()
                // Factories
                ->set(BatchClientFactory::class)
                    ->arg('$clients', tagged_iterator('onetomany.llmsdk.client.batch'))
                ->set(FileClientFactory::class)
                    ->arg('$clients', tagged_iterator('onetomany.llmsdk.client.file'))
                ->set(QueryClientFactory::class)
                    ->arg('$clients', tagged_iterator('onetomany.llmsdk.client.query'))

                // Base Clients
                ->set(AnthropicBaseClient::class)
                    ->abstract(true)
                    ->arg('$apiKey', $config['anthropic']['api_key'])
                    ->arg('$httpClient', service($config['anthropic']['http_client']))
                    ->arg('$denormalizer', service($config['anthropic']['serializer']))
                ->set(GeminiBaseClient::class)
                    ->abstract(true)
                    ->arg('$apiKey', $config['gemini']['api_key'])
                    ->arg('$httpClient', service($config['gemini']['http_client']))
                    ->arg('$denormalizer', service($config['gemini']['serializer']))
                ->set(MockBaseClient::class)
                    ->abstract(true)
                ->set(OpenAiBaseClient::class)
                    ->abstract(true)
                    ->arg('$apiKey', $config['openai']['api_key'])
                    ->arg('$httpClient', service($config['openai']['http_client']))
                    ->arg('$denormalizer', service($config['openai']['serializer']))

                // Batch Actions
                ->set(CreateBatchAction::class)
                    ->arg('$clientFactory', service(BatchClientFactory::class))
                    ->alias(CreateBatchActionInterface::class, service(CreateBatchAction::class))
                ->set(ReadBatchAction::class)
                    ->arg('$clientFactory', service(BatchClientFactory::class))
                    ->alias(ReadBatchActionInterface::class, service(ReadBatchAction::class))

                // Batch Clients
                ->set(AnthropicBatchClient::class)
                    ->tag('onetomany.llmsdk.client.batch')
                    ->parent(AnthropicBaseClient::class)
                ->set(GeminiBatchClient::class)
                    ->tag('onetomany.llmsdk.client.batch')
                    ->parent(GeminiBaseClient::class)
                ->set(MockBatchClient::class)
                    ->tag('onetomany.llmsdk.client.batch')
                    ->parent(MockBaseClient::class)
                ->set(OpenAiBatchClient::class)
                    ->tag('onetomany.llmsdk.client.batch')
                    ->parent(OpenAiBaseClient::class)

                // File Actions
                ->set(UploadFileAction::class)
                    ->arg('$clientFactory', service(FileClientFactory::class))
                    ->alias(UploadFileActionInterface::class, service(UploadFileAction::class))
                ->set(DeleteFileAction::class)
                    ->arg('$clientFactory', service(FileClientFactory::class))
                    ->alias(DeleteFileActionInterface::class, service(DeleteFileAction::class))

                // File Clients
                ->set(AnthropicFileClient::class)
                    ->tag('onetomany.llmsdk.client.file')
                    ->parent(AnthropicBaseClient::class)
                ->set(GeminiFileClient::class)
                    ->tag('onetomany.llmsdk.client.file')
                    ->parent(GeminiBaseClient::class)
                ->set(MockFileClient::class)
                    ->tag('onetomany.llmsdk.client.file')
                    ->parent(MockBaseClient::class)
                ->set(OpenAiFileClient::class)
                    ->tag('onetomany.llmsdk.client.file')
                    ->parent(OpenAiBaseClient::class)

                // Query Actions
                ->set(CompileQueryAction::class)
                    ->arg('$clientFactory', service(QueryClientFactory::class))
                    ->alias(CompileQueryActionInterface::class, service(CompileQueryAction::class))
                ->set(ExecuteQueryAction::class)
                    ->arg('$clientFactory', service(QueryClientFactory::class))
                    ->alias(ExecuteQueryActionInterface::class, service(ExecuteQueryAction::class))

                // Query Clients
                ->set(GeminiQueryClient::class)
                    ->tag('onetomany.llmsdk.client.query')
                    ->parent(GeminiBaseClient::class)
                ->set(MockQueryClient::class)
                    ->tag('onetomany.llmsdk.client.query')
                    ->parent(MockBaseClient::class)
                ->set(OpenAiQueryClient::class)
                    ->tag('onetomany.llmsdk.client.query')
                    ->parent(OpenAiBaseClient::class)
        ;
    }
}
