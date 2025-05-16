<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\PointManage;
use Illuminate\Http\Request;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PointManageController extends Controller
{
    public function manage()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.points.manage.create', [
           'users' => $users,
        ]);
    }

    public function manageUserPoints(Request $request)
    {
        $request->validate([
            'type'        => 'required|in:add,remove',
            'reason'      => 'required|string',
            'user_id'     => 'required|exists:users,id',
            'description' => 'required|string',
            'points'      => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($request->user_id);
            $currentPoints = $user->points ?? 0;

            // Calculate new balance
            $newBalance = $request->type === 'add'
                ? $currentPoints + $request->points
                : $currentPoints - $request->points;

            // Prevent negative balance
            if ($newBalance < 0) {
                return back()->withErrors(['points' => 'User does not have enough points.']);
            }

            // Create point_manages record
            $pointManage = PointManage::create([
                'user_id'       => $user->id,
                'manage_by'     => Auth::id(),
                'points'        => $request->points,
                'reason'        => $request->reason,
                'type'          => $request->type,
                'status'        => 'Completed',
                'metadata'      => json_encode([]),
                'balance_after' => $newBalance,
                'description'   => $request->description,
            ]);

            // Create point_transactions record
            PointTransaction::create([
                'user_id'       => $user->id,
                'resource_id'   => $pointManage->id,
                'points'        => $request->points,
                'type'          => $request->type,
                'status'        => 'Completed',
                'metadata'      => json_encode([
                    'managed_by' => Auth::user()->email,
                    'reason' => $request->reason,
                ]),
                'balance_after' => $newBalance,
                'description'   => $request->description,
            ]);

            // Update user's points
            $user->points = $newBalance;
            $user->save();

            DB::commit();
            return back()->with('success', 'User points updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong.']);
        }
    }
}
