<?php
/**
 * 规则控制器
 */

namespace Controllers;

use Core\Controller;
use Models\RuleModel;
use Exception;

class RuleController extends Controller
{
    protected $ruleModel;

    public function __construct()
    {
        parent::__construct();
        $this->ruleModel = new RuleModel();
    }

    public function getList()
    {
        try {
            $rules = $this->ruleModel->getList();
            
            return $this->success($rules);
        } catch (Exception $e) {
            return $this->error('获取规则列表失败: ' . $e->getMessage(), 500);
        }
    }

    public function getDetail($type)
    {
        try {
            $rule = $this->ruleModel->getByType($type);
            
            if (!$rule) {
                return $this->notFound('规则信息不存在');
            }
            
            return $this->success($rule);
        } catch (Exception $e) {
            return $this->error('获取规则详情失败: ' . $e->getMessage(), 500);
        }
    }
}
