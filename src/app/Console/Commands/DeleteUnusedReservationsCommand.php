<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\WebsocketGateway\Websocket;

class DeleteUnusedReservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes reservations that are due and unused.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::now();
        $now->second(0);

        $this->invalidateTakenSeats($now);
        $this->invalidateNotTakenSeats($now);

        $rooms = Room::all();
        foreach ($rooms as $room) {
            \Websocket::sendToRoom($room->id, Websocket::EVENT_ROOMS_UPDATED, $room);
        }
    }

    private function invalidateTakenSeats($now) {
        $dueReservations = Reservation::where('time_end', '<=', $now)
                                      ->get();

        if (!$dueReservations->isEmpty()) {
            foreach ($dueReservations as $reservation) {
                $reservationSeat = $reservation->seat;
                $reservationSeat->update(['user_id' => null]);
                if ($reservation->seat->user_id !== null) {
                    $reservation->delete();
                }
            }
            $this->info("All reservations that have expired (before $now), but still had user are deleted.");
        }
    }

    private function invalidateNotTakenSeats($now) {
        $timeOffset = config('reservation.time_offset');
        $dueReservations = Reservation::where('time_start', '<=', $now->subMinutes($timeOffset))
                                      ->get();
        if (!$dueReservations->isEmpty()) {
            foreach ($dueReservations as $reservation) {
                if ($reservation->seat->user_id === null) {
                    $reservation->delete();
                }
            }
            $this->info("All reservations that have expired, but user did not show up are deleted.");
        }
    }
}
