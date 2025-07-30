<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'title', 'is_active', 'discipline', 'year', 'school'];

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_student', 'course_id', 'student_id');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_staff', 'course_id', 'staff_id');
    }

    public function scopeForYear($query, $year)
    {
        if (! is_numeric($year)) {
            return $query;
        }

        return $query->where('year', $year);
    }
}
