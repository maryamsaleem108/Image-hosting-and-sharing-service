@component('mail::message')
# WELCOME {{$uName}}

@component('mail::button',['url' => 'http://127.0.0.1:8000/api/verified/'.$uEmail.'/'.$uToken])
    Verify
@endcomponent

@component('mail::panel')
    Kindly Verify Your Email Address
@endcomponent
@endcomponent

