<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_id',
        'tanggal',
        'clock_in',
        'clock_out',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'clock_in' => 'datetime:H:i:s',
        'clock_out' => 'datetime:H:i:s',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'alpha' => 'Alpha',
            default => $this->status,
        };
    }

    public function getDurasiAttribute(): ?string
    {
        if ($this->clock_in && $this->clock_out) {
            $diff = Carbon::parse($this->clock_in)->diff(Carbon::parse($this->clock_out));

            return $diff->format('%H jam %I menit');
        }

        return null;
    }

    /**
     * Apakah karyawan terlambat berdasarkan shift yang dipilih.
     */
    public function getIsLateAttribute(): bool
    {
        if (! $this->clock_in || ! $this->shift) {
            return false;
        }

        $clockIn = Carbon::parse($this->clock_in);
        $shiftStart = Carbon::parse($this->shift->start_time)
            ->setDate($clockIn->year, $clockIn->month, $clockIn->day);

        return $clockIn->gt($shiftStart);
    }

    /**
     * Berapa menit keterlambatan.
     */
    public function getLateMinutesAttribute(): int
    {
        if (! $this->is_late) {
            return 0;
        }

        $clockIn = Carbon::parse($this->clock_in);
        $shiftStart = Carbon::parse($this->shift->start_time)
            ->setDate($clockIn->year, $clockIn->month, $clockIn->day);

        return (int) $clockIn->diffInMinutes($shiftStart);
    }

    /**
     * Label keterlambatan yang sudah diformat.
     */
    public function getLateLabelAttribute(): ?string
    {
        if (! $this->is_late) {
            return null;
        }

        $minutes = $this->late_minutes;
        if ($minutes >= 60) {
            $hours = intdiv($minutes, 60);
            $mins = $minutes % 60;

            return "Terlambat {$hours} jam".($mins > 0 ? " {$mins} menit" : '');
        }

        return "Terlambat {$minutes} menit";
    }
}
