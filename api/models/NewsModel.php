<?php
/**
 * 新闻资讯模型
 */

namespace Models;

use Core\Model;
use Core\Database;

class NewsModel extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'id';
    protected $fillable = [
        'type',
        'title',
        'summary',
        'content',
        'cover_image',
        'source',
        'author',
        'is_banner',
        'is_top',
        'view_count',
        'sort',
        'status',
        'publish_time',
    ];

    private $typeLabels = [
        1 => '新闻资讯',
        2 => '中奖信息',
    ];

    public function getList($type = '', $page = 1, $pageSize = 10)
    {
        $query = $this->select('id, type, title, summary, cover_image, source, author, is_banner, is_top, view_count, sort, status, publish_time, created_at')
            ->where('status', 1);
        
        if ($type !== '') {
            $query->where('type', (int)$type);
        }
        
        return $query->orderBy('is_top', 'DESC')
            ->orderBy('publish_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate($page, $pageSize);
    }

    public function getBanners()
    {
        return $this->select('id, type, title, summary, cover_image, source, author, is_banner, is_top, view_count, publish_time')
            ->where('status', 1)
            ->where('is_banner', 1)
            ->orderBy('sort', 'ASC')
            ->orderBy('publish_time', 'DESC')
            ->limit(5)
            ->get();
    }

    public function getById($id)
    {
        $news = $this->where('id', $id)
            ->where('status', 1)
            ->first();
        
        if ($news) {
            $news['type_label'] = $this->typeLabels[$news['type']] ?? '未知';
            
            $this->updateViewCount($id);
        }
        
        return $news;
    }

    public function updateViewCount($id)
    {
        $db = Database::getInstance();
        return $db->query(
            "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = :id",
            [':id' => $id]
        );
    }

    public function createNews($data)
    {
        if (!isset($data['publish_time'])) {
            $data['publish_time'] = date('Y-m-d H:i:s');
        }
        
        if (!isset($data['sort'])) {
            $maxSort = $this->fetchColumn(
                "SELECT MAX(sort) FROM {$this->table}"
            );
            $data['sort'] = ($maxSort ?: 0) + 1;
        }
        
        return $this->create($data);
    }

    public function updateNews($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteNews($id)
    {
        return $this->update($id, ['status' => 2]);
    }

    public function getTopNews($limit = 5)
    {
        return $this->select('id, type, title, summary, cover_image, source, author, is_banner, is_top, view_count, publish_time')
            ->where('status', 1)
            ->where('is_top', 1)
            ->orderBy('sort', 'ASC')
            ->orderBy('publish_time', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getLatestNews($limit = 10)
    {
        return $this->select('id, type, title, summary, cover_image, source, author, is_banner, is_top, view_count, publish_time')
            ->where('status', 1)
            ->orderBy('publish_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function search($keyword, $page = 1, $pageSize = 10)
    {
        return $this->select('id, type, title, summary, cover_image, source, author, is_banner, is_top, view_count, sort, status, publish_time, created_at')
            ->where('status', 1)
            ->whereRaw('(title LIKE :keyword OR summary LIKE :keyword)', [
                ':keyword' => "%{$keyword}%"
            ])
            ->orderBy('is_top', 'DESC')
            ->orderBy('publish_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate($page, $pageSize);
    }
}
