<?php
require_once __DIR__ . '/../utils/Response.php';

class UploadController {
    public function uploadImage() {
        if (!isset($_FILES['file'])) {
            Response::error('没有上传文件');
        }

        $file = $_FILES['file'];
        $config = require __DIR__ . '/../config/config.php';

        // 检查文件类型
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed)) {
            Response::error('不支持的文件类型');
        }

        // 添加文件大小限制
        if ($file['size'] > 5 * 1024 * 1024) {
            Response::error('文件大小不能超过5MB');
            return;
        }

        // 生成文件名
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = md5(uniqid() . time()) . '.' . $ext;
        $filepath = $config['upload_path'] . $filename;

        // 检查上传目录
        if (!is_writable($config['upload_path'])) {
            Response::error('上传目录无写入权限');
            return;
        }

        // 移动文件
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            Response::error('文件上传失败');
        }

        Response::success([
            'url' => '/uploads/' . $filename
        ]);
    }
}