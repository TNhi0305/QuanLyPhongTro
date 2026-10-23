<?php

namespace App\Helpers;

use PDO;
use Throwable;

/**
 * Helper to handle Push Notifications (Expo Push Service) and notification history.
 */
class PushNotification
{
    private const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Save or update an Expo Push Token for a user device.
     */
    public static function saveToken(int $userId, string $token, string $deviceType = 'mobile', ?string $deviceId = null): bool
    {
        $token = trim($token);
        if ($token === '' || $userId <= 0) {
            return false;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO `push_tokens` (`user_id`, `token`, `device_type`, `device_id`, `updated_at`)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    `user_id` = VALUES(`user_id`),
                    `device_type` = VALUES(`device_type`),
                    `device_id` = VALUES(`device_id`),
                    `updated_at` = NOW()
            ");
            return $stmt->execute([$userId, $token, $deviceType, $deviceId]);
        } catch (Throwable $e) {
            error_log('PushNotification::saveToken error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a push token on logout or when revoked.
     */
    public static function removeToken(string $token): bool
    {
        $token = trim($token);
        if ($token === '') {
            return false;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM `push_tokens` WHERE `token` = ?");
            return $stmt->execute([$token]);
        } catch (Throwable $e) {
            error_log('PushNotification::removeToken error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send remote push notifications via Expo Push Service.
     */
    public static function sendExpoPush(array $tokens, string $title, string $body, array $data = []): array
    {
        $validTokens = array_filter(array_unique($tokens), function ($t) {
            return is_string($t) && (str_starts_with($t, 'ExponentPushToken[') || str_starts_with($t, 'ExpoPushToken[') || strlen($t) > 20);
        });

        if (empty($validTokens)) {
            return ['success' => false, 'message' => 'No valid push tokens provided.'];
        }

        $messages = [];
        foreach ($validTokens as $token) {
            $messages[] = [
                'to'       => $token,
                'sound'    => 'default',
                'title'    => $title,
                'body'     => $body,
                'data'     => $data,
                'priority' => 'high',
                'channelId' => 'default'
            ];
        }

        $jsonPayload = json_encode($messages);

        if (function_exists('curl_init')) {
            $ch = curl_init(self::EXPO_PUSH_URL);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $jsonPayload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Accept-Encoding: gzip, deflate'
                ],
                CURLOPT_TIMEOUT        => 5
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return [
                'success' => ($httpCode >= 200 && $httpCode < 300),
                'http_code' => $httpCode,
                'response' => json_decode($response ?: '[]', true)
            ];
        }

        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => $jsonPayload,
                'timeout' => 5
            ]
        ];
        $context = stream_context_create($opts);
        $result = @file_get_contents(self::EXPO_PUSH_URL, false, $context);

        return [
            'success' => ($result !== false),
            'response' => json_decode($result ?: '[]', true)
        ];
    }

    /**
     * Send push notification to a specific user ID.
     */
    public static function sendToUser(int $userId, string $title, string $body, array $data = []): array
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT `token` FROM `push_tokens` WHERE `user_id` = ?");
            $stmt->execute([$userId]);
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($tokens)) {
                return ['success' => false, 'message' => "No push tokens registered for user #{$userId}."];
            }

            return self::sendExpoPush($tokens, $title, $body, $data);
        } catch (Throwable $e) {
            error_log('PushNotification::sendToUser error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send push notification to all users of a specific role (e.g. 'admin').
     */
    public static function sendToRole(string $role, string $title, string $body, array $data = []): array
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT pt.token 
                FROM push_tokens pt
                JOIN users u ON u.id = pt.user_id
                WHERE u.role = ? AND u.status = 1
            ");
            $stmt->execute([$role]);
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($tokens)) {
                return ['success' => false, 'message' => "No tokens for role {$role}."];
            }

            return self::sendExpoPush($tokens, $title, $body, $data);
        } catch (Throwable $e) {
            error_log('PushNotification::sendToRole error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create notification history record in database and dispatch push notification.
     */
    public static function createAndSend(
        string $title,
        string $content,
        string $type,
        ?int $targetTenantId = null,
        ?int $targetUserId = null,
        ?string $refType = null,
        ?int $refId = null,
        array $dataPayload = [],
        ?int $createdBy = null
    ): array {
        try {
            $pdo = Database::getConnection();

            // Auto-resolve target_user_id from target_tenant_id if needed
            if (!$targetUserId && $targetTenantId) {
                $stmt = $pdo->prepare("SELECT user_id FROM tenants WHERE id = ? LIMIT 1");
                $stmt->execute([$targetTenantId]);
                $targetUserId = $stmt->fetchColumn() ?: null;
            }

            $payloadJson = !empty($dataPayload) ? json_encode($dataPayload, JSON_UNESCAPED_UNICODE) : null;

            $stmt = $pdo->prepare("
                INSERT INTO `notifications` 
                    (`title`, `content`, `type`, `target_tenant_id`, `target_user_id`, `created_by`, `is_read`, `reference_type`, `reference_id`, `data_payload`, `created_at`)
                VALUES 
                    (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $title,
                $content,
                $type,
                $targetTenantId,
                $targetUserId,
                $createdBy,
                $refType,
                $refId,
                $payloadJson
            ]);
            $notificationId = (int) $pdo->lastInsertId();

            // Prepare push payload
            $pushData = array_merge($dataPayload, [
                'notification_id' => $notificationId,
                'type'           => $type,
                'reference_type' => $refType,
                'reference_id'   => $refId
            ]);

            // Dispatch push notification
            $pushResult = null;
            if ($targetUserId) {
                $pushResult = self::sendToUser((int)$targetUserId, $title, $content, $pushData);
            } elseif ($type === 'maintenance' && !$targetUserId) {
                // If it's an incident report for admins
                $pushResult = self::sendToRole('admin', $title, $content, $pushData);
            }

            return [
                'success'         => true,
                'notification_id' => $notificationId,
                'push_result'     => $pushResult
            ];
        } catch (Throwable $e) {
            error_log('PushNotification::createAndSend error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
