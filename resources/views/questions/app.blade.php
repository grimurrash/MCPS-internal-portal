<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body, html {
            margin: 0;
            height: 100%;
            color: rgb(68, 68, 68);
            font-family: Lato, Helvetica, Arial, serif;
        }

        .fr-box {
            display: flex;
            align-items: center;
        }

        main {
            background: #fff;
            height: 100%;
            width: 100%;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .ml-auto {
            margin-left: auto;
        }

        .mr-auto {
            margin-right: auto;
        }

        h1 {
            margin-bottom: .5rem;
            font-family: inherit;
            font-weight: 600;
            line-height: 1.5;
            color: inherit;
            margin-top: .3rem;
            font-size: 2.75rem;
        }

        .lead {
            font-size: 1.4rem;
            font-weight: 400;
            margin: 0;
        }

        p {
            color: #8892a0;
        }

        .fr-wrapper {
            width: 100%;
        }

        #myModal2 .try_fro p {
            color: #fff;
            font-size: 25px;
            margin: 0;
            text-align: center;
        }

        .fr-wrapper p {
            margin: 0;
        }

        .fr-box.fr-inline .fr-wrapper .fr-element.fr-view ul {
            display: inline-block !important;
            list-style: none;
        }

        [role="application"] .fr-view ul {
            display: flex !important;
            list-style: none;
        }

        .fr-view p.logo{
            text-align: center;
        }
        .fr-view p.logo img {
            max-width: 90%;
        }


        .fp-no-block p {
            margin: 0;
        }

        .fp-no-sections p + p {
            font-size: 16px;
            margin-top: 5px;
        }

        .tui-image-editor-container.left .tui-image-editor-submenu .tui-image-editor-submenu-item li {
            margin-top: 8px;
        }

        .tui-image-editor-container.left .tui-image-editor-submenu .tui-image-editor-submenu-item li:nth-child(1) {
            margin-top: -20px;
        }

        .try_fro_content p {
            color: #000;
        }

        .try_fro_content_bottom p {
            color: #000;
            font-size: 14px;
        }

        .try_fro_content_footer ul {
            font-size: 14px;
            padding: 0;
            color: #000;
        }

        .pro-btn button {
            background-color: #0789DC;
            border: none;
            color: #fff;
            padding: 7px 12px;
            border-radius: 2px;
            cursor: pointer;
            margin-top: 7px;
            outline: none !important;
        }

        .download_botton button {
            padding: 10px 25px;
            font-size: 20px;
            font-weight: 700;
            line-height: 25px;
        }

        .try_fro h1 {
            font-weight: 300;
            color: #7DFEFF;
            margin: 0;
            line-height: 47px;
        }

        .try_fro p {
            color: #fff;
            font-size: 35px;
            margin: 0;
        }

        body.fp-add-view [data-block-type] {
            user-select: none;
            position: relative;
        }

        body.fp-add-view section[data-block-type] {
            cursor: move;
        }

        body.fp-add-view [data-block-type]:after {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            display: block;
            z-index: 10000;
            content: "";
        }
        .container>* {
            z-index: 10;
            position: relative;
        }

        .container>.row {
            display: flex;
            align-items: center;
        }

        .container-background {
            position: absolute;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: url("/public/images/backgroud.svg") no-repeat 50%;
            background-size: cover;
            z-index: 1;
            opacity: 1;
        }

        @media only screen and (max-width: 767px) {
            main {
                display: flex;
                align-items: center;
            }

            .fr-view p.logo img {
                max-width: 60%;
            }

            form .input-group {
                display: flex;
                flex-direction: column;
            }

            form .input-group input {
                width: 100% !important;
                margin-bottom: 1em;
            }

            form .input-group .input-group-append {
                text-align: center;
            }
        }

        @media only screen and (max-width: 500px) {
            .fr-view p.logo img {
                max-width: 80%;
            }
        }
    </style>
    <title>@yield('title')</title>
</head>
<body>
<main>
    <div class="container">
        <div class="container-background"></div>
        <div class="row">
{{--            <div class="col-12 col-md-6 m-md-auto ml-lg-0 col-lg-5 fr-box">--}}
{{--                <div class="fr-wrapper">--}}
{{--                    <div class="fr-element fr-view">--}}
{{--                        <p class="logo">--}}
{{--                            <img src="{{ asset('images/logo.svg') }}"--}}
{{--                                 class="img-fluid" alt="image">--}}
{{--                        </p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
            @yield('content')
        </div>
    </div>
</main>
</body>
</html>