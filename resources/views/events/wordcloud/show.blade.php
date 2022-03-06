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
                            <h1>Вопрос</h1>
                            <p class="lead text-white">{{ $wordCloud->question }}</p></div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="fr-box">
                        <div class="fr-wrapper">
                            <div class="fr-element fr-view">
                                <form method="POST" action="{{ route('wordCloud.answer.save', $wordCloud->id) }}">
                                    @csrf
                                    <div class="input-group">
                                        <input required name="answer" maxlength="50" type="text" class="form-control"
                                               placeholder="Введите свой ответ">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">Отправить</button>
                                        </div>
                                    </div>
                                    <br>
                                    <small class="text-white">* Ответ должен быть не более 50 символов</small>
                                    <br>
                                    <small class="text-white">** Несколько ответов нужно указывать через запятую</small>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection