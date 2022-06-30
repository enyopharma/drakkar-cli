<?php

declare(strict_types=1);

namespace App\Actions;

final class PopulateRunSql implements PopulateRunInterface
{
    const SELECT_RUN_SQL = <<<SQL
        SELECT * FROM runs WHERE id = ?
    SQL;

    const SELECT_PUBLICATIONS_SQL = <<<SQL
        SELECT p.*
        FROM publications AS p, associations AS a
        WHERE p.pmid = a.pmid AND a.run_id = ?
        AND p.populated IS FALSE
    SQL;

    const UPDATE_RUN_SQL = <<<SQL
        UPDATE runs SET populated = TRUE WHERE id = ?
    SQL;

    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function populate(int $id, callable $populate): PopulateRunResult
    {
        // prepare the queries.
        $select_run_sth = $this->pdo->prepare(self::SELECT_RUN_SQL);
        $select_publications_sth = $this->pdo->prepare(self::SELECT_PUBLICATIONS_SQL);
        $update_run_sth = $this->pdo->prepare(self::UPDATE_RUN_SQL);

        // select the curation run.
        $select_run_sth->execute([$id]);

        if (!$run = $select_run_sth->fetch()) {
            return PopulateRunResult::notFound();
        }

        if ($run['populated']) {
            return PopulateRunResult::alreadyPopulated($run['type'], $run['name']);
        }

        // populate the run publications using the callable.
        $errors = 0;

        $select_publications_sth->execute([$run['id']]);

        while ($publication = $select_publications_sth->fetch()) {
            if (!$populate($publication['pmid'])) {
                $errors++;
            }
        }

        // write a failure when there is errors.
        if ($errors > 0) {
            return PopulateRunResult::failure($run['type'], $run['name']);
        }

        // update the run to ensure it is in populated state (ex = all publications already populated)
        $update_run_sth->execute([$id]);

        // success !
        return PopulateRunResult::success($run['type'], $run['name']);
    }
}
