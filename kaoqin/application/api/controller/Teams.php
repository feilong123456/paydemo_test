<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

/**
 * Teams 机器人接口
 * 
 * 用于与 Node.js Teams Bot 服务通信
 */
class Teams extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    // Node.js Bot 服务地址（根据实际情况修改）
    private $botServiceUrl = 'http://localhost:3978';
    // 如果 Node.js 服务在另一台服务器，改为：
    // private $botServiceUrl = 'https://bot.yourdomain.com';

    /**
     * 推送消息到 Teams
     * 
     * @api {post} /api/teams/push 推送消息
     * @apiName pushMessage
     * @apiGroup Teams
     * @apiParam {String} conversationId 对话ID（必需）
     * @apiParam {String} message 消息内容（必需）
     * @apiParam {String} [title] 消息标题（可选，默认：通知）
     */
    public function push()
    {
        $conversationId = $this->request->post('conversationId');
        $message = $this->request->post('message');
        $title = $this->request->post('title', '通知');

        if (empty($conversationId) || empty($message)) {
            $this->error('缺少必要参数：conversationId 和 message');
        }

        // 调用 Node.js 服务推送消息
        $result = $this->callBotService('/api/notify', [
            'conversationId' => $conversationId,
            'message' => $message,
            'title' => $title
        ]);

        if (isset($result['success']) && $result['success']) {
            $this->success('消息推送成功', $result);
        } else {
            $errorMsg = isset($result['error']) ? $result['error'] : '消息推送失败';
            $this->error($errorMsg);
        }
    }

    /**
     * 获取对话列表
     * 
     * @api {get} /api/teams/conversations 获取对话列表
     * @apiName getConversations
     * @apiGroup Teams
     */
    public function conversations()
    {
        $result = $this->callBotService('/api/conversations', [], 'GET');

        if (isset($result['conversations'])) {
            $this->success('获取成功', $result);
        } else {
            $this->error('获取失败');
        }
    }

    /**
     * 推送考勤通知（示例）
     * 
     * @api {post} /api/teams/pushAttendance 推送考勤通知
     * @apiName pushAttendance
     * @apiGroup Teams
     * @apiParam {Int} admin_id 管理员ID
     * @apiParam {String} message 消息内容
     */
    public function pushAttendance()
    {
        $adminId = $this->request->post('admin_id');
        $message = $this->request->post('message', '您有新的考勤通知');

        if (empty($adminId)) {
            $this->error('缺少管理员ID');
        }

        // 从数据库获取用户的 Teams conversationId
        // 假设在 admin 表中有 teams_conversation_id 字段
        $conversationId = Db::name('admin')
            ->where('id', $adminId)
            ->value('teams_conversation_id');

        if (empty($conversationId)) {
            $this->error('该用户未绑定 Teams 对话ID');
        }

        // 调用推送接口
        $result = $this->callBotService('/api/notify', [
            'conversationId' => $conversationId,
            'message' => $message,
            'title' => '考勤通知'
        ]);

        if (isset($result['success']) && $result['success']) {
            $this->success('考勤通知推送成功', $result);
        } else {
            $errorMsg = isset($result['error']) ? $result['error'] : '推送失败';
            $this->error($errorMsg);
        }
    }

    /**
     * 调用 Node.js Bot 服务
     * 
     * @param string $endpoint API 端点
     * @param array $data 请求数据
     * @param string $method 请求方法
     * @return array
     */
    private function callBotService($endpoint, $data = [], $method = 'POST')
    {
        $url = $this->botServiceUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'error' => '连接失败：' . $error];
        }
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return $result ? $result : ['success' => false, 'error' => '响应解析失败'];
        } else {
            return ['success' => false, 'error' => '服务调用失败，HTTP状态码：' . $httpCode];
        }
    }
}
