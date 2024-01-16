@extends("emails.template")

@section("content")
    <p>
        Thank you for extending the package.
        <br/>
        <br/>
        Details of the order:
        <ul>
            <li>Number of packages purchased: {{ $order->quantity }}</li>
            <li>Total number of packages: {{ $subscription->items }}</li>
            <li>The package will remain valid until: {{ date("Y-m-d H:i:s", $subscription->end) }}</li>
        </ul>
    </p>
    
    <p>
        You will find the invoice for your order after logging in, in the 'Invoices' tab.
    </p>
        
    <p>
        Best regards,
        <br/>
        n86.pl team
    </p>
@endsection