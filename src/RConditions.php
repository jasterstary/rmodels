<?php

namespace JasterStary\RModels;

use \RedBeanPHP\R as R;

class RConditions {

  private RRepo $repo;

    /**
    * buildColumns
    *
    * @param array $columns
    * @return string
    */
    protected function buildColumns(array $columns = []): string {
      if ((empty($columns))&&(empty($this->repo->getDeniedColumns()))) return '*';
      $deniedColumns = $this->repo->getDeniedColumns();
      $allowedColumns = array_merge(['id'], array_keys($this->repo->getColumns()), ['created_dt', 'updated_dt']);
      if (empty($columns)) $columns = $allowedColumns;
      if (!in_array('id', $columns)) $columns[] = 'id';
      $response = [];
      foreach ($columns as $col) {
        if (!in_array($col, $allowedColumns)) throw new \Exception('Wrong column.');
        if (!in_array($col, $deniedColumns)) $response[$col] = $col;
      }
      return implode(', ', array_keys($response));
    }

    /**
    * buildSorts
    *
    * @param array $columns
    * @return array
    */
    protected function buildSorts(array $columns = []): array {
      $deniedColumns = $this->repo->getDeniedColumns();
      if ((empty($columns))&&(empty($deniedColumns))) return [];
      $response = [];
      $allowedColumns = array_merge(['id'], array_keys($this->repo->getColumns()));
      foreach ($columns as $key => $val) {
        if (!in_array($key, $allowedColumns)) throw new \Exception('Wrong sorter.'.$key);
        if (!in_array($val, ['ASC', 'DESC'])) throw new \Exception('Wrong sorter.'.$val);
        if (!in_array($key, $deniedColumns)) $response[$key] = $key . ' ' . $val;
      }
      return array_values($response);
    }

    protected function readFunctions(string &$col) {
        list($col, $funcs) = explode('|', $col . '|', 2);
        if (!$funcs) return [];
        $funcs = explode('|', $funcs);
        foreach ($funcs as $func) {
          if ($func &&(!in_array($func, [
            'MAX', 'MIN', 'SUM', 'COUNT',
            'ABS', 'CBRT', 'CEIL', 'CEILING', 'DEGREES', 'EXP',
            'FACTORIAL', 'FLOOR', 'GAMMA', 'LGAMMA', 'LN',
            'LOG', 'LOG10', 'MIN_SCALE', 'RADIANS', 'ROUND',
            'SCALE', 'SIGN', 'SQRT', 'TRIM_SCALE', 'TRUNC',
            'LOWER', 'UPPER','TRIM','REVERSE',
            'DATE', 'TIME', 'AGE',
            'SECOND', 'MINUTE', 'HOUR', 'DAY', 'MONTH', 'YEAR']))) {
            throw new \Exception('Wrong function.');
          };
        }
        return $funcs;
    }

    protected function buildFunctions(string $col, array $funcs):string {
      if ($funcs) {
        foreach ($funcs as $func) if ($func) $col = $func.'('.$col.')';
      };
      return $col;
    }

    protected function buildCondition(&$cond, &$data, $key, $val, $allowedColumns) {
        list($col, $comparator) = explode(' ', $key . ' =');
        $funcs = $this->readFunctions($col);
        if ($comparator == 'NOT_IN') $comparator = 'NOT IN';
        if (!in_array($comparator, ['=', '<', '<=', '>', '>=', '<>', 'IN','NOT IN', 'LIKE', 'NOT'])) {
          throw new \Exception('Wrong comparator.');
        };
        if (in_array($col, $allowedColumns)) {
          if (is_array($val)) {
            if (!in_array($comparator, ['=', 'IN', 'NOT'])) {
              throw new \Exception('Wrong comparator.');
            };
            $col = $this->buildFunctions($col, $funcs);
            $cond[] = $col . ' ' . $comparator . ' (' . R::genSlots( $val ) . ')';
            foreach ($val as $ID) {
              if (is_scalar($ID)) array_push($data, $ID);
              //array_push($data, intval($ID));
            };
          } elseif (is_null($val)) {
            if ($comparator == 'NOT') {
              $cond[] = $col . ' IS NOT NULL ';
            } else {
              $cond[] = $col . ' IS NULL ';
            }
          } else {
            $col = $this->buildFunctions($col, $funcs);
            $cond[] = $col . ' ' . $comparator . ' ? ';
            array_push($data, $val);
          }
        }
    }

    /**
    * buildConditions
    *
    * @param array $conditions
    * @param array $columns
    * @return string
    */
    public function buildConditions(array $conditions, array $columns = []) {
      $cond = [];$data = [];$lims = [];$sorts = [];
      $allowedColumns = array_merge(['id', 'created_dt', 'updated_dt'], array_keys($this->repo->getColumns()));
      foreach ($conditions as $key => $val) {
        if (in_array($key, ['LIMIT', 'OFFSET'])) {
           $lims[$key] = intval($val);
        } else if (in_array($key, ['SORT'])) {
           $sorts = $this->buildSorts($val);
        } else {
           $this->buildCondition($cond, $data, $key, $val, $allowedColumns);
        };
      };
      foreach ($this->repo->getHardConditions() as $key => $val) {
        $this->buildCondition($cond, $data, $key, $val, $allowedColumns);
      };
      $q = '';
      if (!empty($cond)) $q.='WHERE ' . implode(' AND ', $cond);
      $countQuery = $q;
      if (!empty($sorts)) $q.=' ORDER BY ' . implode(' ', $sorts);
      if (!empty($lims)) {
        foreach ($lims as $k => $v) {
          $q.=' ' . $k . ' ' . $v;
        }
       }

      $response = new \stdClass();
      $response->columns = $this->buildColumns($columns);
      $response->query = $q;
      $response->countQuery = $countQuery;
      $response->data = $data;
      $response->table = $this->repo->getName();
      $response->limit = 0;
      $response->offset = 0;
      if (isset($lims['LIMIT'])) $response->limit = $lims['LIMIT'];
      if (isset($lims['OFFSET'])) $response->offset = $lims['OFFSET'];

      return $response;
    }

    function __construct(RRepo $repo, array $conditions, array $columns = []) {
      $this->repo = $repo;
      $this->response = $this->buildConditions($conditions, $columns);
    }

    public function __get(string $key) {
      if (in_array($key, [
        'columns', 'query', 'countQuery', 'data',
        'table', 'limit', 'offset'
      ])) {
        return $this->response->$key;
      }
      return null;
    }

}
