<?php
/**
 * 模型基类
 */

namespace Core;

use Core\Database;
use PDO;

class Model
{
    protected $table = '';
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $timestamps = true;

    protected $db;
    protected $query = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function select($columns = ['*'])
    {
        $this->query['select'] = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if (!isset($this->query['where'])) {
            $this->query['where'] = [];
            $this->query['whereParams'] = [];
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $placeholder = ':' . uniqid('where_');
        $this->query['where'][] = "{$column} {$operator} {$placeholder}";
        $this->query['whereParams'][$placeholder] = $value;

        return $this;
    }

    public function whereIn($column, $values)
    {
        if (!isset($this->query['where'])) {
            $this->query['where'] = [];
            $this->query['whereParams'] = [];
        }

        $placeholders = [];
        foreach ($values as $index => $value) {
            $placeholder = ':where_in_' . uniqid() . '_' . $index;
            $placeholders[] = $placeholder;
            $this->query['whereParams'][$placeholder] = $value;
        }

        $this->query['where'][] = "{$column} IN (" . implode(', ', $placeholders) . ")";

        return $this;
    }

    public function whereRaw($sql, $params = [])
    {
        if (!isset($this->query['where'])) {
            $this->query['where'] = [];
            $this->query['whereParams'] = [];
        }

        $this->query['where'][] = $sql;
        foreach ($params as $key => $value) {
            $this->query['whereParams'][$key] = $value;
        }

        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        if (!isset($this->query['orderBy'])) {
            $this->query['orderBy'] = [];
        }

        $direction = strtoupper($direction);
        $direction = in_array($direction, ['ASC', 'DESC']) ? $direction : 'ASC';
        $this->query['orderBy'][] = "{$column} {$direction}";

        return $this;
    }

    public function limit($limit, $offset = 0)
    {
        $this->query['limit'] = (int)$limit;
        $this->query['offset'] = (int)$offset;

        return $this;
    }

    public function paginate($page = 1, $pageSize = 10)
    {
        $page = max(1, (int)$page);
        $pageSize = max(1, min(100, (int)$pageSize));

        $offset = ($page - 1) * $pageSize;

        $total = $this->count();
        $this->limit($pageSize, $offset);
        $items = $this->get();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => (int)ceil($total / $pageSize),
        ];
    }

    public function get()
    {
        $sql = $this->buildSelectSql();
        $params = $this->query['whereParams'] ?? [];

        $this->resetQuery();

        return $this->db->fetchAll($sql, $params);
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return $results ? $results[0] : null;
    }

    public function find($id)
    {
        return $this->where($this->primaryKey, $id)->first();
    }

    public function count()
    {
        $select = $this->query['select'] ?? '*';
        $this->query['select'] = 'COUNT(*)';

        $sql = $this->buildSelectSql();
        $params = $this->query['whereParams'] ?? [];

        $this->resetQuery();
        $this->query['select'] = $select;

        return (int)$this->db->fetchColumn($sql, $params);
    }

    public function create($data)
    {
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->update($this->table, $data, "{$this->primaryKey} = :id", [':id' => $id]);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, "{$this->primaryKey} = :id", [':id' => $id]);
    }

    private function buildSelectSql()
    {
        $select = $this->query['select'] ?? '*';
        $sql = "SELECT {$select} FROM {$this->table}";

        if (!empty($this->query['where'])) {
            $sql .= " WHERE " . implode(' AND ', $this->query['where']);
        }

        if (!empty($this->query['orderBy'])) {
            $sql .= " ORDER BY " . implode(', ', $this->query['orderBy']);
        }

        if (isset($this->query['limit'])) {
            $sql .= " LIMIT " . $this->query['limit'];
            if (isset($this->query['offset'])) {
                $sql .= " OFFSET " . $this->query['offset'];
            }
        }

        return $sql;
    }

    private function resetQuery()
    {
        $this->query = [];
    }
}
