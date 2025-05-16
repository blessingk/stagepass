# StagePass - Event Ticketing System

A modern event ticketing system built with Laravel, focusing on real-time seat booking and management.

## Architecture & Design Decisions

### 1. Database Design
- **Soft Deletes**: Implemented on key models (Event, Seat, Booking) to maintain data integrity and history
- **Relationships**: 
  - One-to-Many between Events and Seats
  - One-to-Many between Events and Bookings
  - One-to-Many between Users and Bookings
  - One-to-One between Seats and Bookings
- **Optimizations**:
  - Indexed foreign keys for faster joins
  - Composite indexes for status + expiration checks
  - Chunked inserts for large seat generations

### 2. Authentication & Authorization
- **Multi-User Roles**: 
  - Admin users (is_admin flag) for event management
  - Regular users for booking tickets
- **Route Protection**: 
  - Admin middleware for protecting admin routes
  - Custom RedirectIfAdmin middleware for UX
- **Session Management**: 
  - Secure session handling with CSRF protection
  - Remember-me functionality
  - Session-based flash messages

### 3. Event Management
- **Status Lifecycle**:
  - Draft: Initial event creation
  - Published: Available for booking
  - Cancelled: No longer available
- **Seat Map Generation**:
  - Dynamic grid creation (max 20x20)
  - Automatic cleanup of old seats
  - Bulk insert optimization
- **Validation Rules**:
  - Maximum dimensions enforcement
  - Date and capacity validation
  - Price range constraints

### 4. Booking System
- **State Machine**:
  - Seat States: available → reserved → booked
  - Booking States: pending → completed/failed
  - Payment States: pending → paid/failed/refunded
- **Concurrency Control**:
  - Pessimistic locking for seat operations
  - Temporary reservations with TTL
  - Transaction isolation for booking operations
- **Payment Processing**:
  - Multiple payment method support
  - Secure payment confirmation flow
  - Automatic cleanup of abandoned bookings

### 5. Real-time Features
- **Seat Management**:
  - Live seat status updates (5s polling)
  - Immediate booking feedback
  - Concurrent booking protection
- **User Interface**:
  - Interactive seat selection
  - Real-time availability updates
  - Dynamic pricing display

### 6. Command Layer
- **Scheduled Tasks**:
  - Expired reservation cleanup (every minute)
  - Failed payment handling
  - Automatic event status updates
- **Maintenance Commands**:
  - Booking simulation for testing
  - Database cleanup utilities
  - System health checks

### 7. Service Layer
- **Business Logic Isolation**:
  - BookingService for core booking logic
  - EventService for event management
  - PaymentService for payment processing
- **Transaction Management**:
  - Atomic operations
  - Rollback capabilities
  - Dead lock prevention

### 8. Testing Strategy
- **Unit Tests**: Core business logic testing
- **Feature Tests**: End-to-end testing of key features
- **Integration Tests**: Testing component interactions
- **Concurrency Tests**: Simulation of concurrent bookings

## Concurrency Handling

The system implements a multi-layered approach to handle concurrent bookings and prevent race conditions:

### 1. Database Level Protection
- **Pessimistic Locking**: 
  - Uses `lockForUpdate()` when reserving seats to prevent double bookings
  - Ensures atomic operations during critical seat status updates
  ```php
  Seat::where('id', $seatId)
      ->where('status', Seat::STATUS_AVAILABLE)
      ->lockForUpdate()
      ->first();
  ```

- **Transaction Isolation**:
  - Wraps all booking operations in database transactions
  - Prevents phantom reads and non-repeatable reads
  ```php
  DB::transaction(function () {
      // Seat reservation logic
  });
  ```

### 2. Application Level Protection
- **Temporary Reservation System**:
  - Time-limited seat reservation (5 minutes)
  - Automatic cleanup of expired reservations via scheduled command
  - State machine for seat status: available → reserved → booked

- **Scheduled Cleanup**:
  - Artisan command `seats:cleanup-expired` runs periodically
  - Releases expired seat reservations
  - Updates associated booking statuses

### 3. Real-time Updates
- **Livewire Polling**:
  - Automatic seat map refresh every 5 seconds
  - Immediate UI feedback for user actions
  - Optimistic updates with rollback on failure

### 4. Error Handling & Recovery
- **Graceful Degradation**:
  - Comprehensive error catching and logging
  - User-friendly error messages
  - Automatic reservation cleanup

### 5. Testing & Simulation
- **Concurrent Booking Tests**:
  ```bash
  # Simulate concurrent bookings
  php artisan booking:simulate {seatId} --users=100
  ```

- **Test Coverage**:
  - Unit tests for booking service
  - Feature tests for concurrent scenarios
  - Integration tests for booking flow

### Future Concurrency Improvements
1. **Real-time Updates**:
   - Implement WebSocket for instant updates
   - Replace polling with push notifications

2. **Rate Limiting**:
   - Add rate limiting middleware
   - Configure booking attempt thresholds

3. **Advanced Monitoring**:
   - Implement deadlock detection
   - Add circuit breaker for external services
   - Enhanced logging and monitoring

## Key Technical Decisions

1. **Why Livewire?**
   - Reduces JavaScript complexity
   - Real-time reactivity without building an API
   - Seamless integration with Laravel

2. **Why Soft Deletes?**
   - Maintain booking history
   - Enable data recovery
   - Support for analytics and reporting

3. **Why Custom Admin Redirection?**
   - Better user experience for admin users
   - Clear separation between admin and user interfaces
   - Simplified navigation flow

4. **Why 20x20 Seat Limit?**
   - Optimal performance for real-time updates
   - Reasonable limit for most venues
   - Prevents potential scaling issues

## Future Considerations

1. **Scalability Improvements**
   - Queue system for high-traffic periods
   - Distributed caching for seat status
   - Horizontal scaling capabilities

2. **Feature Enhancements**
   - Multiple seating categories
   - Season pass functionality
   - Waiting list system
   - Multi-language support

3. **Technical Debt Management**
   - Regular dependency updates
   - Code refactoring plan
   - Performance monitoring implementation

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
- **Database:** MySQL
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

## Setup Instructions

1. Clone the repository:
   ```bash
   git clone git@github.com:blessingk/stagepass.git
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
   php artisan migrate --seed
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
