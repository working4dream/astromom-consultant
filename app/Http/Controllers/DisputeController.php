<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dispute;
use App\Models\User;
use App\Models\DisputeDiscussion;
use Carbon\Carbon;


class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $disputes = Dispute::query();
        $request->filled('booking_id') ? $disputes->where('booking_id', 'LIKE' ,"%{$request->booking_id}%") : null;
        if ($request->filled('full_name')) {
            $fullName = trim($request->full_name);
        
            $disputes->whereHas('customer', function ($query) use ($fullName) {
                if (str_contains($fullName, ' ')) {

                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$fullName}%"])
                            ->orWhere('users.first_name', 'LIKE', "%{$fullName}%");
                } else {
                    $query->where(function ($q) use ($fullName) {
                        $q->where('first_name', 'LIKE', "%{$fullName}%")
                          ->orWhere('last_name', 'LIKE', "%{$fullName}%");
                    });
                }
            });
        }
        if($request->filled('date')){
            $date = explode('to',$request->date);
            if(count($date) === 1){
                $start_date=$date[0];
                $disputes->whereDate('appointment_date', $start_date);
            }
            else {
                $start_date=$date[0];
                $to_date=$date[1];
                $disputes->whereDate('appointment_date', '>=', $start_date);
                $disputes->whereDate('appointment_date', '<=', $to_date);
            }
        }
        $request->filled('status') ? $disputes->where('status',$request->status) : null;
        $disputes =$disputes->with('appointment','customer')->paginate(request('items') ?? 20)->withQueryString();
        $customers = User::role('customer')->orderByDesc('id')->get();
        $total_disputes = Dispute::count();
        $pending_disputes = Dispute::where('status', 1)->count();
        $closed_disputes = Dispute::where('status', 0)->count();

        return view('disputes.index', compact('disputes','customers','total_disputes','pending_disputes','closed_disputes'));
    }
    public function show($id)
    {
        $dispute = Dispute::with('discussions.user')->find($id);
        return view('disputes.show', compact('dispute'));
    }
    public function storeDiscussion(Request $request)
    {
        $message = DisputeDiscussion::create([
            'user_id' => auth()->user()->id,
            'dispute_id' => (int)$request->dispute_id,
            'message' => $request->message,
        ]);

        return redirect()->back();
    }
    public function getDiscussion($disputeId)
    {
        $messages = DisputeDiscussion::where('dispute_id', $disputeId)->get()->map(function ($message) {
            return [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'comment' => $message->message,
                'full_name' => $message->user()->withTrashed()->first()->full_name,
                'profile_picture' => $message->user()->withTrashed()->first()->profile_picture,
                'created_at' => user_tz_format($message->created_at, 'd-M-Y'),
            ];
        });

        return response()->json($messages);
    }
}
