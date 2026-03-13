<?php

namespace OneToMany\LlmSdkBundle\Command;

use OneToMany\LlmSdk\Contract\Enum\Model;
use OneToMany\LlmSdk\Contract\Enum\Vendor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_map;

final class ListModelsCommand extends Command
{
    public function __invoke(SymfonyStyle $io): int
    {
        $getModels = function (Vendor $vendor): array {
            $mapper = function (Model $model): string {
                return $model->getValue();
            };

            return array_map($mapper, $vendor->getModels());
        };

        foreach (Vendor::cases() as $vendor) {
            $io->section($vendor->getValue());
            $io->listing($getModels($vendor));
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
            ->setDescription('Lists all available models by vendor');
    }
}
