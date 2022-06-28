<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Actions\StoreRunInterface;

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
            return $this->invalidPmid($output, $e->getMessage());
        }

        $result = $this->action->store($name, ...$pmids);

        return match ($result->status()) {
            0 => $this->success($output, $result->id()),
            1 => $this->noPmid($output),
            2 => $this->runAlreadyExists($output, $result->id(), $result->name()),
            3 => $this->associationAlreadyExists($output, $result->id(), $result->name(), $result->pmid()),
        };
    }

    private function success(OutputInterface $output, int $id): int
    {
        $output->writeln(
            sprintf('<info>Curation run created with [\'id\' => %s].</info>', $id)
        );

        return 0;
    }

    private function invalidPmid(OutputInterface $output, string $message): int
    {
        $output->writeln(sprintf('<error>%s</error>', $message));

        return 1;
    }

    private function noPmid(OutputInterface $output): int
    {
        $output->writeln('<error>At least one pmid is required.</error>');

        return 1;
    }

    private function runAlreadyExists(OutputInterface $output, int $id, string $name): int
    {
        $output->writeln(
            vsprintf('<error>Name \'%s\' already used by \'%s\' curation run %s</error>', [
                $name,
                $this->action->type(),
                $id,
            ]),
        );

        return 1;
    }

    private function associationAlreadyExists(OutputInterface $output, int $run_id, string $run_name, int $pmid): int
    {
        $output->writeln(
            vsprintf('<error>Publication with PMID %s is already associated with \'%s\' curation run %s (\'%s\')</error>', [
                $pmid,
                $this->action->type(),
                $run_id,
                $run_name,
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
