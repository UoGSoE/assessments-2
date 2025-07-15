<x-mail::message>
# Assessment Feedback

There are {{ $complaints->count() }} reports from students about assessment feedback
being overdue.  The details are :

<x-mail::table>
| Course        | Feedback Due  | Student |
| ------------- | ------------- | ------- |
@foreach ($complaints as $complaint)
| {{ $complaint->assessment->course->code }}       | {{ $complaint->assessment->feedback_deadline->format('d/m/Y') }}    | {{ $complaint->student->email }} |
@endforeach
</x-mail::table>

If you believe these to be inaccurate, please email the teaching office.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
