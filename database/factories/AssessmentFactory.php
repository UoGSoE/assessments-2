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
        return [
            //'deadline' => fake()->dateTimeBetween('now', '+1 month'),
            'deadline' => fake()->date(),
            'type' => fake()->randomElement(['Exam 1', 'Assignment 1', 'Quiz 1']),
            'course_id' => Course::factory(),
            'staff_id' => User::factory()->staff(),
            'feedback_type' => fake()->randomElement(['Moodle', 'Other']),
            //'feedback_deadline' => fake()->dateTimeBetween('now', '+2 month')
            'feedback_deadline' => fake()->date(),
        ];
    }
}

