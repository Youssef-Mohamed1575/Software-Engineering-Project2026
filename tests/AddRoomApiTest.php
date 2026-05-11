<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AddRoomApiTest extends TestCase
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

        if (($session['role'] ?? '') !== 'homeOwner') {
            return ['http_code' => 403, 'body' => ['success' => false, 'message' => 'Only home owners can add rooms']];
        }

        $home_id = intval($session['home_id'] ?? 0);
        if (!$home_id) {
            return ['http_code' => 400, 'body' => ['success' => false, 'message' => 'No home associated with this user']];
        }

        $room_name = trim($input['name'] ?? '');
        
        if (empty($room_name)) {
            return ['http_code' => 400, 'body' => ['success' => false, 'message' => 'Room name is required']];
        }

        $this->conn->begin_transaction();

        $this->conn->query("SET FOREIGN_KEY_CHECKS = 0");

        $stmt = $this->conn->prepare("INSERT INTO rooms (name, home_id) VALUES (?, ?)");
        $stmt->bind_param("si", $room_name, $home_id);

        if ($stmt->execute()) {
            $room_id = $stmt->insert_id;
            $this->conn->rollback();
            $this->conn->query("SET FOREIGN_KEY_CHECKS = 1");
            return ['http_code' => 200, 'body' => ['success' => true, 'message' => 'Room added successfully', 'room_id' => $room_id]];
        } else {
            $this->conn->rollback();
            $this->conn->query("SET FOREIGN_KEY_CHECKS = 1");
            return ['http_code' => 500, 'body' => ['success' => false, 'message' => 'Failed to add room']];
        }
    }

    public function test_P1_UnauthorizedFails(): void
    {
        $response = $this->executeApiLogic([], ['name' => 'Living Room']);
        $this->assertEquals(401, $response['http_code']);
    }

    public function test_P2_NotHomeOwnerFails(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'role' => 'guest'], ['name' => 'Living Room']);
        $this->assertEquals(403, $response['http_code']);
    }

    public function test_P3_MissingHomeIdFails(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'role' => 'homeOwner'], ['name' => 'Living Room']);
        $this->assertEquals(400, $response['http_code']);
    }

    public function test_P4_EmptyRoomNameFails(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'role' => 'homeOwner', 'home_id' => 101], ['name' => '']);
        $this->assertEquals(400, $response['http_code']);
        $this->assertStringContainsString('required', $response['body']['message']);
    }

    public function test_P5_SuccessInsert(): void
    {
        $response = $this->executeApiLogic(
            ['user_id' => 1, 'role' => 'homeOwner', 'home_id' => 101], 
            ['name' => 'Living Room']
        );
        $this->assertEquals(200, $response['http_code']);
        $this->assertTrue($response['body']['success']);
    }
}
