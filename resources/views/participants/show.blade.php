@extends('participants.app')

@section('title')
    Страница участника {{ $info['name'] }}
@endsection

@section('content')
    <img src="{{ $qrCode }}" alt="">

    <div class="card participant-card">
        <div class="card-body">
            <h4>Сведения об участнике:</h4>
            <p> <b>{{ $info['name'] }}</b></p>
            <p> <b>{{ $info['position'] }}</b></p>
            <p> <b>{{ $info['workOrganisation'] }}</b></p>
{{--            <div class="row">--}}
{{--                <div class="col-9">--}}
{{--                    --}}
{{--                </div>--}}
{{--                <div class="col-3">--}}
{{--                    <div class="panel-info">--}}
{{--                        <p><b>Ряд</b></p>--}}
{{--                        <p><b> {{$info['row']}}</b></p>--}}
{{--                    </div>--}}
{{--                    <br>--}}
{{--                    <div class="panel-info">--}}
{{--                        <p><b>Место</b></p>--}}
{{--                        <p><b>{{$info['place']}}</b> </p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
    </div>
@endsection