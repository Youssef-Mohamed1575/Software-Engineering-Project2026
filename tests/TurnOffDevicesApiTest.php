<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TurnOffDevicesApiTest extends TestCase
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
            return ['http_code' => 401, 'body' => ['success' => false, 'message' => 'Unauthorized']];
        }

        $home_id = intval($session['home_id'] ?? 0);
        if (!$home_id) {
            return ['http_code' => 400, 'body' => ['success' => false, 'message' => 'No home associated with this user']];
        }

        $type = strtolower(trim($input['type'] ?? 'devices'));

        $this->conn->begin_transaction();

        $stmt = null;
        if ($type === "devices") {
            $stmt = $this->conn->prepare("UPDATE devices SET status = 'off' WHERE home_id = ?");
            $stmt->bind_param("i", $home_id);
        } else if ($type === "light" || $type === "heater" || $type === "ac") {
            $stmt = $this->conn->prepare("UPDATE devices SET status = 'off' WHERE home_id = ? AND LOWER(type) = ?");
            $stmt->bind_param("is", $home_id, $type);
        } else {
            $this->conn->rollback();
            return ['http_code' => 400, 'body' => ['success' => false, 'message' => 'Invalid type specified']];
        }

        if ($stmt->execute()) {
            $this->conn->rollback(); 
            return ['http_code' => 200, 'body' => ['success' => true, 'message' => ucfirst($type) . ' turned off successfully']];
        } else {
            $this->conn->rollback();
            return ['http_code' => 500, 'body' => ['success' => false, 'message' => 'Failed to update devices']];
        }
    }


    public function test_P1_Unauthorized(): void
    {
        $response = $this->executeApiLogic([], ['type' => 'devices']);
        $this->assertEquals(401, $response['http_code']);
        $this->assertEquals('Unauthorized', $response['body']['message']);
    }

    public function test_P2_MissingHomeId(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1], ['type' => 'devices']);
        $this->assertEquals(400, $response['http_code']);
        $this->assertStringContainsString('No home associated', $response['body']['message']);
    }

    public function test_P3_TurnOffAllDevices(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'home_id' => 101], ['type' => 'devices']);
        $this->assertEquals(200, $response['http_code']);
        $this->assertStringContainsString('Devices', $response['body']['message']);
    }

    public function test_P4_TurnOffLight(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'home_id' => 101], ['type' => 'light']);
        $this->assertEquals(200, $response['http_code']);
        $this->assertStringContainsString('Light', $response['body']['message']);
    }

    public function test_P5_TurnOffHeater(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'home_id' => 101], ['type' => 'heater']);
        $this->assertEquals(200, $response['http_code']);
        $this->assertStringContainsString('Heater', $response['body']['message']);
    }

    public function test_P6_TurnOffAC(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'home_id' => 101], ['type' => 'ac']);
        $this->assertEquals(200, $response['http_code']);
        $this->assertStringContainsString('Ac', $response['body']['message']);
    }

    public function test_P7_InvalidType(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1, 'home_id' => 101], ['type' => 'fan']);
        $this->assertEquals(400, $response['http_code']);
        $this->assertStringContainsString('Invalid type', $response['body']['message']);
    }
}
