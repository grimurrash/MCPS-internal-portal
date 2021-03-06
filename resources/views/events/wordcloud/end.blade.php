@extends('events.wordcloud.app')

@section('title')
    {{ $wordCloud->pageTitle }} - Ответы на вопросы
@endsection

@section('content')
    <div class="col-12 col-md-10 col-lg-6 mt-4 mt-lg-0 ml-auto mr-auto ml-lg-auto text-left fr-box"
         role="application">
        <div class="fr-wrapper">
            <div class="fr-element fr-view">
                <div class="fr-box">
                    <div class="fr-wrapper">
                        <div class="fr-element fr-view text-white">
                            <h1>Спасибо за ответ!</h1>
                            <p class="lead text-white">Ваш ответ очень важен для нас</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection