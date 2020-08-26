@extends('layouts.app')

@section('content')

<div class="container">
    <p><i class="btn btn-dark"><b>USD: </b> {{$usd}}</i>   <i class="btn btn-dark"><b>EUR:</b> {{$eur}}</i>
        now:{{ date("H:i:s") }} <a href="{{route('update')}}" class="btn btn-success ml-4">Обновить данные</a>   </p>
    <small>Последняя дата обновления {{date("H:i:s",$dt)}}</small>

    <input class="form-control" id="myInput" type="text" placeholder="Search..">
    <br>
    <table class="table table-bordered ">
        <thead>
        <tr>
            <th> Fiat 1</th>
            <th>Crypto 1</th>
            <th> Fiat2</th>
            <th>Разница %</th>
        </tr>
        </thead>
        <tbody id="myTable">
        @foreach($res as $k=>$r)
            <tr class="bg-info">
                <td>{{ $r['fiat1'] }} </td>
                <td>{{ $r['crypto1'] }}</td>
                <td>{{ $r['fiat2'] }} </td>
                <td>{{ number_format($r['diff'],2)}} %</td>
            </tr>
        <tr>
            <td colspan="4">
                {{ number_format($r['rate_rub1'],2) }} RUB → {{ number_format($r['rate1'],2) }} → 1 {{ $r['crypto1'] }} → {{number_format($r['rate2'],2) }} {{ $r['fiat2'] }} → {{ number_format($r['rate_rub2'],2) }} RUB </small>
            </td>
        </tr>
        {{--
            @isset($res[$k-1])
                @if(($res[$k-1]->curr2 == $r->curr1) and($res[$k-1]->curr1 == $r->curr2))
                    <tr>
                        <td colspan="6" class="bg-dark"></td>
                    </tr>
                @endif
            @endisset
        --}}
        @endforeach
        </tbody>
    </table>

    <p>Note that we start the search in tbody, to prevent filtering the table headers.</p>
</div>

<script>
    $(document).ready(function(){
        $("#myInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#myTable tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
@endsection
