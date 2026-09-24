<?php

namespace App\Http\Middleware;

use App\Models\PmSchedule;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePmScheduleAssignment
{
    public function handle(Request $request, Closure $next): Response
    {
        // Creating a schedule: a valid technician assignment is mandatory.
        if ($request->routeIs('api.pm.store') || $request->is('api/pm-schedules') && $request->isMethod('post')) {
            $technician = null;

            if ($request->filled('teknisiUserId')) {
                $technician = User::whereKey($request->input('teknisiUserId'))
                    ->where('role', 'teknisi')
                    ->first();
            } elseif ($request->filled('teknisi')) {
                $technician = User::where('role', 'teknisi')
                    ->where('name', $request->input('teknisi'))
                    ->first();
            }

            if (!$technician) {
                return response()->json([
                    'message' => 'Teknisi wajib dipilih dan harus merupakan akun dengan role teknisi.',
                ], 422);
            }
        }

        // A report may only be submitted for a schedule assigned to the
        // currently authenticated technician. This closes the legacy/null
        // assignment loophole in the controller.
        if ($request->route('pmSchedule') instanceof PmSchedule) {
            $schedule = $request->route('pmSchedule');
            if (!$schedule->teknisi_user_id) {
                return response()->json([
                    'message' => 'Jadwal belum memiliki teknisi yang valid. Supervisor harus menetapkan teknisi terlebih dahulu.',
                ], 422);
            }

            if ($schedule->teknisi_user_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'Jadwal ini bukan ditugaskan kepada akun Teknisi yang sedang login.',
                ], 403);
            }
        }

        return $next($request);
    }
}
