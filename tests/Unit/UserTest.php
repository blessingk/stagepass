<?php

use App\Models\User;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false
        ]);
    }

    public function test_user_can_be_created()
    {
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_admin' => false
        ]);
    }

    public function test_password_is_hashed()
    {
        $this->assertTrue(Hash::check('password', $this->user->password));
    }

    public function test_user_can_be_admin()
    {
        $admin = User::factory()->create([
            'is_admin' => true
        ]);

        $this->assertTrue($admin->is_admin);
        $this->assertFalse($this->user->is_admin);
    }

    public function test_user_has_bookings_relationship()
    {
        // Create an event
        $event = Event::create([
            'name' => 'Test Event',
            'description' => 'Test Description',
            'date' => now()->addDays(7),
            'venue' => 'Test Venue',
            'rows' => 5,
            'columns' => 5,
            'status' => 'published'
        ]);

        // Generate seats
        $event->generateSeatMap();
        $seat = $event->seats()->first();

        // Create a booking for the user
        Booking::create([
            'user_id' => $this->user->id,
            'event_id' => $event->id,
            'seat_id' => $seat->id,
            'status' => 'confirmed',
            'total_amount' => 100.00,
            'payment_status' => 'paid',
            'payment_method' => 'credit_card'
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->user->bookings);
        $this->assertEquals(1, $this->user->bookings()->count());
    }

    public function test_user_initials_method()
    {
        $this->assertEquals('JD', $this->user->initials());

        $user = User::factory()->create([
            'name' => 'John Middle Doe'
        ]);
        $this->assertEquals('JMD', $user->initials());

        $user = User::factory()->create([
            'name' => 'John'
        ]);
        $this->assertEquals('J', $user->initials());
    }

    public function test_remember_token_can_be_updated()
    {
        $token = 'new_remember_token';
        $this->user->remember_token = $token;
        $this->user->save();

        $this->assertEquals($token, $this->user->fresh()->remember_token);
    }

    public function test_email_verification()
    {
        // Initially email should not be verified
        $unverifiedUser = User::factory()->unverified()->create();
        $this->assertNull($unverifiedUser->email_verified_at);

        // Verify email
        $now = now();
        $unverifiedUser->email_verified_at = $now;
        $unverifiedUser->save();

        $this->assertEquals($now->timestamp, $unverifiedUser->fresh()->email_verified_at->timestamp);
    }

    public function test_hidden_attributes()
    {
        $userArray = $this->user->toArray();
        
        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }
} 