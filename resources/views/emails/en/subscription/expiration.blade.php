@extends("emails.template")

@section("content")
    <p>
        Your property package will expire in {{ $days }} days.
    </p>
    <p>
        To extend your package, please click the link below.
        <div style="text-align: left; margin-top: 10px; margin-bottom: 10px">
            <a href="{{ env("FRONTEND_URL") }}app/order/prolong" style="display:inline-block; background-color: #506fd9; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">
                Extend your package
            </a>
        </div>
    </p>
    <p>
        Best regards,
        <br/>
        n86.pl team
    </p>
@endsection