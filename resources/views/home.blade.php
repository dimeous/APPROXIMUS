@extends('layouts.app')

@section('content')

<div class="container">
    <p><i class="btn btn-dark"><b>USD: </b> {{$usd}}</i>   <i class="btn btn-dark"><b>EUR:</b> {{$eur}}</i>    Server time:{{ date("Y-m-d H:i:s") }} <a href="{{route('update')}}" class="btn btn-success ml-4">Обновить данные</a></p>


    <input class="form-control" id="myInput" type="text" placeholder="Search..">
    <br>
    <table class="table table-bordered ">
        <thead>
        <tr>
            <th>Валюта 1</th>
            <th>Валюта 2</th>
            <th>Курс обмена</th>
            <th>Разница %</th>
            <th>RUB</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody id="myTable">
        @foreach($res as $k=>$r)

        <tr>
            <td>{{ $r->Name1 }}</td>
            <td>{{ $r->Name2 }}</td>
            <td>{{ $r->rate }}</td>
            <td>{{ $r->diff }}</td>
            <td>{{ $r->rub_rate }}</td>
            <td>{{ $r->updated_at }}</td>
        </tr>
            @isset($res[$k-1])
                @if(($res[$k-1]->curr2 == $r->curr1) and($res[$k-1]->curr1 == $r->curr2))
                    <tr>
                        <td colspan="6" class="bg-dark"></td>
                    </tr>
                @endif
            @endisset
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
