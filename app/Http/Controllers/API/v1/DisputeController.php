<?php

namespace App\Http\Controllers\API\v1;

use Validator;
use Carbon\Carbon;
use App\Models\Dispute;
use App\Traits\AwsS3Trait;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;

class DisputeController extends BaseController
{
    use AwsS3Trait;
    public function raiseDispute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|in:Payment Issue,Service Quality,Missed Appointment,Other',
            'other_reason' => 'required_if:reason,Other',
            'booking_id' => 'required|exists:appointments,booking_id',
            'appointment_date' => 'required|date',
            'description' => 'required',
            'file' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());      
        }

        $existingDispute = Dispute::where('booking_id', $request->booking_id)->where('status',1)->first();
        if ($existingDispute) {
            return $this->sendError('Dispute is already raised.');  
        }

        $ticketId = mt_rand(1000000, 9999999);

        while (Dispute::where('ticket_id', $ticketId)->exists()) {
            $ticketId = mt_rand(1000000, 9999999);
        }

        $dispute = Dispute::create([
            'customer_id' => auth('api')->user()->id,
            'ticket_id' => $ticketId,
            'booking_id' => $request->booking_id,
            'reason' => $request->reason,
            'other_reason' => $request->other_reason,
            'appointment_date' => $request->appointment_date,
            'description' => $request->description,
            'status' => 1,
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $uploadedUrl = $this->uploadFileToS3($file, 'dispute');
            $dispute->update(['file' => $uploadedUrl]);
        }
        $dispute->appointment_date = Carbon::parse($request->appointment_date)->format('d-M-Y');
        return $this->sendResponse($dispute, 'Dispute submitted successfully.');
    }

    public function getDisputes(Request $request)
    {
        $disputes = Dispute::where('customer_id', auth('api')->user()->id)->orderBy('created_at', 'desc')->paginate($request->per_page);
        $data = $disputes->groupBy(function ($dispute) {
            $date = $dispute->created_at;
                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } else {
                    return $date->format('d-M-Y');
                }
        })->map(function ($group, $date) {
            return [
                'day' => $date,
                'data' => $group->map(function ($dispute) {
                    return [
                        'id' => $dispute->id,
                        'ticket_id' => '#'.$dispute->ticket_id,
                        'description' => $dispute->description,
                        'time' => Carbon::parse($dispute->created_at)->format('h:i A'),
                        'status' => $dispute->status === 1 ? 'Open' : 'Closed',
                    ];
                }),
            ];
        })->values();
        return $this->sendResponse($data, 'Dispute retrived successfully.', $disputes);
    }
}
