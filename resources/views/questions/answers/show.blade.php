@extends('questions.app')

@section('title')
    Августовский педсовет 2021 - Ответы на вопросы
@endsection

@section('content')
    <div class="col-12 col-md-10 col-lg-6 mt-4 mt-lg-0 ml-auto mr-auto ml-lg-auto text-left fr-box"
         role="application">
        <div class="fr-wrapper">
            <div class="fr-element fr-view">
                <div class="row">
                    <div class="col fr-box">
                        <div class="fr-wrapper">
                            <div class="fr-element fr-view">
                                <h1>Вопрос</h1>
                                <p class="lead">{{ $question_text }}</p></div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col fr-box">
                        <div class="fr-wrapper">
                            <div class="fr-element fr-view">
                                <form method="POST" action="{{ route('question.answer.end', $question_id) }}">
                                    @csrf
                                    <div class="input-group">
                                        <input required name="answer" maxlength="50" type="text" class="form-control" placeholder="Введите свой ответ">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">Отправить</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection