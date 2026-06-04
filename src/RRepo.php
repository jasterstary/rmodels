<?php

namespace JasterStary\RModels;

use \RedBeanPHP\R as R;
use \RedBeanPHP\OODBBean;

/**
 * RRepo Object Class.
 *
 * @author    Jaster Stary <jasterstary@gmail.com>
 * @copyright 2023 Copyright (c) Jaster Stary
 * @license   MIT https://github.com/jasterstary/rmodels/
 * @link      https://github.com/jasterstary/rmodels/
 */

class RRepo
{

    /**
    * @var int $limit
    */
    protected int $limit = 10;

    /**
    * @var int $offset
    */
    protected int $offset = 0;

    /**
    * @var string $table
    */
    protected static string $table = 'basic';

    /**
    * @var array<mixed> $columns
    */
    protected static array $columns = [];

    /**
    * @var array<mixed> $hardConditions
    */
    protected static array $hardConditions = [];

    /**
    * @var array<mixed> $deniedColumns
    */
    protected static array $deniedColumns = [];

    /**
    * @var array<mixed> $shared
    */
    protected static array $shared = [];

    /**
    * @var array<mixed> $_logs
    */
    protected static array $_logs = [];

    /**
    * constructor
    *
    */
    function __construct() {

    }

    /**
    * setLimit
    *
    * @param int $n
    * @return RRepo
    */
    function setLimit(int $n):RRepo {
      $this->limit = $n;
      return $this;
    }

    /**
    * setOffset
    *
    * @param int $n
    * @return RRepo
    */
    function setOffset(int $n):RRepo {
      $this->offset = $n;
      return $this;
    }

    /**
    * log
    *
    * @param string $log
    * @return void
    */
    static function log(string $log):void {
      self::$_logs[] = $log;
    }

    /**
    * get all
    *
    * @return array<mixed>
    */
    function getAll():array {
      ob_start();
      $a = array_values(R::findAll(static::$table, ' ORDER BY title '));
      ob_end_clean();
      //self::log();
      return $a;
    }

    /**
    * merge data
    *
    * @param \RedBeanPHP\OODBBean $bean
    * @param array<mixed> $data
    * @return void
    */
    protected static function mergeData(\RedBeanPHP\OODBBean &$bean, array $data):void {
      foreach ($data as $k=>$v) {
        if (in_array($k, ['id', 'created_dt', 'updated_dt', 'createdDt', 'updatedDt'])) continue;
        if (!array_key_exists($k, static::$columns)) continue;
        $bean[$k] = $v;
      };
      $allowedColumns = array_merge([], array_keys(static::$columns));
      foreach (static::$hardConditions as $key => $val) {
        list($col, $comparator) = explode(' ', $key . ' =');
        if ((in_array($col, $allowedColumns))&&($comparator == '=')) {
          if (is_scalar($val)) {
            $bean[$col] = $val;
          }
        }
      };
    }

    /**
    * ApplyHardConditions
    *
    * @param array<mixed> $data
    * @return void
    */
    public static function ApplyHardConditions(array &$data) {
      $allowedColumns = array_merge([], array_keys(static::$columns));
      foreach (static::$hardConditions as $key => $val) {
        list($col, $comparator) = explode(' ', $key . ' =');
        if ((in_array($col, $allowedColumns))&&($comparator == '=')) {
          if (is_scalar($val)) {
            $data[$col] = $val;
          }
        }
      };
    }

    /**
    * setHardConditions
    *
    * @param array<mixed> $conditions
    * @return RRepo
    */
    function setHardConditions(array $conditions):RRepo {
      foreach ($conditions as $key => $val) {
        if (array_key_exists($key, static::$hardConditions)) static::$hardConditions[$key] = $val;
      }
      return $this;
    }

    function getHardConditions(): array {
      return static::$hardConditions;
    }

    /**
    * DefaultSorting
    *
    * @return array<mixed> $sorting
    */
    function DefaultSorting() {
      $allowedColumns = array_merge(['id'], array_keys(static::$columns), ['created_dt', 'updated_dt']);
      if (in_array('title', $allowedColumns)) return ['title' => 'ASC'];
      return [];
    }

    /**
    * Created
    *
    * @param array<mixed> $data
    * @param array<string> $unique
    * @param bool $update
    * @return OODBBean
    */
    public function Created(array $data, ?array $unique = null, bool $update = false):OODBBean {
        ob_start();
        if (!empty($unique)) {
          $uni = [];
          foreach ($unique as $k) {
            if (!is_string($k)) throw new \Exception('Only strings are allowed as identifiers of unique columns.');
            $uni[$k] = $data[$k];
          };
          $cond = $this->buildConditions($uni);
          $bean = R::find(static::$table, $cond->query, $cond->data);
          if (!empty($bean)) {
            ob_end_clean();
            if (count($bean) > 1) {
              throw new \Exception('There is more records, against uniqueness.');
            };
            $bean = array_pop($bean);
            $id = $bean->id;
            if ($update) $this->Update($id, $data);
            return $bean;
          };
        };
        $bean = R::dispense(static::$table);
        static::mergeData($bean, $data);
        $bean['created_dt'] = date('Y-m-d H:i:s');
        $id = R::store($bean);
        ob_end_clean();
        return $bean;
    }

    /**
    * Create
    *
    * @param array<mixed> $data
    * @param array<string> $unique
    * @param bool $update
    * @return int|string
    */
    public function Create(array $data, ?array $unique = null, bool $update = false):int|string {
      $bean = $this->Created($data, $unique, $update);
      return $bean->id;
    }

    /**
    * Retrieve
    *
    * @param int $id
    * @param array<mixed> $shared
    * @return ?\RedBeanPHP\OODBBean
    */
    public function Retrieve(int $id, array $shared = []):?\RedBeanPHP\OODBBean {
        ob_start();
        $bean = R::load(static::$table, $id);

        foreach ($shared as $val) {
          $param = "shared$val";
          $bean->$param;
        }

        ob_end_clean();
        //self::log();
        return $bean;
    }

    /**
    * Update
    *
    * @param int $id
    * @param array<mixed> $data
    * @return \RedBeanPHP\OODBBean
    */
    public function Update(int $id, array $data):\RedBeanPHP\OODBBean {
        ob_start();
        $bean = R::load(static::$table, $id);
        static::mergeData($bean, $data);
        $bean['updated_dt'] = date('Y-m-d H:i:s');
        R::store($bean);
        ob_end_clean();
        return $bean;
    }

    /**
    * Delete
    *
    * @param int $id
    * @return void
    */
    public function Delete(int $id):void {
        ob_start();
        $bean = R::load(static::$table, $id);
        R::trash($bean);
        ob_end_clean();
    }

    /**
    * buildConditions
    *
    * @param array $conditions
    * @param array $columns
    * @return string
    */
    public function buildConditions(array $conditions, array $columns = []) {
      return new RConditions($this, $conditions, $columns);
    }

    /**
    * Find
    *
    * @param array $conditions
    * @param array $columns
    * @return object|null
    */
    public function Find(array $conditions, array $columns = []): ?object {
      R::debug(true);
      $a = $this->buildConditions($conditions, $columns);
      ob_start();
      if ($a->columns === '*') {
        $rows = R::findOne(static::$table, $a->query, $a->data);
      } else {
        $rows = R::getAll('SELECT ' . $a->columns . ' FROM ' . static::$table . ' ' . $a->query, $a->data);
        if(!empty($rows)) {
          $rows = array_values($rows)[0];
          $rows = R::convertToBean(static::$table, $rows);
        }
      }
      if ((!empty($rows))&&(!empty(static::$shared))) {
        foreach (static::$shared as $shared) {
          $rows->$shared;
        }
      };
      ob_get_clean();
      if (empty($rows)) return null;
      //return $rows;
      //return $this->beansToArray($rows);
      $rows = $this->beansToModels($rows);
      return $rows;
    }

    /**
    * Listing
    *
    * @param array $conditions
    * @param array $columns
    * @param bool $bReturnArray
    * @return array
    */
    public function Listing(array $conditions, array $columns = [], bool $bReturnArray = false): array {
      R::debug(true);
      $a = $this->buildConditions($conditions, $columns);
      ob_start();
      if ($a->columns === '*') {
        $rows = R::findAll(static::$table, $a->query, $a->data);
      } else {
        $rows = R::getAll('SELECT ' . $a->columns . ' FROM ' . static::$table . ' ' . $a->query, $a->data);
        $rows = R::convertToBeans(static::$table, $rows);
      }
      if (!empty(static::$shared)) {
        foreach ($rows as $key => $val) {
          foreach (static::$shared as $shared) {
            $rows[$key]->$shared;
          }
        }
      };
      ob_get_clean();
      if (empty($rows)) return [];
      //return $rows;
      if ($bReturnArray) return $this->beansToArray($rows);
      return $this->beansToModels($rows);
    }

    /**
    * Collection
    *
    * @param array $conditions
    * @param array $columns
    * @return RCollection
    */
    public function Collection(array $conditions = [], array $columns = []): RCollection {
      R::debug(true);
      $a = $this->buildConditions($conditions, $columns);
      ob_start();
      if ($a->columns === '*') {
        $rows = R::findAll(static::$table, $a->query, $a->data);
      } else {
        $rows = R::getAll('SELECT ' . $a->columns . ' FROM ' . static::$table . ' ' . $a->query, $a->data);
        $rows = R::convertToBeans(static::$table, $rows);
      }
      if (!empty(static::$shared)) {
        foreach ($rows as $key => $val) {
          foreach (static::$shared as $shared) {
            $rows[$key]->$shared;
          }
        }
      };
      ob_get_clean();
      return new RCollection($rows, $this, $a);
    }

    /**
    * SimpleList
    *
    * @param string $column
    * @return array
    */
    function SimpleList(string $column = '') {
      if ($column) {
        if (!$this->hasColumn($column)) throw new \Exception('Wrong column.');
        $re = $this->Listing(['SORT' => [$column => 'ASC']], ['id', $column], true);
      } else if ($this->hasColumn('title')) {
        $re = $this->Listing(['SORT' => ['title' => 'ASC']], ['id', 'title'], true);
      } else if ($this->hasColumn('name')) {
        $re = $this->Listing(['SORT' => ['name' => 'ASC']], ['id', 'name'], true);
      } else if ($this->hasColumn('slug')) {
        $re = $this->Listing(['SORT' => ['slug' => 'ASC']], ['id', 'slug'], true);
      } else {
        $re = [];
      }
      return $re;
    }

    /**
    * Pagination
    *
    * @param array $conditions
    * @param array $data
    * @return array
    */
    public function Pagination(array $conditions, array $data = []):?array {
      R::debug(true);
      ob_start();
      $a = $this->buildConditions($conditions);
      $cnt = R::count(static::$table, $a->query, $a->data);
      ob_get_clean();
      $data = array_merge($data, [
        'count' => $cnt,
        'limit' => $this->limit,
        'offset' => $this->offset,
        //'action' => 'go/article',
      ]);
      return $data;
    }

    /**
    * Count
    *
    * @param array $conditions
    * @return int
    */
    public function Count(array $conditions = []):int {
      R::debug(true);
      ob_start();
      $a = $this->buildConditions($conditions);
      $cnt = R::count(static::$table, $a->countQuery, $a->data);
      ob_get_clean();
      return $cnt;
    }

    /**
    * Deletion
    *
    * @param array $conditions
    * @return array
    */
    public function Deletion(array $conditions):?array {
      R::debug(true);
      ob_start();
      $a = $this->buildConditions($conditions);
      $dt = R::findAll(static::$table, $a->query, $a->data);
      $cnt = 0;
      foreach ($dt as $bean) {
        R::trash($bean);
        $cnt++;
      };
      ob_get_clean();
      $data = [
        'count' => $cnt,
      ];
      return $data;
    }

    /**
    * beansToArray
    *
    * @param array|RedBeanPHP\OODBBean|RedBeanPHP\SimpleModel $dt
    * @return array
    */
    function beansToArray($dt):array {
      if (is_array($dt)) {
        $dt = array_values($dt);
        foreach ($dt as $k => $v) {
          if (is_a($v, 'RedBeanPHP\OODBBean')) {
            $dt[$k] = $v->export();
          } else if (is_subclass_of($v, 'RedBeanPHP\SimpleModel')) {
            $dt[$k] = $v->export();
          }
        }
      } else if (is_a($dt, 'RedBeanPHP\OODBBean')) {
        $dt = [$dt->export()];
      } else if (is_subclass_of($dt, 'RedBeanPHP\SimpleModel')) {
        $dt = [$dt->export()];
      }
      return $dt;
    }

    /**
    * beansToModels
    *
    * @param array|RedBeanPHP\OODBBean|RedBeanPHP\SimpleModel $dt
    * @return array
    */
    function beansToModels($dt) {
      if (is_null($dt)) return $dt;
      if (is_array($dt)) {
        $dt = array_values($dt);
        foreach ($dt as $k => $v) {
          $dt[$k] = $this->beansToModels($v);
        }
      } else if (is_a($dt, 'RedBeanPHP\OODBBean')) {
        $dt = $dt->box();
        if (is_null($dt)) return $dt;
        if (method_exists($dt, 'setRepo')) $dt->setRepo($this);
      } else if (is_subclass_of($dt, 'RedBeanPHP\SimpleModel')) {
        if (method_exists($dt, 'setRepo')) $dt->setRepo($this);
      }
      return $dt;
    }

    /**
    * modelsToBeans
    *
    * @param array|RedBeanPHP\OODBBean|RedBeanPHP\SimpleModel $dt
    * @return array
    */
    function modelsToBeans($dt) {
      if (is_array($dt)) {
        $dt = array_values($dt);
        foreach ($dt as $k => $v) {
          $dt[$k] = $this->modelsToBeans($v);
        }
      } else if (is_a($dt, 'RedBeanPHP\OODBBean')) {

      } else if (is_subclass_of($dt, 'RedBeanPHP\SimpleModel')) {
        $dt = $dt->unbox();
      }
      return $dt;
    }

    /**
    * CreateOrUpdate
    *
    * @param array|RedBeanPHP\OODBBean|RedBeanPHP\SimpleModel $dt
    * @return array
    */
    function CreateOrUpdate(array $data):int|string {
      if (intval($data['id'])==0) {
        return $this->Create($data);
      } else {
        return $this->Update(intval($data['id']), $data);
      }
    }

    /**
    * wipe
    *
    * @return void
    */
    public function Wipe():void {
        ob_start();
        R::wipe(static::$table);
        ob_end_clean();
    }

    /**
    * Options
    *
    * @return array
    */
    public function Options() {
      $reflector = new \ReflectionClass($this);
      $ns = $reflector->getNamespaceName();
      $columns = array_keys(static::$columns);
      $data = [];
      foreach ($columns as $key) {
        //if ($key === 'site_id') continue;
        $pos = strrpos($key, '_id');
        if ($pos>0) {
          $name = $ns . '\\' . ucfirst(substr($key, 0, $pos)) . 'Repo';
          //print_r($name);
          $repo = new $name();
          //if (method_exists($repo, 'setSite')) $repo->setSite($this->site);
          $data[$key] = $repo->SimpleList();
        }
      }
      return $data;
    }

    /**
    * Distinct
    *
    * @param string $column
    * @param array $conditions
    * @return array
    */
    public function Distinct(string $column, array $conditions = []): array {
      if (!$this->hasColumn($column)) throw new \Exception('Wrong column.');
      R::debug(true);
      ob_start();
      $a = $this->buildConditions($conditions);
      $rows = R::getAll('SELECT DISTINCT ' . $column . ' FROM ' . static::$table . ' ' . $a->query, $a->data);
      ob_end_clean();
      if (empty($rows)) return [];
      $re = [];
      foreach ($rows as $k=>$v) {
        $re[] = $v[$column];
      }
      return $re;
    }


    /**
    * get tagged
    *
    * @param int $idTag
    * @return array<mixed>

      function getTagged(int $idTag):array {
        R::debug(true);
        ob_start();
        $dt = R::getAll('SELECT L.* FROM links_tags LT LEFT JOIN links L ON LT.links_id = L.id WHERE LT.tags_id = ? AND L.url is not null order by L.text', [intval($idTag)] );
        ob_get_clean();
        return $dt;
      }
    */
    /**
    * get not tagged
    *
    * @return array<mixed>

      function getNotTagged():array {
        R::debug(true);
        ob_start();
        $dt = R::getAll('SELECT L.* FROM links L LEFT JOIN links_tags LT ON LT.links_id = L.id WHERE LT.tags_id is null order by L.text', [] );
        ob_get_clean();
        return $dt;
      }
    */

