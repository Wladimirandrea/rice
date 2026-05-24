<?php
// app/Http/Controllers/Api/Admin/AppointmentController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * GET /api/admin/appointments/calendar?month=5&year=2026
     * Retorna citas agrupadas por día con conteo por status
     */
    public function calendar(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $month = $request->get('month', now()->month);
        $year  = $request->get('year',  now()->year);

        $appointments = Appointment::with(['client:id,name', 'caseManager:id,name'])
            ->whereMonth('date', $month)
            ->whereYear('date',  $year)
            ->get();

        $grouped = $appointments->groupBy(fn($a) => $a->date->format('Y-m-d'));

        $calendar = $grouped->map(function ($dayAppts) {
            $counts = [
                'pending'   => 0,
                'confirmed' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'total'     => $dayAppts->count(),
            ];
            foreach ($dayAppts as $a) $counts[$a->status]++;
            return $counts;
        });

        // ← Agregar days off del mes
        $daysOff = \App\Models\DayOff::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->keyBy(fn($d) => $d->date->format('Y-m-d'))
            ->map(fn($d) => [
                'reason'     => $d->reason,
                'start_time' => substr($d->start_time, 0, 5),
                'end_time'   => substr($d->end_time, 0, 5),
            ]);

        return response()->json([
            'month'    => $month,
            'year'     => $year,
            'calendar' => $calendar,
            'days_off' => $daysOff,  // ← nuevo
        ]);
    }

    public function day(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $date      = $request->get('date', now()->format('Y-m-d'));
        $dayOfWeek = (int) date('w', strtotime($date));
        $schedule  = \App\Models\Schedule::where('day_of_week', $dayOfWeek)->first();

        $appointments = Appointment::with([
            'client:id,name,profile_image',
            'caseManager:id,name,profile_image',
        ])
            ->whereDate('date', $date)
            ->orderBy('start_time')
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'date'         => $a->date->format('Y-m-d'),
                'start_time'   => substr($a->start_time, 0, 5),
                'end_time'     => substr($a->end_time, 0, 5),
                'status'       => $a->status,
                'notes'        => $a->notes,
                'client'       => [
                    'id'            => $a->client->id,
                    'name'          => $a->client->name,
                    'profile_image' => $a->client->profile_image_url,
                ],
                'case_manager' => [
                    'id'            => $a->caseManager->id,
                    'name'          => $a->caseManager->name,
                    'profile_image' => $a->caseManager->profile_image_url,
                ],
            ]);

        $caseManagers = $appointments->pluck('case_manager')->unique('id')->values();

        // ── Generar slots disponibles ──────────────────────────
        $availableSlots = [];
        $occupiedSlots = $appointments
            ->where('status', '!=', 'cancelled')
            ->pluck('start_time')
            ->toArray();

        if ($schedule?->is_working && $schedule->start_time && $schedule->end_time) {
            $start = substr($schedule->start_time, 0, 5);
            $end   = substr($schedule->end_time,   0, 5);

            [$sh, $sm] = array_map('intval', explode(':', $start));
            [$eh, $em] = array_map('intval', explode(':', $end));

            $h = $sh;
            $m = $sm;
            while ($h < $eh || ($h === $eh && $m < $em)) {
                $slot = sprintf('%02d:%02d', $h, $m);
                $availableSlots[] = [
                    'time'      => $slot,
                    'available' => !in_array($slot, $occupiedSlots),
                ];
                $m += 30;
                if ($m >= 60) {
                    $m = 0;
                    $h++;
                }
            }
        }

        return response()->json([
            'date'            => $date,
            'appointments'    => $appointments,
            'case_managers'   => $caseManagers,
            'available_slots' => $availableSlots,  // ← nuevo
            'schedule'        => [
                'is_working' => $schedule?->is_working ?? false,
                'start_time' => $schedule?->start_time ? substr($schedule->start_time, 0, 5) : null,
                'end_time'   => $schedule?->end_time   ? substr($schedule->end_time, 0, 5)   : null,
            ],
        ]);
    }

    /**
     * POST /api/admin/appointments
     * Crear una nueva cita
     */
    public function store(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'client_id'       => ['required', 'exists:users,id'],
            'case_manager_id' => ['required', 'exists:users,id'],
            'date'            => ['required', 'date'],
            'start_time'      => ['required', 'date_format:H:i'],
            'status'          => ['required', 'in:pending,confirmed,completed,cancelled'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $validated['end_time'] = date('H:i', strtotime($validated['start_time']) + 1800);

        // ── Verificar conflicto por case manager ──
        $cmConflict = Appointment::where('case_manager_id', $validated['case_manager_id'])
            ->whereDate('date', $validated['date'])
            ->where('start_time', $validated['start_time'] . ':00')
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($cmConflict) {
            return response()->json([
                'message' => __('This case manager already has an appointment at this time.'),
            ], 422);
        }

        // ── Verificar conflicto por cliente ──
        $clientConflict = Appointment::where('client_id', $validated['client_id'])
            ->whereDate('date', $validated['date'])
            ->where('start_time', $validated['start_time'] . ':00')
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($clientConflict) {
            return response()->json([
                'message' => __('This client already has an appointment at this time.'),
            ], 422);
        }

        $appointment = Appointment::create($validated);
        $appointment->load('client:id,name,profile_image', 'caseManager:id,name,profile_image');

        return response()->json([
            'message'     => 'Appointment created successfully.',
            'appointment' => [
                'id'           => $appointment->id,
                'date'         => $appointment->date->format('Y-m-d'),
                'start_time'   => substr($appointment->start_time, 0, 5),
                'end_time'     => substr($appointment->end_time, 0, 5),
                'status'       => $appointment->status,
                'notes'        => $appointment->notes,
                'client'       => [
                    'id'            => $appointment->client->id,
                    'name'          => $appointment->client->name,
                    'profile_image' => $appointment->client->profile_image_url,
                ],
                'case_manager' => [
                    'id'            => $appointment->caseManager->id,
                    'name'          => $appointment->caseManager->name,
                    'profile_image' => $appointment->caseManager->profile_image_url,
                ],
            ],
        ], 201);
    }

    /**
     * PATCH /api/admin/appointments/{appointment}/status
     * Cambiar status de una cita
     */
    public function updateStatus(Request $request, Appointment $appointment): JsonResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        $appointment->update($validated);

        return response()->json(['message' => 'Status updated successfully.']);
    }

    public function slots(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $request->validate([
            'date'            => ['required', 'date'],
            'case_manager_id' => ['required', 'exists:users,id'],
        ]);

        $date        = $request->date;
        $managerId   = $request->case_manager_id;
        $dayOfWeek   = (int) date('w', strtotime($date));
        $schedule    = \App\Models\Schedule::where('day_of_week', $dayOfWeek)->first();

        if (!$schedule?->is_working) {
            return response()->json(['slots' => [], 'is_working' => false]);
        }

        // Slots ocupados por el case manager
        $cmOccupied = Appointment::where('case_manager_id', $managerId)
            ->whereDate('date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('start_time')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        // Slots ocupados por cualquier cliente (para verificar conflicto de cliente)
        $clientOccupied = Appointment::whereDate('date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('start_time')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        // Generar slots del horario
        $slots = [];
        $start = substr($schedule->start_time, 0, 5);
        $end   = substr($schedule->end_time,   0, 5);

        [$sh, $sm] = array_map('intval', explode(':', $start));
        [$eh, $em] = array_map('intval', explode(':', $end));

        $h = $sh;
        $m = $sm;
        while ($h < $eh || ($h === $eh && $m < $em)) {
            $slot = sprintf('%02d:%02d', $h, $m);
            $slots[] = [
                'time'      => $slot,
                'available' => !in_array($slot, $cmOccupied),
                'cm_taken'  => in_array($slot, $cmOccupied),
            ];
            $m += 30;
            if ($m >= 60) {
                $m = 0;
                $h++;
            }
        }

        return response()->json([
            'slots'      => $slots,
            'is_working' => true,
        ]);
    }
}
