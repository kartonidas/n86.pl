@php
    use App\Models\SaleRegister;
    use App\Libraries\Helper;
@endphp
<style>
    *, DIV {
        font-family: Arial;
        font-size: 12px;
    }

    TABLE#head,
    TABLE#items,
    TABLE#footer {
        border-collapse: collapse;
        width: 100%;
    }

    TABLE#head TR TD,
    TABLE#items TR TD,
    TABLE#items TR TH {
        border: 1px solid #000;
        padding: 5px;
        font-family: Arial;
        font-size: 12px;
        font-weight: normal;
    }

    TABLE#footer TR TD {
        border: 1px solid #000;
        height: 80px;
        text-align:center;
        vertical-align: bottom;
        color: #898989;
        font-size: 10px;
    }

    .text-center {
        text-align: center;
    }
</style>

<htmlpagefooter name="myFooter1">
    <table id="footer" width="100%">
        <tr>
            <td width="40%" style="">
                imię, nazwisko i podpis osoby<br/>
                upoważnionej do odebrania dokumentu
            </td>
            <td width="20%" style="border: 0"></td>
            <td width="40%" style="border: 1px solid #000; height: 50px;">
                imię, nazwisko i podpis osoby<br/>
                upoważnionej do podpisania dokumentu
            </td>
        </tr>
    </table>
</htmlpagefooter>
<sethtmlpagefooter name="myFooter1" value="on" />

<table id="head">
    <tr>
        <td rowspan="2" class="text-center" style="width: 25%; vertical-align: bottom; padding: 2px; font-size: 10px; color: #898989">
            pieczęć firmowa
        </td>
        <td colspan="2" style="text-align: center; vertical-align: middle;">
            @if($invoice->type == SaleRegister::TYPE_INVOICE)
                <strong>
                    FAKTURA<br/>
                    {{ $invoice->full_number }}
                </strong>
            @elseif($invoice->type == SaleRegister::TYPE_PROFORMA)
                <strong>
                    FAKTURA PROFORMA<br/>
                    {{ $invoice->full_number }}
                </strong>
            @elseif($invoice->type == SaleRegister::TYPE_CORRECTION)
                <strong>
                    FAKTURA Korygująca<br/>
                    {{ $invoice->full_number }}
                </strong>
                <div style="font-size: 10px;">
                    <br/>
                    Korekta do faktury:<br/>
                    {{ $invoice->getCorrectionSource()["full_number"] }} z dnia: {{ $invoice->getCorrectionSource()["document_date"] }}
                </div>
            @endif
        </td>
        <td rowspan="2" class="text-center" style="width: 25%; vertical-align: middle;">
            <strong>{{ $invoice->sell_date }}</strong>
            <br/>
            Data dostawy / usługi
        </td>
    </tr>
    <tr>
        <td colspan="2" class="text-center; vertical-align: middle;">
            {{ $invoice->document_date }}
            <br/>
            {{ $firm_data->city }}
        </td>
    </tr>

    <tr>
        <td style="width: 25%; vertical-align: top">
            @if($firm_data->type == "firm")
                {{ $firm_data->name }}<br/>
            @else
                {{ $firm_data->firstname }} {{ $firm_data->lastname }}<br/>
            @endif
            {{ $firm_data->street }} {{ $firm_data->house_no }}@if($firm_data->apartment_no) / {{ $firm_data->apartment_no }}@endif<br/>
            {{ $firm_data->zip }} {{ $firm_data->city }}
            @if($firm_data->type == "firm")
                <br/>
                NIP: {{ $firm_data->nip }}
            @endif
        </td>
        <td style="width: 25%; vertical-align: top">
            Nabywca:
            <div>
                {{ $invoice->customer->name }}<br/>
                {{ $invoice->customer->street }} {{ $invoice->customer->house_no }}@if($invoice->customer->apartment_no) / {{ $invoice->customer->apartment_no }}@endif<br/>
                {{ $invoice->customer->zip }} {{ $invoice->customer->city }}<br/>
                NIP: {{ $invoice->customer->nip }}
            </div>
        </td>
        <td style="width: 25%; vertical-align: top">
            Odbiorca:
            <div>
                {{ $invoice->recipient->name }}<br/>
                {{ $invoice->recipient->street }} {{ $invoice->recipient->house_no }}@if($invoice->recipient->apartment_no) / {{ $invoice->recipient->apartment_no }}@endif<br/>
                {{ $invoice->recipient->zip }} {{ $invoice->recipient->city }}<br/>
                NIP: {{ $invoice->recipient->nip }}
            </div>
        </td>
        <td style="width: 25%; vertical-align: top">
            Płatnik:
            <div>
                {{ $invoice->payer->name }}<br/>
                {{ $invoice->payer->street }} {{ $invoice->payer->house_no }}@if($invoice->payer->apartment_no) / {{ $invoice->payer->apartment_no }}@endif<br/>
                {{ $invoice->payer->zip }} {{ $invoice->payer->city }}<br/>
                NIP: {{ $invoice->payer->nip }}
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="text-center" style="border-right: 0">
            Forma płatności: {{ $dictionary["payment_types"][$invoice->payment_type_id]["value"] ?? "" }}
        </td>
        <td colspan="2" class="text-center" style="border-left: 0">
            Termin płatności: {{ $invoice->payment_date }}
        </td>
    </tr>
