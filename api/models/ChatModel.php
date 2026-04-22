<?php
/**
 * 聊天消息模型
 */

namespace Models;

use Core\Model;
use Core\Database;

class ChatModel extends Model
{
    protected $table = 'chat_messages';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'from_type',
        'type',
        'content',
        'is_read',
    ];

    public function getHistory($userId, $page = 1, $pageSize = 20)
    {
        $offset = ($page - 1) * $pageSize;
        
        $db = Database::getInstance();
        
        $sql = "SELECT id, user_id, from_type, type, content, is_read, created_at
                FROM {$this->table}
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $messages = $db->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $pageSize,
            ':offset' => $offset,
        ]);
        
        $messages = array_reverse($messages);
        
        $countSql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id";
        $total = $db->fetchColumn($countSql, [':user_id' => $userId]);
        
        return [
            'items' => $messages,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => (int)ceil($total / $pageSize),
        ];
    }

    public function sendUserMessage($userId, $content, $type = 'text')
    {
        $data = [
            'user_id' => $userId,
            'from_type' => 1,
            'type' => $type,
            'content' => $content,
            'is_read' => 0,
        ];
        
        return $this->create($data);
    }

    public function sendServiceMessage($userId, $content, $type = 'text')
    {
        $data = [
            'user_id' => $userId,
            'from_type' => 2,
            'type' => $type,
            'content' => $content,
            'is_read' => 0,
        ];
        
        return $this->create($data);
    }

    public function getUnreadCount($userId, $fromType = 2)
    {
        return $this->count(
            "user_id = :user_id AND from_type = :from_type AND is_read = 0",
            [
                ':user_id' => $userId,
                ':from_type' => $fromType,
            ]
        );
    }

    public function markAsRead($messageId)
    {
        return $this->update($messageId, ['is_read' => 1]);
    }

    public function markAllAsRead($userId, $fromType = 2)
    {
        $db = Database::getInstance();
        return $db->update(
            $this->table,
            ['is_read' => 1],
            "user_id = :user_id AND from_type = :from_type AND is_read = 0",
            [
                ':user_id' => $userId,
                ':from_type' => $fromType,
            ]
        );
    }

    public function getConversationList($page = 1, $pageSize = 20)
    {
        $db = Database::getInstance();
        
        $sql = "SELECT 
                    cm.*,
                    u.nickname,
                    u.avatar,
                    u.phone
                FROM {$this->table} cm
                INNER JOIN (
                    SELECT 
                        user_id,
                        MAX(created_at) as last_time
                    FROM {$this->table}
                    GROUP BY user_id
                ) last_msg ON cm.user_id = last_msg.user_id AND cm.created_at = last_msg.last_time
                LEFT JOIN users u ON cm.user_id = u.id
                ORDER BY cm.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $offset = ($page - 1) * $pageSize;
        
        return $db->fetchAll($sql, [
            ':limit' => $pageSize,
            ':offset' => $offset,
        ]);
    }

    public function getMessagesByUserId($userId, $limit = 50)
    {
        return $this->select('id, user_id, from_type, type, content, is_read, created_at')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }
}
