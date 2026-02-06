<?php

namespace OneToMany\AiBundle;

use OneToMany\AI\Contract\Client\FileClientInterface;
use OneToMany\AI\Contract\Client\QueryClientInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AiBundle extends AbstractBundle
{
    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(FileClientInterface::class)
            ->addTag('1tomany.ai.file_client');

        $builder
            ->registerForAutoconfiguration(QueryClientInterface::class)
            ->addTag('1tomany.ai.query_client');

        $configurator->import('../config/services.yaml');
    }
}
