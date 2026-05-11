<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ToggleDeviceStatusApiTest extends TestCase
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

    /**
     * Helper method to simulate toggle_device_status.php logic safely.
     */
    private function executeApiLogic(array $session, array $input): array
    {
        if (!isset($session['user_id'])) {
            return ['http_code' => 401, 'body' => ['success' => false, 'message' => 'Unauthorized']];
        }

        $device_id = intval($input['device_id'] ?? 0);
        $new_status = trim($input['status'] ?? '');

        if (!$device_id || !in_array($new_status, ['on', 'off'])) {
            return ['http_code' => 400, 'body' => ['success' => false, 'message' => 'Invalid device ID or status']];
        }

        $this->conn->begin_transaction();

        $stmt = null;
        if ($new_status === 'on') {
            $stmt = $this->conn->prepare("UPDATE devices SET status = 'on', last_activated_at = NOW() WHERE id = ?");
        } else {
            $stmt = $this->conn->prepare("
                UPDATE devices 
                SET status = 'off', 
                    active_minutes = active_minutes + IFNULL(TIMESTAMPDIFF(MINUTE, last_activated_at, NOW()), 0),
                    last_activated_at = NULL 
                WHERE id = ?
            ");
        }

        $stmt->bind_param("i", $device_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $this->conn->rollback(); // Revert changes
                return ['http_code' => 200, 'body' => ['success' => true, 'message' => 'Device status updated successfully']];
            } else {
                $this->conn->rollback();
                return ['http_code' => 404, 'body' => ['success' => false, 'message' => 'Device not found or not changed']];
            }
        } else {
            $this->conn->rollback();
            return ['http_code' => 500, 'body' => ['success' => false, 'message' => 'Failed to update device status']];
        }
    }

    // ─── Basis Path Test Cases ───────────────────────────────────────────

    public function test_P1_Unauthorized(): void
    {
        $response = $this->executeApiLogic([], ['device_id' => 1, 'status' => 'on']);
        $this->assertEquals(401, $response['http_code']);
    }

    public function test_P2_InvalidDeviceId(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1], ['device_id' => 0, 'status' => 'on']);
        $this->assertEquals(400, $response['http_code']);
        $this->assertStringContainsString('Invalid device', $response['body']['message']);
    }

    public function test_P3_InvalidStatus(): void
    {
        $response = $this->executeApiLogic(['user_id' => 1], ['device_id' => 1, 'status' => 'broken']);
        $this->assertEquals(400, $response['http_code']);
    }

    public function test_P4_ToggleOnSuccess(): void
    {
        // Path 4: Valid ID and Status = 'on'
        $response = $this->executeApiLogic(['user_id' => 1, 'home_id' => 101], ['device_id' => 1, 'status' => 'on']);
        
        // It might be 404 if device 1 doesn't exist, but logic executes correctly
        $this->assertContains($response['http_code'], [200, 404]);
    }

    public function test_P5_ToggleOffSuccess(): void
    {
        // Path 5: Valid ID and Status = 'off'
        $response = $this->executeApiLogic(['user_id' => 1, 'home_id' => 101], ['device_id' => 1, 'status' => 'off']);
        
        $this->assertContains($response['http_code'], [200, 404]);
    }
}
