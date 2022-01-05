<!DOCTYPE html>
<html :class="{ 'dark': dark }" x-data="data()" lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" href="{{ url('storage/'.config('constants.site_icon')) }}" type="image/gif" sizes="16x16">
        {{-- Title Goes Here --}}
        <title>
            @yield('title', 'Provider Dasboard')
        </title>

        <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/normalize.css') }}" />

        {{-- Font Awesome --}}
        <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">

        {{-- Ratings --}}
        <link rel="stylesheet" href="{{ asset('css/starability-all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/starability-slot.min.css') }}">

        {{-- Alpine --}}
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
        <script src="{{ asset('js/init-alpine.js') }}"></script>

        {{-- Main Script --}}
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/user/main.js') }}"></script>
        
        @stack('startScripts')

        <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('constants.map_key') }}&libraries=places,geocoding"></script>
        
        <style>
            [x-cloak] { display: none; }
        </style>
        
        @livewireStyles
    </head>
    <body class="antialiased bg-body text-body font-body dark:bg-gray-900" onload="contentLoaded()">
        <style>
            #loading {
                position: fixed;
                top: 0px;
                left: 0px;
                width: 100%;
                height: 100vh;
                background: white url('{{ asset('img/loader2.gif') }}') no-repeat center;
                background-size: 150px;
                z-index: 99999;
            }
            .theme {
                background-color: {{ config('constants.light_theme') }};
            }
        </style>
        <div id="loading"></div>
        @include('provider.layout.nav')
        <div class="relative" style="min-height: 94vh;">
            <section class="pt-28 pb-10 h-full">
                <main>
                    <div class="grid">
                        @yield('content')
                    </div>
                </main>
                <div>
                    @livewire('provider.get-new-data')
                </div>
            </section>
        </div>

        @include('provider.layout.footer')


        {{-- Notification --}}
        <script src="{{ asset('js/notyf.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('css/notyf.css') }}">

        <script>
            const notyf = new Notyf({
                duration: 2500,
                position: {
                    x: 'right',
                    y: 'top',
                },
                dismissible: true,
            });
            @if($errors->any())
                @foreach($errors->all() as $error)
                    notyf.error("{{ $error }}");
                @endforeach
            @endif
            @if(session()->has('success'))
                notyf.success("{{ session()->get('success') }}");
            @endif
        </script>

        {{-- Livewire Scripts --}}
        @livewireScripts

        {{-- Other Scripts From Childrens --}}
        @stack('endScripts')

        <script src="{{ asset('js/provider/requestHandler.js') }}"></script>
        <style>
            
            .bell{
                -webkit-animation: ring 4s .7s ease-in-out infinite;
                -webkit-transform-origin: 50% 4px;
                -moz-animation: ring 4s .7s ease-in-out infinite;
                -moz-transform-origin: 50% 4px;
                animation: ring 4s .7s ease-in-out infinite;
                transform-origin: 50% 4px;
            }

            @-webkit-keyframes ring {
                0% { -webkit-transform: rotateZ(0); }
                1% { -webkit-transform: rotateZ(30deg); }
                3% { -webkit-transform: rotateZ(-28deg); }
                5% { -webkit-transform: rotateZ(34deg); }
                7% { -webkit-transform: rotateZ(-32deg); }
                9% { -webkit-transform: rotateZ(30deg); }
                11% { -webkit-transform: rotateZ(-28deg); }
                13% { -webkit-transform: rotateZ(26deg); }
                15% { -webkit-transform: rotateZ(-24deg); }
                17% { -webkit-transform: rotateZ(22deg); }
                19% { -webkit-transform: rotateZ(-20deg); }
                21% { -webkit-transform: rotateZ(18deg); }
                23% { -webkit-transform: rotateZ(-16deg); }
                25% { -webkit-transform: rotateZ(14deg); }
                27% { -webkit-transform: rotateZ(-12deg); }
                29% { -webkit-transform: rotateZ(10deg); }
                31% { -webkit-transform: rotateZ(-8deg); }
                33% { -webkit-transform: rotateZ(6deg); }
                35% { -webkit-transform: rotateZ(-4deg); }
                37% { -webkit-transform: rotateZ(2deg); }
                39% { -webkit-transform: rotateZ(-1deg); }
                41% { -webkit-transform: rotateZ(1deg); }

                43% { -webkit-transform: rotateZ(0); }
                100% { -webkit-transform: rotateZ(0); }
            }

            @-moz-keyframes ring {
                0% { -moz-transform: rotate(0); }
                1% { -moz-transform: rotate(30deg); }
                3% { -moz-transform: rotate(-28deg); }
                5% { -moz-transform: rotate(34deg); }
                7% { -moz-transform: rotate(-32deg); }
                9% { -moz-transform: rotate(30deg); }
                11% { -moz-transform: rotate(-28deg); }
                13% { -moz-transform: rotate(26deg); }
                15% { -moz-transform: rotate(-24deg); }
                17% { -moz-transform: rotate(22deg); }
                19% { -moz-transform: rotate(-20deg); }
                21% { -moz-transform: rotate(18deg); }
                23% { -moz-transform: rotate(-16deg); }
                25% { -moz-transform: rotate(14deg); }
                27% { -moz-transform: rotate(-12deg); }
                29% { -moz-transform: rotate(10deg); }
                31% { -moz-transform: rotate(-8deg); }
                33% { -moz-transform: rotate(6deg); }
                35% { -moz-transform: rotate(-4deg); }
                37% { -moz-transform: rotate(2deg); }
                39% { -moz-transform: rotate(-1deg); }
                41% { -moz-transform: rotate(1deg); }

                43% { -moz-transform: rotate(0); }
                100% { -moz-transform: rotate(0); }
            }

            @keyframes ring {
                0% { transform: rotate(0); }
                1% { transform: rotate(30deg); }
                3% { transform: rotate(-28deg); }
                5% { transform: rotate(34deg); }
                7% { transform: rotate(-32deg); }
                9% { transform: rotate(30deg); }
                11% { transform: rotate(-28deg); }
                13% { transform: rotate(26deg); }
                15% { transform: rotate(-24deg); }
                17% { transform: rotate(22deg); }
                19% { transform: rotate(-20deg); }
                21% { transform: rotate(18deg); }
                23% { transform: rotate(-16deg); }
                25% { transform: rotate(14deg); }
                27% { transform: rotate(-12deg); }
                29% { transform: rotate(10deg); }
                31% { transform: rotate(-8deg); }
                33% { transform: rotate(6deg); }
                35% { transform: rotate(-4deg); }
                37% { transform: rotate(2deg); }
                39% { transform: rotate(-1deg); }
                41% { transform: rotate(1deg); }

                43% { transform: rotate(0); }
                100% { transform: rotate(0); }
            }
        </style>
        <script>
            function contentLoaded() {
                var load = document.getElementById('loading');
                load.style.display = 'none';
            }
        </script>
    </body>
</html>
