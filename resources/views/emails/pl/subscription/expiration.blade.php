@extends("emails.template")

@section("content")
    <p>
        Twój pakiet nieruchomości wygaśnie za {{ $days }} dni.
    </p>
    <p>
        Aby przedłużyć pakiet, kliknij poniższy link.
        <div style="text-align: left; margin-top: 10px; margin-bottom: 10px">
            <a href="{{ env("FRONTEND_URL") }}app/order/prolong" style="display:inline-block; background-color: #506fd9; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">
                Przedłuż pakiet
            </a>
        </div>
    </p>
    <p>
        Z wyrazami szacunku,
        <br/>
        Zespół n86.pl
    </p>
@endsection