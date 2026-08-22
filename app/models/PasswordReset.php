<?php

class PasswordReset extends Model {

    public function logResetAttempt($email, $ipAddress) {
        $stmt = $this->db->prepare(
            "INSERT INTO password_reset_attempts (email, ip_address, attempt_time)
             VALUES (:email, :ip, NOW())"
        );
        return $stmt->execute([
            ':email' => substr((string)$email, 0, 150),
            ':ip'    => substr((string)$ipAddress, 0, 45)
        ]);
    }

    public function isRateLimited($ipAddress, $email, $windowMinutes = 15, $maxAttempts = 5) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS attempts
             FROM password_reset_attempts
             WHERE attempt_time >= DATE_SUB(NOW(), INTERVAL :window MINUTE)
               AND (ip_address = :ip OR (:email <> '' AND email = :email))"
        );
        $stmt->bindValue(':window', (int)$windowMinutes, PDO::PARAM_INT);
        $stmt->bindValue(':ip', substr((string)$ipAddress, 0, 45), PDO::PARAM_STR);
        $stmt->bindValue(':email', substr((string)$email, 0, 150), PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        return ((int)($result['attempts'] ?? 0)) > $maxAttempts;
    }

    public function createToken($userType, $userId, $email, $tokenHash, $expiresAt, $requestIp) {
        $stmt = $this->db->prepare(
            "INSERT INTO password_reset_tokens
                (user_type, user_id, email, token_hash, expires_at, request_ip, created_at)
             VALUES
                (:user_type, :user_id, :email, :token_hash, :expires_at, :request_ip, NOW())"
        );
        return $stmt->execute([
            ':user_type'  => $userType,
            ':user_id'    => $userId,
            ':email'      => $email,
            ':token_hash' => $tokenHash,
            ':expires_at' => $expiresAt,
            ':request_ip' => $requestIp
        ]);
    }

    public function invalidateActiveTokens($userType, $userId) {
        $stmt = $this->db->prepare(
            "UPDATE password_reset_tokens
             SET used_at = NOW()
             WHERE user_type = :user_type
               AND user_id = :user_id
               AND used_at IS NULL
               AND expires_at > NOW()"
        );
        return $stmt->execute([
            ':user_type' => $userType,
            ':user_id'   => $userId
        ]);
    }

    public function isValidToken($plainToken) {
        $tokenHash = hash('sha256', $plainToken);
        $stmt = $this->db->prepare(
            "SELECT reset_id
             FROM password_reset_tokens
             WHERE token_hash = :token_hash
               AND used_at IS NULL
               AND expires_at > NOW()
             LIMIT 1"
        );
        $stmt->execute([':token_hash' => $tokenHash]);
        return (bool)$stmt->fetch();
    }

    public function findByToken($plainToken) {
        $tokenHash = hash('sha256', $plainToken);
        $stmt = $this->db->prepare(
            "SELECT reset_id, user_type, user_id, email, expires_at, used_at
             FROM password_reset_tokens
             WHERE token_hash = :token_hash
             LIMIT 1"
        );
        $stmt->execute([':token_hash' => $tokenHash]);
        return $stmt->fetch();
    }

    public function consumeToken($plainToken) {
        $tokenHash = hash('sha256', $plainToken);
        $stmt = $this->db->prepare(
            "UPDATE password_reset_tokens
             SET used_at = NOW()
             WHERE token_hash = :token_hash
               AND used_at IS NULL
               AND expires_at > NOW()"
        );
        $stmt->execute([':token_hash' => $tokenHash]);
        return $stmt->rowCount() === 1;
    }
}
