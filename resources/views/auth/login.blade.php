@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row vh-100">
        <div class="col-sm-12 my-auto mx-auto">
             <div class="">
                <div class="row ">
                    <div class="col-d-4 my-auto mx-auto">
                        <h1 class="text-center align-items-center">APPROXIMUS</h1>
                    </div>
                </div>
                <div class="row  ">
                    <div class="col-d-4 my-auto mx-auto">
                                 <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                                    {{ csrf_field() }}
                                    <div class="form-group row ">
                                           <div class="col-lg-12  " style="display: flex">
                                            <input
                                                    id="password"
                                                    type="password"
                                                    class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                                    name="password"
                                                    required
                                            >

                                                   <button type="submit" class="btn btn-primary">
                                                       <i class="fas fa-angle-right"></i>
                                                   </button>
                                            @if ($errors->has('password'))
                                                <div class="invalid-feedback">
                                                    <strong>{{ $errors->first('password') }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                    </div>
                </div>
         </div>
        </div>
    </div>
</div>
@endsection
