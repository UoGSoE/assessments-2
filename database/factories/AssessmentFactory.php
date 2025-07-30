<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deadline = Carbon::now()->addDays(fake()->numberBetween(1, 14));
        $feedbackDeadline = $deadline->copy();
        $feedbackDeadline->addDays(config('assessments.feedback_grace_days'));
        return [
            'deadline' => $deadline,
            'type' => fake()->randomElement(['Exam 1', 'Assignment 1', 'Quiz 1']),
            'course_id' => Course::factory(),
            'staff_id' => User::factory()->staff(),
            'feedback_type' => fake()->randomElement(['Moodle', 'Other']),
            'feedback_deadline' => $feedbackDeadline,
        ];
    }
}

