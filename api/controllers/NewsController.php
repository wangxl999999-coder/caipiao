<?php
/**
 * 新闻资讯控制器
 */

namespace Controllers;

use Core\Controller;
use Models\NewsModel;
use Exception;

class NewsController extends Controller
{
    protected $newsModel;

    public function __construct()
    {
        parent::__construct();
        $this->newsModel = new NewsModel();
    }

    public function getList()
    {
        try {
            $type = $this->get('type', '');
            $page = (int)$this->get('page', 1);
            $pageSize = (int)$this->get('pageSize', 10);
            
            $result = $this->newsModel->getList($type, $page, $pageSize);
            
            return $this->success($result['items']);
        } catch (Exception $e) {
            return $this->error('获取新闻列表失败: ' . $e->getMessage(), 500);
        }
    }

    public function getBanner()
    {
        try {
            $banners = $this->newsModel->getBanners();
            
            return $this->success($banners);
        } catch (Exception $e) {
            return $this->error('获取轮播图失败: ' . $e->getMessage(), 500);
        }
    }

    public function getDetail($id)
    {
        try {
            $news = $this->newsModel->getById($id);
            
            if (!$news) {
                return $this->notFound('新闻资讯不存在');
            }
            
            return $this->success($news);
        } catch (Exception $e) {
            return $this->error('获取新闻详情失败: ' . $e->getMessage(), 500);
        }
    }
}
