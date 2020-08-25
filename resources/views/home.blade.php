@extends('layouts.app')

@section('content')

<div class="container">
    <p><i class="btn btn-dark"><b>USD: </b> {{$usd}}</i>   <i class="btn btn-dark"><b>EUR:</b> {{$eur}}</i>
        Server time:{{ date("Y-m-d H:i:s") }} <a href="{{route('update')}}" class="btn btn-success ml-4">Обновить данные</a>   </p>
    <small>Последняя дата обновления {{date("d.m.Y H:i:s",$dt)}}</small>

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

        <tr>
            <td>{{ $r['fiat1'] }} <small>({{ number_format($r['rate1'],3) }})</small><br><small>{{ number_format($r['rate_rub1'],3) }}</small></td>
            <td>{{ $r['crypto1'] }}</td>
            <td>{{ $r['fiat2'] }} <br><small>{{ number_format($r['rate_rub2'],3) }}</small></td>
            <td>{{ number_format($r['diff'],4)}}</td>

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
