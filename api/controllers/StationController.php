<?php
/**
 * 站点控制器
 */

namespace Controllers;

use Core\Controller;
use Models\StationModel;
use Exception;

class StationController extends Controller
{
    protected $stationModel;

    public function __construct()
    {
        parent::__construct();
        $this->stationModel = new StationModel();
    }

    public function getList()
    {
        try {
            $latitude = $this->get('latitude');
            $longitude = $this->get('longitude');
            $city = $this->get('city', '');
            $page = (int)$this->get('page', 1);
            $pageSize = (int)$this->get('pageSize', 10);
            
            $lat = $latitude !== null ? (float)$latitude : null;
            $lng = $longitude !== null ? (float)$longitude : null;
            
            $result = $this->stationModel->getList($lat, $lng, $page, $pageSize, $city);
            
            return $this->success($result['items']);
        } catch (Exception $e) {
            return $this->error('获取站点列表失败: ' . $e->getMessage(), 500);
        }
    }

    public function getDetail($id)
    {
        try {
            $station = $this->stationModel->getById($id);
            
            if (!$station) {
                return $this->notFound('站点信息不存在');
            }
            
            return $this->success($station);
        } catch (Exception $e) {
            return $this->error('获取站点详情失败: ' . $e->getMessage(), 500);
        }
    }
}
