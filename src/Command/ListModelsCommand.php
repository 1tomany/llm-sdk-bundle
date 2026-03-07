<?php

namespace OneToMany\LlmSdkBundle\Command;

use OneToMany\LlmSdk\Client\Anthropic\AnthropicClient;
use OneToMany\LlmSdk\Client\Gemini\GeminiClient;
use OneToMany\LlmSdk\Client\OpenAi\OpenAiClient;
use OneToMany\LlmSdk\Contract\Client\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ListModelsCommand extends Command
{
    /**
     * @var non-empty-list<class-string<ClientInterface>>
     */
    private array $clients = [
        AnthropicClient::class,
        GeminiClient::class,
        OpenAiClient::class,
    ];

    public function __invoke(SymfonyStyle $io): int
    {
        foreach ($this->clients as $client) {
            $io->section($client);
            $io->listing($client::getModels());
        }

        return Command::SUCCESS;
    }

    /**
     * @see Symfony\Component\Console\Command\Command
     */
    protected function configure(): void
    {
        $this
            ->setName('onetomany:llm-sdk:list-models')
            ->setDescription('Lists all available models by client');
    }
}
