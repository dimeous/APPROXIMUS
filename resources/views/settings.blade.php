@extends('layouts.app')

@section('content')

<div class="container">
    <div class="well">

        {!! Form::open(['url' => '/settings', 'class' => 'form-horizontal']) !!}
        <fieldset>
            <legend>Настройки   {!! Form::submit('Сохранить', ['class' => 'btn btn-lg btn-info pull-right'] ) !!}</legend>
            <div class="form-group">
                <div class="form-check">
                        {!! Form::checkbox('checkbox_min_rub',1,(\Cache::get('checkbox_min_rub_sum')?'checked':'')) !!}
                            {!! Form::label('checkbox_min_rub', 'Включить расчет точной суммы в рублях') !!}
                        {!! Form::text('checkbox_min_rub_sum',intval(\Cache::get('checkbox_min_rub_sum'))) !!}
                </div>
            </div>
            <hr>
            <div class="form-group">
                <div class="form-check">
                    {!! Form::checkbox('checkbox_minus',1,(\Cache::get('checkbox_minus')?'checked':'') )!!}
                    {!! Form::label('checkbox_minus', 'Отображать блоки криптовалют с отрицательным процентом') !!}
                </div>
            </div>
            <h3>Fiat валюты</h3>
            <hr>
            <div class="form-group">
                @foreach($fiat as $k=>$r)
                <div class="form-check">
                        {!! Form::checkbox("fiat-$k",$k,(isset($sfiat[$k])?'checked':'')) !!}
                            {!! Form::label("fiat-$k", $currs[$k]->name) !!}
                </div>
                @endforeach
            </div>
                <hr>
                    <h3>Crypto валюты</h3>
            <div class="form-group">
                @foreach($crypto as $k=>$r)
                    <div class="form-check">
                        {!! Form::checkbox("crypto-$k",$k,(isset($scrypto[$k])?'checked':'')) !!}
                        {!! Form::label("crypto-$k", $currs[$k]->name) !!}
                    </div>
                @endforeach
            </div>
            <!-- Submit Button -->
            <div class="form-group">
                <div class="col-lg-10 col-lg-offset-2">
                    {!! Form::submit('Сохранить', ['class' => 'btn btn-lg btn-info pull-right'] ) !!}
                </div>
            </div>

        </fieldset>

        {!! Form::close()  !!}

    </div>
</div>


@endsection
