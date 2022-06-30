<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Actions\StoreRunInterface;
use App\Actions\StoreRunResultType;

final class CreateRunCommand extends Command
{
    public function __construct(
        private StoreRunInterface $action,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(join(':', ['runs', 'create', $this->action->type()]))
            ->setDescription(sprintf('Create a %s curation run', strtoupper($this->action->type())))
            ->setHelp('PMID associated to the curation run are read from STDIN')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the curation run.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ((array) $input->getArgument('name'))[0];

        try {
            $pmids = $this->pmidsFromStdin();
        } catch (\UnexpectedValueException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        $result = $this->action->store($name, ...$pmids);

        return match ($result->type) {
            StoreRunResultType::Success => $this->success($output, $result->id()),
            StoreRunResultType::NoPmid => $this->noPmid($output),
            StoreRunResultType::RunAlreadyExists => $this->runAlreadyExists($output, $name, ...$result->xs),
            StoreRunResultType::NoNewPmid => $this->noNewPmid($output),
        };
    }

    private function success(OutputInterface $output, int $id): int
    {
        $output->writeln(
            sprintf('<info>Curation run created with id %s.</info>', $id)
        );

        return 0;
    }

    private function noPmid(OutputInterface $output): int
    {
        $output->writeln('<error>At least one pmid is required.</error>');

        return 1;
    }

    private function runAlreadyExists(OutputInterface $output, string $name, int $id): int
    {
        $output->writeln(
            vsprintf('<error>Name \'%s\' already used by curation run [type => %s, id => %s, name => %s]</error>', [
                $name,
                $this->action->type(),
                $id,
                $name,
            ]),
        );

        return 1;
    }

    private function noNewPmid(OutputInterface $output): int
    {
        $output->writeln(
            vsprintf('<error>All pmid are already associated with other %s runs</error>', [
                $this->action->type(),
            ])
        );

        return 1;
    }

    /**
     * @return array<int>
     */
    private function pmidsFromStdin(): array
    {
        $pmids = [];

        $stdin = fopen('php://stdin', 'r');

        try {
            while ($stdin && $line = fgets($stdin)) {
                $line = rtrim($line);

                if (empty($line)) continue;

                if (!preg_match('/^[0-9]+$/', $line)) {
                    throw new \UnexpectedValueException(
                        vsprintf('Value \'%s\' from stdin is not a valid PMID', [
                            strlen($line) > 10 ? substr($line, 0, 10) . '...' : $line,
                        ])
                    );
                }

                $pmids[(int) $line] = true;
            }
        } catch (\UnexpectedValueException $e) {
            throw $e;
        } finally {
            $stdin && fclose($stdin);
        }

        return array_keys($pmids);
    }
}
