@extends("emails.template")

@section("title")
    {{ $title }}
@endsection

@section("content")
    <div margin-bottom: 10px;>
        <p>
            Thank you for your interest and registration on our website.
            <br/>
            To complete the registration process and use the website, click on the link below.
            After clicking, you will be taken to a page where you will fill in all the necessary data and create your account.
        </p>
    </div>
        
    <div style="text-align: left; margin-top: 10px; margin-bottom: 10px">
        <a href="{{ env("FRONTEND_URL") }}sign-up/confirm/{{ $token->token }}" style="display:inline-block; background-color: #3B82F6; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">
            Confirm your registration
        </a>
    </div>
    
    <p>
        Best regards,
        <br/>
        n86.pl team
    </p>
@endsection