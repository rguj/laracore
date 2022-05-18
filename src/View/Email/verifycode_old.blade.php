@component('mail::html.message')
<h2><b>Welcome <a href="mailto:{{ $data['to']['email'] }}">{{ $data['to']['email'] }}</a>!</b></h2><br>

Thank you for signing up for <b>{{ $data['from']['name'] }}</b>!<br>

Please verify your email address by clicking the button below.<br>

@component('mail::html.button', ['url' => $data['to']['link_activate']])
Verify my Account<br>
@endcomponent

OR,<br>

Enter this code on the <a href="{{ $data['to']['link_manual'] }}">Verify Page</a>.<br>

@component('mail::html.panel')
<div>{{ $data['to']['code'] }}</div>
@endcomponent

Please note that unverified accounts are automatically deleted in 30 days after sign up.<br>

If you didn't request this, please ignore this email.<br>

<hr>

Yours,<br>
{{ $data['from']['name_desc'] }}<br>
<a href="mailto:{{ $data['from']['email'] }}">{{ $data['from']['email'] }}</a><br>
@endcomponent