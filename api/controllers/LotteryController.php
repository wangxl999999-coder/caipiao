<?php
/**
 * 开奖信息控制器
 */

namespace Controllers;

use Core\Controller;
use Models\LotteryModel;
use Exception;

class LotteryController extends Controller
{
    protected $lotteryModel;

    public function __construct()
    {
        parent::__construct();
        $this->lotteryModel = new LotteryModel();
    }

    public function getList()
    {
        try {
            $type = $this->get('type', '');
            $page = (int)$this->get('page', 1);
            $pageSize = (int)$this->get('pageSize', 10);
            
            $result = $this->lotteryModel->getList($type, $page, $pageSize);
            
            foreach ($result['items'] as &$item) {
                $item['red_balls'] = json_decode($item['red_balls'], true) ?: [];
                $item['blue_balls'] = json_decode($item['blue_balls'], true) ?: [];
                $item['prize_info'] = json_decode($item['prize_info'], true) ?: null;
            }
            
            return $this->success($result['items']);
        } catch (Exception $e) {
            return $this->error('获取开奖列表失败: ' . $e->getMessage(), 500);
        }
    }

    public function getLatest()
    {
        try {
            $lotteries = $this->lotteryModel->getLatest();
            
            return $this->success($lotteries);
        } catch (Exception $e) {
            return $this->error('获取最新开奖失败: ' . $e->getMessage(), 500);
        }
    }

    public function getDetail($id)
    {
        try {
            $lottery = $this->lotteryModel->getById($id);
            
            if (!$lottery) {
                return $this->notFound('开奖信息不存在');
            }
            
            return $this->success($lottery);
        } catch (Exception $e) {
            return $this->error('获取开奖详情失败: ' . $e->getMessage(), 500);
        }
    }

    public function getByTypeAndIssue()
    {
        try {
            $type = $this->get('type');
            $issue = $this->get('issue');
            
            if (!$type || !$issue) {
                return $this->error('请提供彩种类型和期号', 400);
            }
            
            $lottery = $this->lotteryModel->getByTypeAndIssue($type, $issue);
            
            if (!$lottery) {
                return $this->notFound('开奖信息不存在');
            }
            
            return $this->success($lottery);
        } catch (Exception $e) {
            return $this->error('查询开奖信息失败: ' . $e->getMessage(), 500);
        }
    }
}
