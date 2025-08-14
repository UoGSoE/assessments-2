<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
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

    public function canBeAutoSignedOff()
    {
        if ($this->feedback_deadline->gte(Carbon::now())) {
            return false;
        }
        if ($this->feedback_completed_date) {
            return false;
        }
        if ($this->complaints->count() > 0) {
            return false;
        }
        if ($this->feedback_deadline->addDays(21)->gte(Carbon::now())) {
            return false;
        }

        return true;
    }

    public function percentageNegativeFeedbacks()
    {
        if ($this->course->students->count() == 0) {
            return 0;
        }
        if ($this->complaints->count() == 0) {
            return 0;
        }

        return 100.0 / ($this->course->students->count() / $this->complaints->count());
    }

    public function isProblematic()
    {
        if ($this->percentageNegativeFeedbacks() > config('assessments.problematic_threshold_'.$this->course->school)) {
            return true;
        }

        return false;
    }

    public function isLate()
    {
        return $this->feedback_deadline < now() && $this->feedback_completed_date === null;
    }

    public function wasLate()
    {
        return $this->feedback_deadline < $this->feedback_completed_date;
    }

    public function toCalendarEvent($assessmentType = 'assessment')
    {
        $assessmentArray = [
            'id' => $this->id,
            'title' => $this->course->code.' - '.$this->type,
            'start' => $this->deadline->toIso8601String(),
            'end' => $this->deadline->addHours(1)->toIso8601String(),
            'course_code' => $this->course->code,
            'course_title' => $this->course->title,
            'feedback_due' => $this->feedback_deadline->toIso8601String(),
            'discipline' => $this->course->discipline,
            'color' => 'steelblue',
            'textColor' => 'white',
            'url' => route('assessment.show', $this),
            'year' => $this->course->year,
        ];
        if ($assessmentType == 'feedback') {
            $assessmentArray['color'] = 'crimson';
            $assessmentArray['textColor'] = 'white';
            $assessmentArray['title'] = 'Feedback Due: '.$this->course->code.' - '.$this->type;
            $assessmentArray['start'] = $this->feedback_deadline->toIso8601String();
            $assessmentArray['end'] = $this->feedback_deadline->addHours(1)->toIso8601String();
        }

        return $assessmentArray;
    }
}
