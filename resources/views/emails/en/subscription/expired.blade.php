@extends("emails.template")

@section("content")
    <p>
       Your real estate package has expired.
    </p>
    <p>
        You can re-purchase the premium package at any time. To do this, click on the link below:
        <div style="text-align: left; margin-top: 10px; margin-bottom: 10px">
            <a href="{{ env("FRONTEND_URL") }}app/order" style="display:inline-block; background-color: #506fd9; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">
                Buy a premium package
            </a>
        </div>
    </p>
    <p>
        Best regards,
        <br/>
        n86.pl team
    </p>
@endsection