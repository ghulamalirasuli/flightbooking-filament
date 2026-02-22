<?php
namespace App\Filament\Resources\GroupBookings\Pages;

use App\Filament\Resources\GroupBookings\GroupBookingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;
use Illuminate\Support\Str;
use App\Models\GroupBooking;
use App\Models\GroupFlight;

class CreateGroupBooking extends CreateRecord
{
    protected static string $resource = GroupBookingResource::class;

  protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // ---------------------------------------------------------
            // 1. EXTRACT DATA & PREPARE COMMON VARIABLES
            // ---------------------------------------------------------
            $groupFlights = $data['groupFlights'] ?? []; 
            
            $branch_code = Branch::where('id',  $data['branch_id'] )->value('branch_code');

            // Generate ONE shared Reference Number for the whole batch
            $batchReferenceNo = 'GBF'.$branch_code . now()->format('ymdHis') . strtoupper(Str::random(6));
             $uniqueGUid = 'GBX' . now()->format('ymdHis') . rand(10, 99); 
            // Common IDs
            $userId = Auth::id();

            $createdRecords = [];

                $trxData = [
                                        
                    'uid'           => $uniqueGUid, 
                    'branch_id'     =>$data['branch_id'] ?? null,
                    'user_id'       => $userId,
                    'account_id'     =>$data['account_id'] ?? null,
                    'currency'      => $data['currency'],
                    'reference_no'  => $batchReferenceNo,
                    'type'      => $data['type'],
                    'hand_baggage'      => $data['hand_baggage'],
                    'baggage'      => $data['baggage'],

                    'adult_seat'      => $data['adult_seat'],
                    'adult_basefare'      => $data['adult_basefare'],
                    'adult_tax'      => $data['adult_tax'],
                    'adult_tprice'      => $data['adult_tprice'],

                    'child_seat'      => $data['child_seat'],
                    'child_basefare'      => $data['child_basefare'],
                    'child_tax'      => $data['child_tax'],
                    'child_tprice'      => $data['child_tprice'],


                    'infant_seat'      => $data['infant_seat'],
                    'infant_basefare'      => $data['infant_basefare'],
                    'infant_tax'      => $data['infant_tax'],
                    'infant_tprice'      => $data['infant_tprice'],

                    'description'   => $data['description'] ?? null,
                    
                    'update'      => $data['update'] ?? null,
                     'date_confirm'     => now(),
                    'date_update'      => now(),
                ];

                // D. Create The Transaction Record
                $record = static::getModel()::create($trxData);
                $createdRecords[] = $record;

            // ---------------------------------------------------------
            // 2. LOOP THROUGH GROUP FLIGHTS (Create Transaction PER Item)
            // ---------------------------------------------------------
           
              
              
                 foreach ($groupFlights as $index => $flight) {

                  $uniqueUid = 'GBX' . now()->format('ymdHis') . rand(10, 99) . $index; 
                $uniqueRef = 'G-' . now()->format('ymdHis') . rand(1000, 9999);

                GroupFlight::create([
                    'uid'              => $uniqueUid,
                    'reference_no'     => $batchReferenceNo, // Shared Batch ID
                    'airlines'     => $flight['airlines'] ?? null,
                    'flightno'       => $flight['flightno'] ?? null,
                    
                    'class'     => $flight['class'] ?? null,
                    'pnr'    => $flight['pnr'] ?? null,
                    'from_f'      => $flight['from_f'] ?? null,
                    'to_f'      => $flight['to_f'] ?? null,

                    'f_terminal'      => $flight['f_terminal'] ?? null,
                    't_terminal'      => $flight['t_terminal'] ?? null,

                    'depart_time'      => $flight['depart_time'] ?? null,
                    'arrival_time'      => $flight['arrival_time'] ?? null,
                    'duration'      =>  null,
                    'layover'     =>  null,
                    'stops'    =>  null,
                    'changeable'  => $flight['changeable'] ?? null,
                    'refundable'    => $flight['refundable'] ?? null,
                ]);

                 }

            return $record;
            });
    }


}
