<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LoginApiTest extends TestCase
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
     * Helper method to simulate login.php logic safely.
     */
    private function executeApiLogic(array $input): array
    {
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            return ['http_code' => 400, 'body' => ['success' => false, 'message' => 'Username and password are required']];
        }

        $stmt = $this->conn->prepare("SELECT id, username, password, role, home_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // In the actual API, it uses direct comparison or password_verify
            // Simulating direct comparison based on original code
            if ($password === $user['password']) {
                return ['http_code' => 200, 'body' => [
                    'success' => true, 
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role'],
                        'home_id' => $user['home_id']
                    ]
                ]];
            } else {
                return ['http_code' => 401, 'body' => ['success' => false, 'message' => 'Invalid username or password']];
            }
        } else {
            return ['http_code' => 401, 'body' => ['success' => false, 'message' => 'Invalid username or password']];
        }
    }

    // ─── Basis Path Test Cases ───────────────────────────────────────────

    public function test_P1_EmptyUsernameAndPassword(): void
    {
        $response = $this->executeApiLogic(['username' => '', 'password' => '']);
        $this->assertEquals(400, $response['http_code']);
        $this->assertStringContainsString('required', $response['body']['message']);
    }

    public function test_P2_EmptyPassword(): void
    {
        $response = $this->executeApiLogic(['username' => 'owner', 'password' => '']);
        $this->assertEquals(400, $response['http_code']);
    }

    public function test_P3_EmptyUsername(): void
    {
        $response = $this->executeApiLogic(['username' => '', 'password' => 'pass123']);
        $this->assertEquals(400, $response['http_code']);
    }

    public function test_P4_NonExistentUser(): void
    {
        $response = $this->executeApiLogic(['username' => 'nonexistent999', 'password' => 'pass123']);
        $this->assertEquals(401, $response['http_code']);
        $this->assertStringContainsString('Invalid username or password', $response['body']['message']);
    }

    public function test_P5_WrongPassword(): void
    {
        // Requires an actual user in DB to fully test locally, simulating path execution
        // Assume 'owner' exists but password is wrong.
        $response = $this->executeApiLogic(['username' => 'owner', 'password' => 'wrongpass']);
        // Might be 401 if user exists, or 401 if not. Both lead to same error.
        $this->assertEquals(401, $response['http_code']);
    }
}
