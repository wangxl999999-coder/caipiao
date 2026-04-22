<?php
/**
 * 用户模型
 */

namespace Models;

use Core\Model;
use Core\Database;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'openid',
        'unionid',
        'phone',
        'nickname',
        'avatar',
        'gender',
        'city',
        'province',
        'country',
        'status',
    ];

    public function findByOpenid($openid)
    {
        return $this->where('openid', $openid)->first();
    }

    public function findByPhone($phone)
    {
        return $this->where('phone', $phone)->first();
    }

    public function getList($page = 1, $pageSize = 10, $keyword = '')
    {
        $query = $this->select('id, phone, nickname, avatar, gender, status, last_login_time, created_at');
        
        if ($keyword) {
            $query->whereRaw('(phone LIKE :keyword OR nickname LIKE :keyword)', [
                ':keyword' => "%{$keyword}%"
            ]);
        }
        
        return $query->orderBy('id', 'DESC')->paginate($page, $pageSize);
    }

    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    public function updateLoginInfo($id, $ip = '')
    {
        $data = [
            'last_login_time' => date('Y-m-d H:i:s'),
        ];
        
        if ($ip) {
            $data['last_login_ip'] = $ip;
        }
        
        return $this->update($id, $data);
    }

    public function getById($id)
    {
        return $this->select('id, phone, nickname, avatar, gender, city, province, country, status, last_login_time, created_at')
            ->where('id', $id)
            ->first();
    }
}
