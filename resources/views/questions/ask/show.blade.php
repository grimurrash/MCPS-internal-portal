@extends('questions.app')

@section('title')
    Августовский педсовет 2021 - Задать вопрос
@endsection

@section('content')
    <div class="col-12 col-md-10 col-lg-6 mt-4 mt-lg-0 ml-auto mr-auto ml-lg-auto text-left fr-box"
         role="application">
        <div class="fr-wrapper">
            <div class="fr-element fr-view">
                <div class="fr-box">
                    <div class="fr-wrapper">
                        <div class="fr-element fr-view">
                            <h1>Задайте вопрос</h1>
                            {{--                                <p class="lead">Можно добавить дополнительный текст</p></div>--}}
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="fr-box">
                        <div class="fr-wrapper">
                            <div class="fr-element fr-view">
                                <form method="POST" action="{{ route('question.ask.end') }}">
                                    @csrf
                                    <input type="hidden" name="event" value="{{ $event }}">
                                    <div class="input-group mb-2">

                                        <input required name="name" type="text" class="form-control"
                                               placeholder="Введите своё ФИО">
                                    </div>
                                    <div class="input-group mb-2">

                                        <input required name="question" type="text" class="form-control"
                                               placeholder="Введите свой вопрос">
                                    </div>
                                    <div class="input-group mb-2">
                                        <input required name="email" type="email" class="form-control"
                                               placeholder="Введите Email для ответа">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">Отправить</button>
                                        </div>
                                    </div>
{{--                                    <div class="input-group">--}}
{{--                                        <a href="https://t.me/joinchat/LrUy-wY75lBkNzky" style="text-decoration: none" target="_blank">Группа Телеграм для срочных вопросов.</a>--}}
{{--                                    </div>--}}
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <script>
         function checkedTabs() {
             let nowDateTime = (new Date().);
             document.querySelector('.tabs').checked = false
             if (+nowDateTime >= +(new Date('2021-08-24 12:00')) && +nowDateTime < +(new Date('2021-08-24 14:00'))) {
                 document.querySelector('.tabs #tab1').checked = true
             }

             setTimeout(checkedTabs, 10000)
         }

         window.addEventListener('load', () => {
             checkedTabs()
         })

     </script>
@endsection