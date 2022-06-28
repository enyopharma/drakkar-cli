<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Actions\PopulatePublicationInterface;

final class PopulatePublicationCommand extends Command
{
    public function __construct(
        private PopulatePublicationInterface $action,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('publications:populate')
            ->setDescription('Populate the metadata of a publication')
            ->setHelp('Metadata are downloaded from pubmed')
            ->addArgument('pmid', InputArgument::REQUIRED, 'The pmid of the publication.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pmid = (int) ((array) $input->getArgument('pmid'))[0];

        $result = $this->action->populate($pmid);

        return match ($result->status()) {
            0 => $this->success($output, $pmid),
            1 => $this->alreadyPopulated($output, $pmid),
            2 => $this->notFound($output, $pmid),
            3 => $this->parsingError($output, $pmid, $result->message()),
        };
    }

    private function success(OutputInterface $output, int $pmid): int
    {
        $output->writeln(
            sprintf('<info>Metadata of publication with [\'pmid\' => %s] successfully updated.</info>', $pmid)
        );

        return 0;
    }

    private function alreadyPopulated(OutputInterface $output, int $pmid): int
    {
        $output->writeln(
            sprintf('<info>Metadata of publication with [\'pmid\' => %s] already populated</info>', $pmid)
        );

        return 1;
    }

    private function notFound(OutputInterface $output, int $pmid): int
    {
        $output->writeln(
            sprintf('<error>No publication with [\'pmid\' => %s]</error>', $pmid)
        );

        return 1;
    }

    private function parsingError(OutputInterface $output, int $pmid, string $message): int
    {
        $output->writeln(
            sprintf('<error>Failed to retrieve metadata of publication with [\'pmid\' => %s] - %s</error>', $pmid, $message)
        );

        return 1;
    }
}
