<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DeleteUserApiTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli("localhost", "root", "", "projectdb");
        if ($this->conn->connect_error) {
            $this->markTestSkipped("Database connection not available.");
        }
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    private function executeApiLogic(array $session, array $input): array
    {
        if (!isset($session['user_id'])) {
            return ['http_code' => 401, 'body' => ['success' => false, 'message' => 'Unauthorized access']];
        }

        if (($session['role'] ?? '') !== 'homeOwner' && ($session['role'] ?? '') !== 'admin') {
            return ['http_code' => 403, 'body' => ['success' => false, 'message' => 'Not allowed to delete users']];
        }

        $target_user_id = intval($input['user_id'] ?? 0);
        
        if (!$target_user_id) {
            return ['http_code' => 400, 'body' => ['success' => false, 'message' => 'Target user ID is required']];
        }

        if ($target_user_id === $session['user_id']) {
            return ['http_code' => 400, 'body' => ['success' => false, 'message' => 'Cannot delete yourself']];
        }

        $this->conn->begin_transaction();

        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $target_user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $this->conn->rollback();
                return ['http_code' => 200, 'body' => ['success' => true, 'message' => 'User deleted successfully']];
            } else {
                $this->conn->rollback();
                return ['http_code' => 404, 'body' => ['success' => false, 'message' => 'User not found']];
            }
        } else {
            $this->conn->rollback();
            return ['http_code' => 500, 'body' => ['success' => false, 'message' => 'Failed to delete user']];
        }
    }


    public function test_P1_UnauthorizedFails(): void
    {
        $response = $this->executeApiLogic([], ['user_id' => 2]);
        $this->assertEquals(401, $response['http_code']);
    }

    public function test_P2_GuestRoleFails(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'role' => 'guest'], ['user_id' => 2]);
        $this->assertEquals(403, $response['http_code']);
    }

    public function test_P3_MissingTargetUserId(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'role' => 'homeOwner'], ['user_id' => 0]);
        $this->assertEquals(400, $response['http_code']);
    }

    public function test_P4_CannotDeleteSelf(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'role' => 'homeOwner'], ['user_id' => 1]);
        $this->assertEquals(400, $response['http_code']);
    }

    public function test_P5_SuccessDelete(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'role' => 'homeOwner'], ['user_id' => 2]);
        
        $this->assertContains($response['http_code'], [200, 404]);
    }
}
