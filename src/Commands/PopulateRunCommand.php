<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Actions\PopulateRunInterface;
use App\Actions\PopulateRunResultType;

final class PopulateRunCommand extends Command
{
    public function __construct(
        private PopulateRunInterface $action,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('runs:populate')
            ->setDescription('Populate the metadata of the publications of a curation run')
            ->setHelp('Metadata are downloaded from pubmed')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the curation run.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (int) ((array) $input->getArgument('id'))[0];

        // get the populate publication command.
        $command = $this->command('publication:populate');

        // create the populate publication callable.
        $populate = function (int $pmid) use ($command, $output): bool {
            return $command->run(new ArrayInput(['pmid' => $pmid]), $output) === 0;
        };

        // execute the action and produce a response.
        $result = $this->action->populate($id, $populate);

        return match ($result->type) {
            PopulateRunResultType::Success => $this->success($output, $id, ...$result->xs),
            PopulateRunResultType::NotFound => $this->notfound($output, $id),
            PopulateRunResultType::AlreadyPopulated => $this->alreadyPopulated($output, $id, ...$result->xs),
            PopulateRunResultType::Failure => $this->failure($output, $id, ...$result->xs),
        };
    }

    private function command(string $cmd): Command
    {
        if ($application = $this->getApplication()) {
            return $application->find($cmd);
        }

        throw new \Exception('no application');
    }

    private function success(OutputInterface $output, int $id, string $type, string $name): int
    {
        $output->writeln(
            vsprintf('<info>Metadata of curation run [type => %s, id => %s, name => %s] publications successfully updated.</info>', [
                $type,
                $id,
                $name,
            ])
        );

        return 0;
    }

    private function notfound(OutputInterface $output, int $id): int
    {
        $output->writeln(
            sprintf('<error>No run with id %s</error>', $id)
        );

        return 1;
    }

    private function alreadyPopulated(OutputInterface $output, int $id, string $type, string $name): int
    {
        $output->writeln(
            vsprintf('<info>Metadata of curation run [type => %s, id => %s, name => %s] publications are already populated</info>', [
                $type,
                $id,
                $name,
            ])
        );

        return 1;
    }

    private function failure(OutputInterface $output, int $id, string $type, string $name): int
    {
        $output->writeln(
            vsprintf('<error>Failed to retrieve metadata of run [type => %s, id => %s, name => %s] publications</error>', [
                $type,
                $id,
                $name,
            ])
        );

        return 1;
    }
}
