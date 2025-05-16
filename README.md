# Ticket Booking System

A high-concurrency ticket booking system built with Laravel, Livewire, TailwindCSS, and AlpineJS.

## Features

- Event Management (CRUD operations)
- Interactive Seat Map Display
- Concurrent Seat Booking System
- Admin Panel
- High Concurrency Safety

## Tech Stack

- **Backend Framework:** Laravel 12.0
- **Frontend Framework:** Livewire 3.6
- **UI Components:** Livewire Flux 2.1
- **CSS Framework:** TailwindCSS 3.x
- **JavaScript:** AlpineJS 3.x
- **Database:** MySQL/PostgreSQL
- **Cache:** Redis (for session and cache)

### Key Packages

```json
{
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "laravel/tinker": "^2.10.1",
    "livewire/flux": "^2.1.1",
    "livewire/livewire": "^3.6",
    "livewire/volt": "^1.7.0"
}
```

## Architecture

### Concurrency Handling

The system uses multiple layers of concurrency control to prevent overbooking:

1. **Database Level:**
   - Pessimistic locking for seat reservations
   - Unique constraints on seat bookings
   - Transaction isolation level: SERIALIZABLE

2. **Application Level:**
   - Atomic operations for seat status updates
   - Queue-based job processing for heavy operations
   - Temporary seat reservation system with TTL

3. **Frontend Level:**
   - Real-time seat status updates
   - Optimistic UI updates with rollback
   - Clear user feedback

## Setup Instructions

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd stagepass
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node dependencies:
   ```bash
   npm install
   ```

4. Copy .env.example to .env and configure your database:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Run migrations:
   ```bash
   php artisan migrate
   ```

7. Build assets:
   ```bash
   npm run dev
   ```

8. Start the server:
   ```bash
   php artisan serve
   ```

9. Start the queue worker (in a separate terminal):
   ```bash
   php artisan queue:work
   ```

10. Schedule the cleanup command (in a separate terminal):
    ```bash
    php artisan schedule:work
    ```

## Testing

Run the test suite:
```bash
php artisan test
```

### Concurrency Testing

To simulate concurrent booking attempts:
```bash
php artisan booking:simulate {seatId} --users=100
```

This command simulates multiple users attempting to book the same seat simultaneously.

## Database Schema

### Events Table
- id
- name
- description
- date
- venue
- rows
- columns
- status (draft, published, cancelled)
- created_at
- updated_at
- deleted_at

### Seats Table
- id
- event_id
- row
- column
- status (available, reserved, booked)
- reservation_expires_at
- created_at
- updated_at
- deleted_at

### Bookings Table
- id
- user_id
- event_id
- seat_id
- status (pending, confirmed, cancelled)
- total_amount
- payment_status
- payment_method
- created_at
- updated_at
- deleted_at

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## API Documentation

[API documentation will be added here] 