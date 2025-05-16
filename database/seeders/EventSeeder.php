<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'name' => 'Summer Music Festival 2024',
                'description' => 'A spectacular outdoor music festival featuring top artists from around the world. Experience three days of non-stop music across multiple stages.',
                'date' => '2025-07-15 18:00:00',
                'rows' => 20,
                'columns' => 20,
                'price' => 149.99,
                'status' => 'published'
            ],
            [
                'name' => 'Comedy Night Special',
                'description' => 'An evening of laughter with the best stand-up comedians in town. Featuring international and local talent.',
                'date' => '2025-06-01 20:00:00',
                'rows' => 15,
                'columns' => 20,
                'price' => 79.99,
                'status' => 'published'
            ],
            [
                'name' => 'Classical Symphony Orchestra',
                'description' => 'Experience the magic of classical music with our renowned symphony orchestra performing Beethoven\'s greatest works.',
                'date' => '2025-08-30 19:30:00',
                'rows' => 20,
                'columns' => 18,
                'price' => 129.99,
                'status' => 'published'
            ],
            [
                'name' => 'Broadway Musical - The Phantom',
                'description' => 'A stunning performance of the classic Broadway musical that has captivated audiences for generations.',
                'date' => '2025-09-15 19:00:00',
                'rows' => 18,
                'columns' => 20,
                'price' => 199.99,
                'status' => 'draft'
            ],
            [
                'name' => 'Rock Legends Reunion',
                'description' => 'The biggest names in rock music come together for one unforgettable night of classic hits and new collaborations.',
                'date' => '2025-10-01 20:00:00',
                'rows' => 20,
                'columns' => 20,
                'price' => 179.99,
                'status' => 'draft'
            ],
            [
                'name' => 'Jazz in the Park',
                'description' => 'An intimate evening of smooth jazz under the stars. Bring your blankets and enjoy the music.',
                'date' => '2025-06-30 19:00:00',
                'rows' => 10,
                'columns' => 15,
                'price' => 59.99,
                'status' => 'cancelled'
            ],
            [
                'name' => 'Dance Festival 2025',
                'description' => 'Contemporary and classical dance performances from leading dance companies around the world.',
                'date' => '2025-11-15 18:30:00',
                'rows' => 16,
                'columns' => 20,
                'price' => 89.99,
                'status' => 'published'
            ],
            [
                'name' => 'Opera Night - La Traviata',
                'description' => 'A magnificent production of Verdi\'s masterpiece, featuring international opera stars.',
                'date' => '2025-12-01 19:00:00',
                'rows' => 20,
                'columns' => 19,
                'price' => 159.99,
                'status' => 'draft'
            ],
            [
                'name' => 'New Year\'s Eve Spectacular',
                'description' => 'Welcome 2025 with an incredible show featuring live performances, fireworks, and more.',
                'date' => '2025-12-31 20:00:00',
                'rows' => 20,
                'columns' => 20,
                'price' => 249.99,
                'status' => 'draft'
            ],
            [
                'name' => 'Children\'s Theater - The Lion King',
                'description' => 'A magical theatrical experience perfect for the whole family. Bring the kids to this beloved classic.',
                'date' => '2025-07-30 14:00:00',
                'rows' => 15,
                'columns' => 20,
                'price' => 69.99,
                'status' => 'cancelled'
            ]
        ];

        foreach ($events as $eventData) {
            $event = Event::create($eventData);
            $event->generateSeatMap();
        }

        // Create some past events
        $pastEvents = [
            [
                'name' => 'Spring Music Festival 2024',
                'description' => 'A celebration of spring with live music and entertainment.',
                'date' => '2024-03-15 18:00:00',
                'rows' => 20,
                'columns' => 18,
                'price' => 129.99,
                'status' => 'published'
            ],
            [
                'name' => 'Valentine\'s Day Concert',
                'description' => 'A romantic evening of classical and contemporary love songs.',
                'date' => '2024-02-14 19:00:00',
                'rows' => 15,
                'columns' => 20,
                'price' => 99.99,
                'status' => 'published'
            ]
        ];

        foreach ($pastEvents as $eventData) {
            $event = Event::create($eventData);
            $event->generateSeatMap();
        }
    }
}
