<?php
    /**
     * Database class
     */

    namespace Lib;

    use \PDO, \PDOException;

    class DB {
        private $pdo;
        private $table = 'snapshots';

        /**
         * DB constructor.
         * @param $credentials
         */
        public function __construct($credentials) {
            $dsn = 'mysql:host=' . $credentials['DB_HOST'] . ';dbname=' . $credentials['DB_NAME'] . ';charset=utf8';
            $opt = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            try {
                $this->pdo = new PDO($dsn, $credentials['DB_USER'], $credentials['DB_PASSWORD'], $opt);
            } catch (PDOException $e) {
                die('Подключение не удалось: ' . $e->getMessage());
            }
        }

        /**
         * Insert
         * @param array $data
         */
        public function insert($data) {
            $fields = $placeholders = $values = [];

            foreach ($data as $field => $value) {
                $fields[] = $field;
                $placeholders[] = ':' . $field;
                $values[$field] = $value;
            }

            $query_fields = implode(',', $fields);
            $query_placeholders = implode(',', $placeholders);
            $query = 'INSERT INTO ' . $this->table . ' (' . $query_fields . ') VALUES (' . $query_placeholders . ')';

            $st = $this->pdo->prepare($query);
            $st->execute($values);
        }

        public function getLast() {
            $query = 'SELECT * FROM ' . $this->table . ' WHERE date < "' . date('Y-m-d') . '" ORDER BY date desc LIMIT 1';

            $st = $this->pdo->prepare($query);
            $st->execute([]);
            $result = $st->fetchAll(PDO::FETCH_ASSOC);

            if (!$result) {
                exit('Предыдущие данные не найдены');
            }
            if ($result && count($result)) {
                $result = unserialize($result[0]['data']);
            }
            if (!$result) {
                exit('Невозможно прочитать предыдущие данные');
            }

            return $result;
        }
    }