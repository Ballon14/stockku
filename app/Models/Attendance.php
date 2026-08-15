<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
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
}