    /**
    * getLog
    *
    * @return array<mixed>
    */
    public function getLog():array {
      return self::$_logs;
    }

    /**
    * getName
    *
    * @return string
    */
    public function getName():string {
      return static::$table;
    }

    /**
    * getDeniedColumns
    *
    * @return array<mixed>
    */
    public function getDeniedColumns():array {
      return static::$deniedColumns;
    }

    /**
    * getColumns
    *
    * @return array<mixed>
    */
    public function getColumns(?array $which = null):array {
      if (empty($which)) return static::$columns;
      $re = [];
      foreach ($which as $key) {
        $re[$key] = static::$columns[$key];
      }
      return $re;
    }

    /**
    * exportColumnsFull
    *
    * @return array<mixed>
    */
    public function exportColumnsFull():array {
      R::debug(true);
      ob_start();
      $sql = 'SELECT * FROM information_schema.columns WHERE table_schema = \'public\' AND table_name   = \''.static::$table.'\' ORDER BY ordinal_position;';
      $re = R::getAll($sql);
      ob_end_clean();
      return $re;
    }

    /**
    * exportColumns
    *
    * @return array<mixed>
    */
    public function exportColumns(bool $bWithType = false):array {
      $rows = $this->exportColumnsFull();
      // column_name,data_type
      $columns = [];
      foreach ($rows as $row) {
        $columns[$row['column_name']] = $row['udt_name'];//$row['data_type'];
      };
      if ($bWithType) return $columns;
      return array_keys($columns);
    }

    /**
    * getDriverName
    *
    * @return string
    */
    protected function getDriverName():string {
      return R::getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
    * hasColumn
    *
    * @param string $column
    * @return bool
    */
    public function hasColumn(string $col):bool {
      if (isset(static::$columns[$col])) return true;
      return false;
    }

    /**
    * exportCSV
    *
    * @param string $fname
    * @return void
    */
    public function exportCSV(string $fname):void {
      $columns = $this->exportColumns();
      R::csv( '
        SELECT '.implode(',', $columns).' FROM ' .static::$table. ' ORDER BY id ASC',
        [],
        $columns,
        $fname,
        false
      );
    }

    /**
    * importCSV
    *
    * @param string $fname
    * @return void
    */
    public function importCSV(string $fname):void {
      //R::debug(true);
      //R::wipe(static::$table);
      try{
        R::exec( 'TRUNCATE ' . static::$table . ' CASCADE');
        R::exec( 'ALTER SEQUENCE ' . static::$table . '_id_seq RESTART WITH 1');
      } catch(\Throwable $e) {
        print_r($e->getMessage());
      }
      $row = 0;
      if (($handle = fopen($fname, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $row++;
          $num = count($data);
          if ($row == 1) {
            $keys = $data;
          } else {
            $insert = array_combine($keys, $data);
            //print_r($insert);
            $bean = R::dispense(static::$table);
            //foreach ($insert as $key => $val) if ($key!='id') $bean[$key] = $val;
            foreach ($insert as $key => $val) {
              if(in_array($key, ['id'])) {//,'lang_id','page_id'

              } else if ($val === '') {
                $val = null;
              } else {
                $bean[$key] = $val;
              }
            }
            //print_r($bean);
            try {
              R::store($bean);
            } catch(\Throwable $e) {
              print_r($e->getMessage());
            }
          }
        };
        fclose($handle);
      };
    }

    /**
    * slugify
    *
    * @param string $text
    * @param string $divider
    * @return string
    */
    public static function slugify(string $text, string $divider = '-'):string
    {
      // replace non letter or digits by divider
      $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);

      // trim
      $text = trim($text, $divider);

      // remove duplicate divider
      $text = preg_replace('~-+~', $divider, $text);

      // lowercase
      $text = strtolower($text);

      if (empty($text)) {
        return 'n-a';
      }

      return $text;
    }

}
