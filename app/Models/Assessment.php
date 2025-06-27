<?php

namespace App\Models;

use App\Models\User;
use App\Models\Course;
use App\Models\Complaint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Assessment extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentFactory> */
    use HasFactory;

    protected $fillable = ['type', 'course_id', 'staff_id', 'deadline', 'submission_window_start', 'submission_window_end', 'feedback_type', 'feedback_deadline', 'feedback_completed_date', 'comment', 'office_notified'];

    protected $casts = [
        'deadline' => 'datetime',
        'feedback_deadline' => 'datetime',
        'feedback_completed_date' => 'datetime',
        'submission_window_start' => 'datetime',
        'submission_window_end' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function isOld(): bool
    {
        return $this->deadline < now()->subDays(90);
    }

    public function studentAlreadyComplained(User $student): bool
    {
        $existingComplaint = Complaint::where('student_id', $student->id)->where('assessment_id', $this->id)->first();
        return $existingComplaint ? true : false;
    }
}
