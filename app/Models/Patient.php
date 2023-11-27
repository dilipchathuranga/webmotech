<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $table = 'patients';

    public function invoice()
    {
        return $this->hasMany(Invoice::class, 'external_patient_id', 'external_patient_id');
    }

    public function receipt()
    {
        return $this->hasMany(Receipt::class, 'external_patient_id', 'external_patient_id');
    }

    public function appointment(): HasMany
    {
        return $this->hasMany(Appointment::class, 'external_patient_id', 'external_patient_id');
    }


}
