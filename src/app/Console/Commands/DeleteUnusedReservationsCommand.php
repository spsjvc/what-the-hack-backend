<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Reservation;

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

        $dueReservations = Reservation::join('seats', 'reservations.seat_id', '=', 'seats.id')
                                        ->where('time_end', '<=', $now)
                                        ->whereNotNull('seats.user_id')
                                        ->get();

        if (!$dueReservations->isEmpty()) {
            foreach ($dueReservations as $reservation) {
                $reservationSeat = $reservation->seat;
                $reservationSeat->update(['user_id' => null]);

                $reservation->delete();
            }
            $this->info("All reservations that have expired (before $now), but still had user are deleted.");
        }


    }
}
