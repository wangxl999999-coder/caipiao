<?php
/**
 * 站点模型
 */

namespace Models;

use Core\Model;
use Core\Database;

class StationModel extends Model
{
    protected $table = 'stations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code',
        'name',
        'address',
        'province',
        'city',
        'district',
        'latitude',
        'longitude',
        'phone',
        'business_hours',
        'description',
        'sort',
        'status',
    ];

    public function getList($latitude = null, $longitude = null, $page = 1, $pageSize = 10, $city = '')
    {
        $db = Database::getInstance();
        
        if ($latitude !== null && $longitude !== null) {
            $sql = "SELECT *, 
                    (6371 * acos(cos(radians(:lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:lng)) + sin(radians(:lat)) * sin(radians(latitude)))) AS distance
                    FROM {$this->table} 
                    WHERE status = 1";
            
            $params = [
                ':lat' => $latitude,
                ':lng' => $longitude,
            ];
            
            if ($city) {
                $sql .= " AND city = :city";
                $params[':city'] = $city;
            }
            
            $sql .= " ORDER BY distance ASC 
                      LIMIT :limit OFFSET :offset";
            
            $params[':limit'] = (int)$pageSize;
            $params[':offset'] = (int)(($page - 1) * $pageSize);
            
            $stations = $db->fetchAll($sql, $params);
            
            $countSql = "SELECT COUNT(*) FROM {$this->table} WHERE status = 1";
            $countParams = [];
            if ($city) {
                $countSql .= " AND city = :city";
                $countParams[':city'] = $city;
            }
            $total = $db->fetchColumn($countSql, $countParams);
            
            return [
                'items' => $stations,
                'total' => $total,
                'page' => $page,
                'pageSize' => $pageSize,
                'totalPages' => (int)ceil($total / $pageSize),
            ];
        }
        
        $query = $this->select('id, code, name, address, province, city, district, latitude, longitude, phone, business_hours, description, sort, status, created_at')
            ->where('status', 1);
        
        if ($city) {
            $query->where('city', $city);
        }
        
        return $query->orderBy('sort', 'ASC')
            ->orderBy('id', 'DESC')
            ->paginate($page, $pageSize);
    }

    public function getById($id)
    {
        return $this->where('id', $id)
            ->where('status', 1)
            ->first();
    }

    public function createStation($data)
    {
        if (!isset($data['sort'])) {
            $maxSort = $this->fetchColumn(
                "SELECT MAX(sort) FROM {$this->table}"
            );
            $data['sort'] = ($maxSort ?: 0) + 1;
        }
        
        return $this->create($data);
    }

    public function updateStation($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteStation($id)
    {
        return $this->update($id, ['status' => 0]);
    }

    public function getByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    public function search($keyword, $page = 1, $pageSize = 10)
    {
        return $this->select('id, code, name, address, province, city, district, latitude, longitude, phone, business_hours, description, sort, status, created_at')
            ->where('status', 1)
            ->whereRaw('(name LIKE :keyword OR code LIKE :keyword OR address LIKE :keyword)', [
                ':keyword' => "%{$keyword}%"
            ])
            ->orderBy('sort', 'ASC')
            ->orderBy('id', 'DESC')
            ->paginate($page, $pageSize);
    }
}
