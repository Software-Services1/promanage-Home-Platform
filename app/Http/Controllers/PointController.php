<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PointsService;
use App\Support\WorkTypes;
use Illuminate\Http\Request;

class PointController extends Controller
{
    public function index(Request $request, PointsService $points)
    {
        $month = $this->activeMonth($request);

        $viewer = $request->user();
        $base = $viewer->isManager()
            ? User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))->with('roles')->get()
            : User::where('id', $viewer->id)->with('roles')->get();

        $team = $base
            ->map(function (User $u) use ($points, $month) {
                $u->setAttribute('points', $points->totalPoints($u, $month));
                if ($u->hasRole('supervisor')) {
                    $u->setAttribute('breakdown', $points->supervisorBreakdown($u, $month));
                }
                return $u;
            });

        return view('points.index', [
            'month' => $month, 'team' => $team,
            'designersTotal' => $points->designersTotal($month),
            'taskTypes' => \App\Models\TaskType::map(), 'maintTypes' => WorkTypes::MAINTENANCE,
        ]);
    }
}
