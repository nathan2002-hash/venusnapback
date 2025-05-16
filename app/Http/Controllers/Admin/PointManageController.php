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

    public function allocations()
    {
        $pointmanagements = PointManage::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.points.manage.allocations', [
           'pointmanagements' => $pointmanagements,
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

            $newBalance = $request->type === 'add'
                ? $currentPoints + $request->points
                : $currentPoints - $request->points;

            $isFailed = false;
            $metadata = [];

            // Handle insufficient balance
            if ($request->type === 'remove' && $currentPoints < $request->points) {
                $isFailed = true;
                $metadata['reason'] = 'Insufficient balance';
                $metadata['current_points'] = $currentPoints;
                $metadata['requested_points'] = $request->points;
            }

            // Create point_manages record regardless
            $pointManage = PointManage::create([
                'user_id'       => $user->id,
                'manage_by'     => Auth::id(),
                'points'        => $request->points,
                'reason'        => $request->reason,
                'type'          => $request->type,
                'status'        => $isFailed ? 'failed' : 'completed',
                'metadata'      => json_encode($metadata),
                'balance_after' => $isFailed ? $currentPoints : $newBalance,
                'description'   => $request->description,
            ]);

            if (!$isFailed) {
                // Only create transaction and update user if successful
                PointTransaction::create([
                    'user_id'       => $user->id,
                    'resource_id'   => $pointManage->id,
                    'points'        => $request->points,
                    'type'          => $request->type,
                    'status'        => 'completed',
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
            }

            DB::commit();

            return back()->with(
                $isFailed ? ['error' => 'User has insufficient points. Action recorded as failed.'] :
                            ['success' => 'User points updated successfully.']
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong.']);
        }
    }

}
