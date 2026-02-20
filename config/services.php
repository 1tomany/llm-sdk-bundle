<?php

use OneToMany\AI\Clients\Action\File\DeleteFileAction;
use OneToMany\AI\Clients\Action\File\UploadFileAction;
use OneToMany\AI\Clients\Action\Query\CompileQueryAction;
use OneToMany\AI\Clients\Action\Query\ExecuteQueryAction;
use OneToMany\AI\Clients\Client\Claude\FileClient as ClaudeFileClient;
use OneToMany\AI\Clients\Client\Gemini\FileClient as GeminiFileClient;
use OneToMany\AI\Clients\Client\Gemini\QueryClient as GeminiQueryClient;
use OneToMany\AI\Clients\Client\Mock\FileClient as MockFileClient;
use OneToMany\AI\Clients\Client\Mock\QueryClient as MockQueryClient;
use OneToMany\AI\Clients\Client\OpenAI\FileClient as OpenAIFileClient;
use OneToMany\AI\Clients\Client\OpenAI\QueryClient as OpenAIQueryClient;
use OneToMany\AI\Clients\Contract\Action\File\DeleteFileActionInterface;
use OneToMany\AI\Clients\Contract\Action\File\UploadFileActionInterface;
use OneToMany\AI\Clients\Contract\Action\Query\CompileQueryActionInterface;
use OneToMany\AI\Clients\Contract\Action\Query\ExecuteQueryActionInterface;
use OneToMany\AI\Clients\Factory\ClientFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container
        ->services()
            // Client Factories
            ->set('otm.ai.clients.factory.client', ClientFactory::class)
                ->abstract(true)
            ->set('otm.ai.clients.factory.client.file', ClientFactory::class)
                ->arg('$clients', tagged_iterator('otm.ai.clients.client.file'))
            ->set('otm.ai.clients.factory.client.query', ClientFactory::class)
                ->arg('$clients', tagged_iterator('otm.ai.clients.client.query'))

            // File Actions
            ->alias(DeleteFileActionInterface::class, service('otm.ai.clients.action.file.delete'))
            ->alias(UploadFileActionInterface::class, service('otm.ai.clients.action.file.upload'))
            ->set('otm.ai.clients.action.file.delete', DeleteFileAction::class)
                ->arg('$clientFactory', service('otm.ai.clients.factory.client.file'))
            ->set('otm.ai.clients.action.file.upload', UploadFileAction::class)
                ->arg('$clientFactory', service('otm.ai.clients.factory.client.file'))

            // File Clients
            ->set('otm.ai.clients.client.claude.file', ClaudeFileClient::class)
                ->tag('otm.ai.clients.client.file')
            ->set('otm.ai.clients.client.gemini.file', GeminiFileClient::class)
                ->tag('otm.ai.clients.client.file')
            ->set('otm.ai.clients.client.mock.file', MockFileClient::class)
                ->tag('otm.ai.clients.client.file')
            ->set('otm.ai.clients.client.openai.file', OpenAIFileClient::class)
                ->tag('otm.ai.clients.client.file')

            // Query Actions
            ->alias(CompileQueryActionInterface::class, service('otm.ai.clients.action.query.compile'))
            ->alias(ExecuteQueryActionInterface::class, service('otm.ai.clients.action.query.execute'))
            ->set('otm.ai.clients.action.query.compile', CompileQueryAction::class)
                ->arg('$clientFactory', service('otm.ai.clients.factory.client.query'))
            ->set('otm.ai.clients.action.query.execute', ExecuteQueryAction::class)
                ->arg('$clientFactory', service('otm.ai.clients.factory.client.query'))

            // Query Clients
            ->set('otm.ai.clients.client.gemini.query', GeminiQueryClient::class)
                ->tag('otm.ai.clients.client.query')
            ->set('otm.ai.clients.client.mock.query', MockQueryClient::class)
                ->tag('otm.ai.clients.client.query')
            ->set('otm.ai.clients.client.openai.query', OpenAIQueryClient::class)
                ->tag('otm.ai.clients.client.query')
    ;
};
