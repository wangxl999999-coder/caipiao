<?php
/**
 * 系统配置模型
 */

namespace Models;

use Core\Model;
use Core\Database;

class SettingModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'key',
        'value',
        'name',
        'description',
        'group',
        'sort',
        'status',
    ];

    public function getByKey($key)
    {
        $setting = $this->where('key', $key)
            ->where('status', 1)
            ->first();
        
        if ($setting) {
            $value = json_decode($setting['value'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $setting['value'] = $value;
            }
        }
        
        return $setting;
    }

    public function getValueByKey($key, $default = null)
    {
        $setting = $this->getByKey($key);
        if ($setting) {
            return $setting['value'];
        }
        return $default;
    }

    public function getByGroup($group)
    {
        $settings = $this->select('id, key, value, name, description, group, sort, status, created_at')
            ->where('group', $group)
            ->where('status', 1)
            ->orderBy('sort', 'ASC')
            ->get();
        
        foreach ($settings as &$setting) {
            $value = json_decode($setting['value'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $setting['value'] = $value;
            }
        }
        
        return $settings;
    }

    public function getAll()
    {
        $settings = $this->select('id, key, value, name, description, group, sort, status, created_at')
            ->where('status', 1)
            ->orderBy('sort', 'ASC')
            ->get();
        
        $result = [];
        foreach ($settings as $setting) {
            $value = json_decode($setting['value'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $setting['value'] = $value;
            }
            $result[$setting['key']] = $setting;
        }
        
        return $result;
    }

    public function setByKey($key, $value, $name = '', $description = '', $group = 'basic')
    {
        $exists = $this->where('key', $key)->first();
        
        $data = [
            'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value,
        ];
        
        if ($name) {
            $data['name'] = $name;
        }
        if ($description) {
            $data['description'] = $description;
        }
        if ($group) {
            $data['group'] = $group;
        }
        
        if ($exists) {
            return $this->update($exists['id'], $data);
        } else {
            $data['key'] = $key;
            $data['name'] = $name ?: $key;
            $data['group'] = $group;
            return $this->create($data);
        }
    }

    public function getAboutUs()
    {
        return $this->getValueByKey('about_us', [
            'content' => '<p>福彩助手是一款专业的福彩查询应用，为您提供最新、最准确的福彩开奖信息查询服务。</p>'
        ]);
    }

    public function getUserAgreement()
    {
        return $this->getValueByKey('user_agreement', [
            'content' => '<p>欢迎您使用福彩助手！</p>'
        ]);
    }

    public function getPrivacyPolicy()
    {
        return $this->getValueByKey('privacy_policy', [
            'content' => '<p>保护用户隐私是本应用的一项基本政策。</p>'
        ]);
    }

    public function getCustomerService()
    {
        return $this->getValueByKey('customer_service', [
            'phone' => '400-123-4567',
            'work_time' => '9:00-18:00',
            'qq' => '123456789',
            'welcome_msg' => '您好！欢迎使用福彩助手在线客服，请问有什么可以帮助您的？'
        ]);
    }

    public function updateSetting($id, $data)
    {
        if (isset($data['value']) && is_array($data['value'])) {
            $data['value'] = json_encode($data['value'], JSON_UNESCAPED_UNICODE);
        }
        
        return $this->update($id, $data);
    }

    public function createSetting($data)
    {
        if (isset($data['value']) && is_array($data['value'])) {
            $data['value'] = json_encode($data['value'], JSON_UNESCAPED_UNICODE);
        }
        
        if (!isset($data['sort'])) {
            $maxSort = $this->fetchColumn(
                "SELECT MAX(sort) FROM {$this->table} WHERE `group` = :group",
                [':group' => $data['group'] ?? 'basic']
            );
            $data['sort'] = ($maxSort ?: 0) + 1;
        }
        
        return $this->create($data);
    }
}
