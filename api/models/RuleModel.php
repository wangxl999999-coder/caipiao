<?php
/**
 * 规则模型
 */

namespace Models;

use Core\Model;
use Core\Database;

class RuleModel extends Model
{
    protected $table = 'rules';
    protected $primaryKey = 'id';
    protected $fillable = [
        'type',
        'name',
        'draw_time',
        'draw_channel',
        'rules',
        'prize_description',
        'bet_rules',
        'sort',
        'status',
    ];

    private $typeInfo = [
        'ssq' => ['name' => '双色球', 'color' => '#e60012'],
        'qcl' => ['name' => '七乐彩', 'color' => '#1890ff'],
        '22x5' => ['name' => '22选5', 'color' => '#52c41a'],
        '3d' => ['name' => '3D', 'color' => '#faad14'],
        'kl8' => ['name' => '快乐8', 'color' => '#722ed1'],
    ];

    public function getList()
    {
        $rules = $this->select('id, type, name, draw_time, draw_channel, rules, prize_description, bet_rules, sort, status, created_at')
            ->where('status', 1)
            ->orderBy('sort', 'ASC')
            ->get();
        
        foreach ($rules as &$rule) {
            $rule['type_info'] = $this->typeInfo[$rule['type']] ?? ['name' => $rule['name'], 'color' => '#e60012'];
        }
        
        return $rules;
    }

    public function getByType($type)
    {
        $rule = $this->where('type', $type)
            ->where('status', 1)
            ->first();
        
        if ($rule) {
            $rule['type_info'] = $this->typeInfo[$rule['type']] ?? ['name' => $rule['name'], 'color' => '#e60012'];
        }
        
        return $rule;
    }

    public function getById($id)
    {
        $rule = $this->where('id', $id)->first();
        
        if ($rule) {
            $rule['type_info'] = $this->typeInfo[$rule['type']] ?? ['name' => $rule['name'], 'color' => '#e60012'];
        }
        
        return $rule;
    }

    public function createRule($data)
    {
        if (!isset($data['sort'])) {
            $maxSort = $this->fetchColumn(
                "SELECT MAX(sort) FROM {$this->table}"
            );
            $data['sort'] = ($maxSort ?: 0) + 1;
        }
        
        return $this->create($data);
    }

    public function updateRule($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteRule($id)
    {
        return $this->update($id, ['status' => 0]);
    }

    public function getByTypeList($types)
    {
        $rules = $this->select('id, type, name, draw_time, draw_channel, rules, prize_description, bet_rules, sort, status, created_at')
            ->whereIn('type', $types)
            ->where('status', 1)
            ->orderBy('sort', 'ASC')
            ->get();
        
        foreach ($rules as &$rule) {
            $rule['type_info'] = $this->typeInfo[$rule['type']] ?? ['name' => $rule['name'], 'color' => '#e60012'];
        }
        
        return $rules;
    }
}
