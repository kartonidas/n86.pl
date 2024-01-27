@extends("emails.template")

@section("title")
    {{ $title }}
@endsection

@section("content")
    <div margin-bottom: 10px;>
        <p>
            Thank you for completing your registration on our website.
            Your account is now active and you can log in.
        </p>
        
        <p>
            To log in to your account, click the link below:
            <div style="text-align: left; margin-top: 10px; margin-bottom: 10px">
                <a href="{{ env("FRONTEND_URL") }}sign-in" style="display:inline-block; background-color: #3B82F6; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">
                    Log in.
                </a>
            </div>
        </p>
    </div>
        
    <p>
        Best regards,
        <br/>
        n86.pl team
    </p>
@endsection