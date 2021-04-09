@component('mail::message')
# Your News has a new comment

@component('mail::panel')
{{ $comment->content }}
@endcomponent

Written by {{ $comment->nick_name }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
