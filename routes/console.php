<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;

Schedule::command('assessments:auto-signoff')->dailyAt('03:00');
Schedule::command('assessments:notify-office-overdue-feedback')->dailyAt('04:00');
Schedule::command('assessments:notify-staff-overdue-feedback')->dailyAt('05:00');
