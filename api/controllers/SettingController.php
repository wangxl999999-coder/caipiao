<?php
/**
 * 系统配置控制器
 */

namespace Controllers;

use Core\Controller;
use Models\SettingModel;
use Exception;

class SettingController extends Controller
{
    protected $settingModel;

    public function __construct()
    {
        parent::__construct();
        $this->settingModel = new SettingModel();
    }

    public function getAboutUs()
    {
        try {
            $about = $this->settingModel->getAboutUs();
            
            return $this->success($about);
        } catch (Exception $e) {
            return $this->error('获取关于我们失败: ' . $e->getMessage(), 500);
        }
    }

    public function getUserAgreement()
    {
        try {
            $agreement = $this->settingModel->getUserAgreement();
            
            return $this->success($agreement);
        } catch (Exception $e) {
            return $this->error('获取用户协议失败: ' . $e->getMessage(), 500);
        }
    }

    public function getPrivacyPolicy()
    {
        try {
            $policy = $this->settingModel->getPrivacyPolicy();
            
            return $this->success($policy);
        } catch (Exception $e) {
            return $this->error('获取隐私政策失败: ' . $e->getMessage(), 500);
        }
    }

    public function getCustomerService()
    {
        try {
            $service = $this->settingModel->getCustomerService();
            
            return $this->success($service);
        } catch (Exception $e) {
            return $this->error('获取客服配置失败: ' . $e->getMessage(), 500);
        }
    }
}
