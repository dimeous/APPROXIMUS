@extends('layouts.app')

@section('content')

<div class="container">
    <div class="w-100 text-center text-head" style="background: cyan">
        USD/RUB {{$usd}} • EUR/RUB {{$eur}} • UPDATED {{date("H:i:s",$dt)}}
    </div>
    {{--
    <a href="{{route('update')}}" class="btn btn-success ml-4">Обновить данные</a>
     <input class="form-control" id="myInput" type="text" placeholder="Search..">
     --}}
    <table class="table table-bordered ">
        <tbody id="myTable">
        @foreach($res as $k=>$r)
            <tr class="bg-grey">
                <td>{{ $r['fiat1'] }} </td>
                <td>{{ $r['crypto1'] }}</td>
                <td>{{ $r['fiat2'] }} </td>
                <td>{{ number_format($r['diff'],2)}}</td>
            </tr>
        <tr>
            <td colspan="4">
                @if(Cache::get('checkbox_min_rub'))
                    {{ number_format($r['rate_rub1']*$r['k'],2) }} RUB → {{ number_format($r['rate1']*$r['k'],2) }} {{$r['fiat1']}} → {{$r['k']}} {{ $r['crypto1'] }} → {{number_format($r['rate2']*$r['k'],2) }} {{ $r['fiat2'] }} → {{ number_format($r['rate_rub2']*$r['k'],2) }} RUB </small>
                @else
                    {{ number_format($r['rate_rub1'],2) }} RUB → {{ number_format($r['rate1'],2) }} {{$r['fiat1']}} → 1 {{ $r['crypto1'] }} → {{number_format($r['rate2'],2) }} {{ $r['fiat2'] }} → {{ number_format($r['rate_rub2'],2) }} RUB </small>
                @endif
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
