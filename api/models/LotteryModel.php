<?php
/**
 * 开奖信息模型
 */

namespace Models;

use Core\Model;
use Core\Database;

class LotteryModel extends Model
{
    protected $table = 'lotteries';
    protected $primaryKey = 'id';
    protected $fillable = [
        'type',
        'type_name',
        'issue',
        'red_balls',
        'blue_balls',
        'issue_date',
        'draw_time',
        'sales_amount',
        'jackpot_amount',
        'prize_info',
        'status',
    ];

    private $typeInfo = [
        'ssq' => ['name' => '双色球', 'color' => '#e60012'],
        'qcl' => ['name' => '七乐彩', 'color' => '#1890ff'],
        '22x5' => ['name' => '22选5', 'color' => '#52c41a'],
        '3d' => ['name' => '3D', 'color' => '#faad14'],
        'kl8' => ['name' => '快乐8', 'color' => '#722ed1'],
    ];

    public function getList($type = '', $page = 1, $pageSize = 10)
    {
        $query = $this->select('id, type, type_name, issue, red_balls, blue_balls, issue_date, sales_amount, jackpot_amount, prize_info, status');
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->orderBy('issue_date', 'DESC')
            ->orderBy('issue', 'DESC')
            ->paginate($page, $pageSize);
    }

    public function getLatest()
    {
        $db = Database::getInstance();
        
        $lotteries = [];
        
        foreach ($this->typeInfo as $type => $info) {
            $lottery = $this->select('id, type, type_name, issue, red_balls, blue_balls, issue_date, sales_amount, jackpot_amount, prize_info, status')
                ->where('type', $type)
                ->where('status', 1)
                ->orderBy('issue_date', 'DESC')
                ->orderBy('issue', 'DESC')
                ->first();
            
            if ($lottery) {
                $lottery['type_info'] = $info;
                $lottery['red_balls'] = json_decode($lottery['red_balls'], true) ?: [];
                $lottery['blue_balls'] = json_decode($lottery['blue_balls'], true) ?: [];
                $lottery['prize_info'] = json_decode($lottery['prize_info'], true) ?: null;
                $lotteries[] = $lottery;
            }
        }
        
        return $lotteries;
    }

    public function getById($id)
    {
        $lottery = $this->where('id', $id)->first();
        
        if ($lottery) {
            $lottery['type_info'] = $this->typeInfo[$lottery['type']] ?? ['name' => $lottery['type_name'], 'color' => '#e60012'];
            $lottery['red_balls'] = json_decode($lottery['red_balls'], true) ?: [];
            $lottery['blue_balls'] = json_decode($lottery['blue_balls'], true) ?: [];
            $lottery['prize_info'] = json_decode($lottery['prize_info'], true) ?: null;
        }
        
        return $lottery;
    }

    public function getByTypeAndIssue($type, $issue)
    {
        $lottery = $this->where('type', $type)
            ->where('issue', $issue)
            ->first();
        
        if ($lottery) {
            $lottery['type_info'] = $this->typeInfo[$lottery['type']] ?? ['name' => $lottery['type_name'], 'color' => '#e60012'];
            $lottery['red_balls'] = json_decode($lottery['red_balls'], true) ?: [];
            $lottery['blue_balls'] = json_decode($lottery['blue_balls'], true) ?: [];
            $lottery['prize_info'] = json_decode($lottery['prize_info'], true) ?: null;
        }
        
        return $lottery;
    }

    public function getHistory($type, $limit = 10)
    {
        $lotteries = $this->select('id, type, type_name, issue, red_balls, blue_balls, issue_date, sales_amount, jackpot_amount, prize_info, status')
            ->where('type', $type)
            ->where('status', 1)
            ->orderBy('issue_date', 'DESC')
            ->orderBy('issue', 'DESC')
            ->limit($limit)
            ->get();
        
        foreach ($lotteries as &$lottery) {
            $lottery['type_info'] = $this->typeInfo[$lottery['type']] ?? ['name' => $lottery['type_name'], 'color' => '#e60012'];
            $lottery['red_balls'] = json_decode($lottery['red_balls'], true) ?: [];
            $lottery['blue_balls'] = json_decode($lottery['blue_balls'], true) ?: [];
            $lottery['prize_info'] = json_decode($lottery['prize_info'], true) ?: null;
        }
        
        return $lotteries;
    }

    public function createLottery($data)
    {
        if (isset($data['red_balls']) && is_array($data['red_balls'])) {
            $data['red_balls'] = json_encode($data['red_balls'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['blue_balls']) && is_array($data['blue_balls'])) {
            $data['blue_balls'] = json_encode($data['blue_balls'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['prize_info']) && is_array($data['prize_info'])) {
            $data['prize_info'] = json_encode($data['prize_info'], JSON_UNESCAPED_UNICODE);
        }
        
        return $this->create($data);
    }

    public function updateLottery($id, $data)
    {
        if (isset($data['red_balls']) && is_array($data['red_balls'])) {
            $data['red_balls'] = json_encode($data['red_balls'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['blue_balls']) && is_array($data['blue_balls'])) {
            $data['blue_balls'] = json_encode($data['blue_balls'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['prize_info']) && is_array($data['prize_info'])) {
            $data['prize_info'] = json_encode($data['prize_info'], JSON_UNESCAPED_UNICODE);
        }
        
        return $this->update($id, $data);
    }
}
