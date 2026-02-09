<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceEvent;
use App\Models\AttendanceRecord;
use App\Models\Classroom;
use App\Models\ClassroomMembership;
use App\Models\Device;
use App\Models\RfidCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RfidScanTest extends TestCase
{
    use RefreshDatabase;

    private function createDevice(string $token = 'device-token'): Device
    {
        return Device::query()->create([
            'name' => 'Reader 1',
            'location' => 'Gate',
            'token_hash' => hash('sha256', $token),
            'is_active' => true,
        ]);
    }

    public function test_unregistered_card_returns_card_not_registered(): void
    {
        $this->createDevice('token-1');

        $response = $this
            ->withHeader('X-Device-Token', 'token-1')
            ->postJson('/api/rfid/scan', [
                'uid' => 'UNKNOWN',
                'scanned_at' => '2026-02-03T06:00:00+07:00',
            ]);

        $response->assertOk()->assertJson([
            'ok' => false,
            'code' => 'CARD_NOT_REGISTERED',
        ]);

        $this->assertSame(1, AttendanceEvent::query()->count());
    }

    public function test_checkin_and_checkout_flow(): void
    {
        $this->createDevice('token-2');

        $student = User::factory()->create();

        $classroom = Classroom::query()->create(['name' => 'XII IPA 1']);

        ClassroomMembership::query()->create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $student->id,
            'is_secretary' => false,
            'active_from' => '2026-02-01',
        ]);

        RfidCard::query()->create([
            'uid' => 'CARD123',
            'user_id' => $student->id,
            'status' => 'active',
        ]);

        $checkIn = $this
            ->withHeader('X-Device-Token', 'token-2')
            ->postJson('/api/rfid/scan', [
                'uid' => 'CARD123',
                'scanned_at' => '2026-02-03T06:00:00+07:00',
            ]);

        $checkIn->assertOk()->assertJson([
            'ok' => true,
            'action' => 'check_in',
            'code' => 'CHECKIN_OK',
        ]);

        $record = AttendanceRecord::query()->where('student_user_id', $student->id)->whereDate('date', '2026-02-03')->first();
        $this->assertNotNull($record);
        $this->assertSame('present', $record->status);
        $this->assertNotNull($record->check_in_at);
        $this->assertNull($record->check_out_at);

        $checkInAgain = $this
            ->withHeader('X-Device-Token', 'token-2')
            ->postJson('/api/rfid/scan', [
                'uid' => 'CARD123',
                'scanned_at' => '2026-02-03T06:05:00+07:00',
            ]);

        $checkInAgain->assertOk()->assertJson([
            'ok' => true,
            'action' => 'none',
            'code' => 'ALREADY_CHECKED_IN',
        ]);

        $outsideWindow = $this
            ->withHeader('X-Device-Token', 'token-2')
            ->postJson('/api/rfid/scan', [
                'uid' => 'CARD123',
                'scanned_at' => '2026-02-03T14:00:00+07:00',
            ]);

        $outsideWindow->assertOk()->assertJson([
            'ok' => false,
            'code' => 'OUTSIDE_WINDOW',
        ]);

        $checkOut = $this
            ->withHeader('X-Device-Token', 'token-2')
            ->postJson('/api/rfid/scan', [
                'uid' => 'CARD123',
                'scanned_at' => '2026-02-03T15:30:00+07:00',
            ]);

        $checkOut->assertOk()->assertJson([
            'ok' => true,
            'action' => 'check_out',
            'code' => 'CHECKOUT_OK',
        ]);

        $record->refresh();
        $this->assertNotNull($record->check_out_at);
        $this->assertSame('normal', $record->check_out_type);

        $this->assertSame(4, AttendanceEvent::query()->count());
    }
}
