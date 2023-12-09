@extends("emails.template")

@section("title")
    {{ $title }}
@endsection

@section("content")
    <div margin-bottom: 10px;>
        <p>
            Aby ustawić nowe hasło, kliknij w poniższy link:
            <div style="text-align: left; margin-top: 10px; margin-bottom: 10px">
                <a href="{{ $url }}" style="display:inline-block; background-color: #3B82F6; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">
                    Ustaw nowe hasło
                </a>
            </div>
        </p>
    </div>
    
    <p style="font-size: 14px">
        Jeśli to nie Ty wnioskowałeś o zmianę hasła lub masz jakiekolwiek wątpliwości, prosimy o niezwłoczny kontakt.
    </p>
        
    <p>
        Z wyrazami szacunku,
        <br/>
        Zespół n86.pl
    </p>
@endsection

