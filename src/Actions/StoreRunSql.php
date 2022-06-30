<?php

declare(strict_types=1);

namespace App\Actions;

final class StoreRunSql implements StoreRunInterface
{
    const INSERT_RUN_SQL = <<<SQL
        INSERT INTO runs (type, name) VALUES (?, ?)
    SQL;

    const INSERT_PUBLICATION_SQL = <<<SQL
        INSERT INTO publications (pmid) VALUES (?)
    SQL;

    const INSERT_ASSOCIATION_SQL = <<<SQL
        INSERT INTO associations (run_id, pmid) VALUES (?, ?)
    SQL;

    const SELECT_RUN_SQL = <<<SQL
        SELECT * FROM runs WHERE  name = ?
    SQL;

    const SELECT_PUBLICATION_SQL = <<<SQL
        SELECT * FROM publications WHERE pmid = ?
    SQL;

    const SELECT_PUBLICATIONS_SQL = <<<SQL
        SELECT a.pmid
        FROM runs AS r, associations AS a
        WHERE r.id = a.run_id
        AND r.type = ?
        AND a.pmid IN(%s)
    SQL;

    public function __construct(
        private \PDO $pdo,
        private string $type,
    ) {
        if (!in_array($type, ['hh', 'vh'])) {
            throw new \InvalidArgumentException('Run type must either be \'hh\' or \'vh\'');
        }
    }

    public function type(): string
    {
        return $this->type;
    }

    public function store(string $name, int ...$pmids): StoreRunResult
    {
        // ensure there is at least one pmid.
        if (count($pmids) == 0) {
            return StoreRunResult::noPmid();
        }

        // prepare the queries.
        $insert_run_sth = $this->pdo->prepare(self::INSERT_RUN_SQL);
        $insert_publication_sth = $this->pdo->prepare(self::INSERT_PUBLICATION_SQL);
        $insert_association_sth = $this->pdo->prepare(self::INSERT_ASSOCIATION_SQL);
        $select_run_sth = $this->pdo->prepare(self::SELECT_RUN_SQL);
        $select_publication_sth = $this->pdo->prepare(self::SELECT_PUBLICATION_SQL);

        $select_publications_sth = $this->pdo->prepare(vsprintf(self::SELECT_PUBLICATIONS_SQL, [
            implode(', ', array_pad([], count($pmids), '?')),
        ]));

        // return an error when a run with the same name already exist.
        $select_run_sth->execute([$name]);

        if ($run = $select_run_sth->fetch()) {
            return StoreRunResult::runAlreadyExists($run['id']);
        }

        // select the pmid of the publications already associated with a curation
        // run of the same type, then remove them from the pmid array. Error when
        // no pmid is not already present.
        $select_publications_sth->execute(array_merge([$this->type], $pmids));

        $existing = $select_publications_sth->fetchAll(\PDO::FETCH_COLUMN);

        $diff = array_diff($pmids, $existing);

        if (count($diff) == 0) {
            return StoreRunResult::noNewPmid();
        }

        // insert the curation run, the missing pmids and associations.
        $this->pdo->beginTransaction();

        $insert_run_sth->execute([$this->type, $name]);

        $id = (int) $this->pdo->lastInsertId();

        foreach ($diff as $pmid) {
            $select_publication_sth->execute([$pmid]);

            if (!$select_publication_sth->fetch()) {
                $insert_publication_sth->execute([$pmid]);
            }

            $insert_association_sth->execute([$id, $pmid]);
        }

        $this->pdo->commit();

        // success !
        return StoreRunResult::success($id);
    }
}
