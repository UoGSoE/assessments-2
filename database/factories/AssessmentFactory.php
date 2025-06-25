<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
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
        $deadline = fake()->dateTimeBetween('now', '+1 month');
        $feedbackDeadline = fake()->dateTimeBetween($deadline, '+2 month');
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

