<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class);
    }
}