</table>

@if($invoice->type == SaleRegister::TYPE_CORRECTION)
    <div style="margin-top:50px">
        Przed korektą:
        <table id="items">
            <thead>
                <tr>
                    <th style="width: 40px">Lp.</th>
                    <th>Nazwa i PKWiU</th>
                    <th style="width: 80px">Kod GTU</th>
                    <th style="width: 60px">Ilość</th>
                    <th style="width: 70px">JM</th>
                    <th style="width: 100px">VAT</th>
                    <th style="width: 100px">Cena netto</th>
                    <th style="width: 100px">Wartość netto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items_after_correction as $index => $item)
                    <tr>
                        <td>{{ $index+1 }}.</td>
                        <td>{{ $item->name }}</td>
                        <td class="text-center">{{ $item->gtu }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-center">{{ $item->unit_type }}</td>
                        <td style="text-align: right">{{ $item->vat_value }}{{ is_numeric($item->vat_value) ? "%" : "" }}</td>
                        <td style="text-align: right">
                            @if($item->discount > 0)
                                {{ Helper::amount($item->net_amount_discount) }}
                            @else
                                {{ Helper::amount($item->net_amount) }}
                            @endif
                            {{ $invoice->currency }}
                        </td>
                        <td style="text-align: right">
                            @if($item->discount > 0)
                                {{ Helper::amount($item->net_amount_discount * $item->quantity) }}
                            @else
                                {{ Helper::amount($item->net_amount * $item->quantity) }}
                            @endif
                            {{ $invoice->currency }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div style="margin-top:50px">
    @if($invoice->type == SaleRegister::TYPE_CORRECTION)
        Po korekcie:
    @endif
    <table id="items">
        <thead>
            <tr>
                <th style="width: 40px">Lp.</th>
                <th>Nazwa i PKWiU</th>
                <th style="width: 80px">Kod GTU</th>
                <th style="width: 60px">Ilość</th>
                <th style="width: 70px">JM</th>
                <th style="width: 100px">VAT</th>
                <th style="width: 100px">Cena netto</th>
                <th style="width: 100px">Wartość netto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index+1 }}.</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-center">{{ $item->gtu }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-center">{{ $item->unit_type }}</td>
                    <td style="text-align: right">{{ $item->vat_value }}{{ is_numeric($item->vat_value) ? "%" : "" }}</td>
                    <td style="text-align: right">
                        @if($item->discount > 0)
                            {{ Helper::amount($item->net_amount_discount) }}
                        @else
                            {{ Helper::amount($item->net_amount) }}
                        @endif
                        {{ $invoice->currency }}
                    </td>
                    <td style="text-align: right">
                        @if($item->discount > 0)
                            {{ Helper::amount($item->net_amount_discount * $item->quantity) }}
                        @else
                            {{ Helper::amount($item->net_amount * $item->quantity) }}
                        @endif
                        {{ $invoice->currency }}
                    </td>
                </tr>
            @endforeach

            @if($invoice->type == SaleRegister::TYPE_CORRECTION)
                @include("pdf.customer-invoices._summary-correction")
            @else
                @include("pdf.customer-invoices._summary")
            @endif
        </tbody>
    </table>
</div>

<div style="margin-top: 20px; border: 1px solid #000; padding: 10px">
    @if($invoice->type == SaleRegister::TYPE_CORRECTION)
        {{ Helper::slownie(abs($corrected_invoice->gross_amount - $invoice->gross_amount), $invoice->currency) }}
    @else
        {{ Helper::slownie($invoice->gross_amount, $invoice->currency) }}
    @endif
</div>
