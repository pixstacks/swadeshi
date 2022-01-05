<!DOCTYPE html>
<html :class="{ 'dark': dark }" x-data="data()" lang="en">
    <head>
        <title>
            @yield('title')
        </title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="{{ url('storage/'.config('constants.site_icon')) }}" type="image/gif" sizes="16x16">
        <link rel="stylesheet" href="{{ asset('css/user/starter.min.css') }}">

        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
        <script src="{{ asset('js/init-alpine.js') }}"></script>
        
        {{-- Font Awesome --}}
        <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        @stack('startScripts')
        <style>
            [x-cloak] { display: none; }
        </style>
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

        @if(auth('provider')->user())
            @include('provider.layout.nav')
        @else
            @include('user.layout.nav')
        @endif

        <div class="relative" style="min-height: 94vh;">
            <section class="pt-28 pb-10">
                <main>
                    <div class="mx-auto grid">
                        @yield('content')
                    </div>
                </main>
            </section>
        </div>
        
        @include('user.layout.footer')

        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        
        <link rel="stylesheet" href="{{ asset('css/normalize.css') }}">
        <script src="{{ asset('js/user/main.js') }}"></script>

        {{-- Alpine --}}
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
        <script src="{{ asset('js/init-alpine.js') }}"></script>

        {{-- Livewire Styles --}}
        @livewireStyles

        {{-- Notification --}}
        <script src="{{ asset('js/notyf.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('css/notyf.css') }}">

        @stack('frontScripts')
        
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

        @auth('web')
            @livewire('user.get-new-data')
        @endauth
        
        {{-- Livewire Scripts --}}
        @livewireScripts

        {{-- Other Scripts From Childrens --}}
        @stack('endScripts')

        <script>
            // Listening for livewire event.
            Livewire.on('livewire_success', function(msg) {
                notyf.success(msg);
            });
            Livewire.on('livewire_error', function(msg) {
                notyf.error(msg);
            });
            
            function contentLoaded() {
                var load = document.getElementById('loading');
                load.style.display = 'none';
            }
        </script>
    </body>
</html>
