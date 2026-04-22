<?php
/**
 * 聊天控制器
 */

namespace Controllers;

use Core\Controller;
use Models\ChatModel;
use Models\SettingModel;
use Exception;

class ChatController extends Controller
{
    protected $chatModel;
    protected $settingModel;

    public function __construct()
    {
        parent::__construct();
        $this->chatModel = new ChatModel();
        $this->settingModel = new SettingModel();
    }

    public function getHistory()
    {
        try {
            global $current_user;
            
            if (!$current_user) {
                return $this->unauthorized();
            }
            
            $page = (int)$this->get('page', 1);
            $pageSize = (int)$this->get('pageSize', 20);
            
            $result = $this->chatModel->getHistory($current_user['id'], $page, $pageSize);
            
            return $this->success($result['items']);
        } catch (Exception $e) {
            return $this->error('获取聊天记录失败: ' . $e->getMessage(), 500);
        }
    }

    public function sendMessage()
    {
        try {
            global $current_user;
            
            if (!$current_user) {
                return $this->unauthorized();
            }
            
            $content = $this->post('content');
            $type = $this->post('type', 'text');
            
            if (!$content) {
                return $this->error('消息内容不能为空', 400);
            }
            
            $messageId = $this->chatModel->sendUserMessage(
                $current_user['id'],
                $content,
                $type
            );
            
            $reply = $this->generateAutoReply($content, $current_user);
            
            $serviceConfig = $this->settingModel->getCustomerService();
            $welcomeMsg = $serviceConfig['welcome_msg'] ?? '感谢您的咨询，客服人员会尽快回复您。';
            
            $replyContent = $reply ?: $welcomeMsg;
            
            $this->chatModel->sendServiceMessage(
                $current_user['id'],
                $replyContent,
                'text'
            );
            
            return $this->success([
                'message_id' => $messageId,
                'reply' => $replyContent,
            ], '发送成功');
        } catch (Exception $e) {
            return $this->error('发送消息失败: ' . $e->getMessage(), 500);
        }
    }

    private function generateAutoReply($content, $user)
    {
        $content = mb_strtolower($content, 'UTF-8');
        
        $keywords = [
            '开奖' => '您可以在首页或开奖页面查看最新的开奖信息，支持双色球、七乐彩、22选5、3D、快乐8等多种彩种。',
            '中奖' => '恭喜您！如果您想了解中奖信息，可以在资讯页面查看最新的中奖公告。',
            '规则' => '您可以在"游戏规则"页面查看各彩种的详细规则说明，包括开奖时间、投注规则、奖级说明等。',
            '站点' => '您可以在"附近站点"页面查看附近的彩票销售站点，支持导航和电话联系。',
            '登录' => '请点击"我的"页面进行登录，支持手机号登录和微信一键登录。',
            '注册' => '您可以在登录页面选择注册，使用手机号即可完成注册。',
            '客服' => '您好，我是智能客服，请问有什么可以帮助您的？如果您需要人工客服，可以在工作时间（9:00-18:00）拨打客服电话：400-123-4567。',
            '双色球' => '双色球每周二、四、日 21:15开奖，从01-33中选6个红球，从01-16中选1个蓝球。',
            '七乐彩' => '七乐彩每周一、三、五 21:15开奖，从01-30中选7个号码。',
            '3d' => '3D每日开奖，从000-999中选择一个3位数进行投注。',
            '快乐8' => '快乐8每日开奖，从01-80中选择1-10个号码进行投注。',
            '22选5' => '22选5每日开奖，从01-22中选5个号码进行投注。',
            '你好' => '您好！欢迎使用福彩助手在线客服，请问有什么可以帮助您的？',
            'hello' => '您好！欢迎使用福彩助手在线客服，请问有什么可以帮助您的？',
            'hi' => '您好！欢迎使用福彩助手在线客服，请问有什么可以帮助您的？',
            '谢谢' => '不客气！如果您还有其他问题，欢迎随时咨询。祝您生活愉快！',
            '感谢' => '不客气！如果您还有其他问题，欢迎随时咨询。祝您生活愉快！',
        ];
        
        foreach ($keywords as $keyword => $reply) {
            if (mb_strpos($content, $keyword) !== false) {
                return $reply;
            }
        }
        
        return null;
    }
}
