@extends("emails.template")

@section("title")
    {{ $title }}
@endsection

@section("content")
    <div margin-bottom: 10px;>
        <p>
            Dziękujemy za dokończenie rejestracji w naszym serwisie.
            Twoje konto jest już aktywne i możesz się na nie zalogować.
        </p>
        
        <p>
            Aby zalogować się na swoje konto kliknij w poniższy link:
            <div style="text-align: left; margin-top: 10px; margin-bottom: 10px">
                <a href="{{ env("FRONTEND_URL") }}/sign-in" style="display:inline-block; background-color: #3B82F6; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">
                    Zaloguj się.
                </a>
            </div>
        </p>
    </div>
    
    <p>
        Z wyrazami szacunku,
        <br/>
        Zespół n86.pl
    </p>
@endsection