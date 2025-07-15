<x-mail::message>
# Problematic Assessment

The following assessment has been reported as having late feedback by >30% of students on the course.

<x-mail::button :url="route('assessment.show', $assessment)">
    {{ $assessment->course->code}} {{$assessment->type }} (feedback due {{ $assessment->feedback_deadline->format('d/m/Y') }})
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
