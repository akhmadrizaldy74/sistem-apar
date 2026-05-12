<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Refill extends Model
{
    use Auditable;
    protected $fillable = ['service_id', 'unit_apar_id', 'jenis_refill_id', 'tgl_refill', 'biaya'];

    protected $casts = [
        'tgl_refill' => 'date',
    ];

    public function unitApar()
    {
        return $this->belongsTo(UnitApar::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function jenisRefill()
    {
        return $this->belongsTo(JenisRefill::class);
    }
}
