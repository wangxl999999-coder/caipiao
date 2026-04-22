<?php
/**
 * 控制器基类
 */

namespace Core;

use Exception;

class Controller
{
    protected $request = [];
    protected $response = [];

    public function __construct()
    {
        $this->parseRequest();
    }

    private function parseRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        $this->request = [
            'method' => $method,
            'path' => $path,
            'get' => $_GET ?? [],
            'post' => $this->getPostData(),
            'header' => $this->getRequestHeaders(),
        ];
    }

    private function getPostData()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            return json_decode($input, true) ?? [];
        }
        
        return $_POST ?? [];
    }

    private function getRequestHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    protected function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->request['get'];
        }
        return $this->request['get'][$key] ?? $default;
    }

    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->request['post'];
        }
        return $this->request['post'][$key] ?? $default;
    }

    protected function input($key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($this->request['get'], $this->request['post']);
        }
        return $this->request['post'][$key] ?? $this->request['get'][$key] ?? $default;
    }

    protected function header($key = null, $default = null)
    {
        if ($key === null) {
            return $this->request['header'];
        }
        return $this->request['header'][$key] ?? $default;
    }

    protected function method()
    {
        return $this->request['method'];
    }

    protected function isGet()
    {
        return $this->method() === 'GET';
    }

    protected function isPost()
    {
        return $this->method() === 'POST';
    }

    protected function isPut()
    {
        return $this->method() === 'PUT';
    }

    protected function isDelete()
    {
        return $this->method() === 'DELETE';
    }

    protected function json($data = null, $code = 200, $message = 'success')
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        $response = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => time(),
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function success($data = null, $message = '操作成功')
    {
        return $this->json($data, 200, $message);
    }

    protected function error($message = '操作失败', $code = 400, $data = null)
    {
        return $this->json($data, $code, $message);
    }

    protected function unauthorized($message = '未授权访问')
    {
        return $this->json(null, 401, $message);
    }

    protected function notFound($message = '资源不存在')
    {
        return $this->json(null, 404, $message);
    }

    protected function validate($rules, $data = null)
    {
        $data = $data ?? $this->input();
        
        foreach ($rules as $field => $rule) {
            $ruleParts = explode('|', $rule);
            
            foreach ($ruleParts as $rulePart) {
                $ruleParams = explode(':', $rulePart);
                $ruleName = $ruleParams[0];
                $ruleValue = $ruleParams[1] ?? null;
                
                $value = $data[$field] ?? null;
                
                switch ($ruleName) {
                    case 'required':
                        if ($value === null || $value === '') {
                            throw new Exception("字段 {$field} 不能为空");
                        }
                        break;
                        
                    case 'email':
                        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            throw new Exception("字段 {$field} 不是有效的邮箱地址");
                        }
                        break;
                        
                    case 'phone':
                        if ($value !== null && !preg_match('/^1[3-9]\d{9}$/', $value)) {
                            throw new Exception("字段 {$field} 不是有效的手机号码");
                        }
                        break;
                        
                    case 'min':
                        if ($value !== null && strlen($value) < (int)$ruleValue) {
                            throw new Exception("字段 {$field} 长度不能少于 {$ruleValue} 个字符");
                        }
                        break;
                        
                    case 'max':
                        if ($value !== null && strlen($value) > (int)$ruleValue) {
                            throw new Exception("字段 {$field} 长度不能超过 {$ruleValue} 个字符");
                        }
                        break;
                        
                    case 'numeric':
                        if ($value !== null && !is_numeric($value)) {
                            throw new Exception("字段 {$field} 必须是数字");
                        }
                        break;
                        
                    case 'integer':
                        if ($value !== null && filter_var($value, FILTER_VALIDATE_INT) === false) {
                            throw new Exception("字段 {$field} 必须是整数");
                        }
                        break;
                        
                    case 'in':
                        $allowedValues = explode(',', $ruleValue);
                        if ($value !== null && !in_array($value, $allowedValues)) {
                            throw new Exception("字段 {$field} 的值必须是以下之一：" . implode(', ', $allowedValues));
                        }
                        break;
                }
            }
        }
        
        return true;
    }
}
