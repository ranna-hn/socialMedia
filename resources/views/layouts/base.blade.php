<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="lg:h-full">
    <head>
        @yield('head')
    </head>
    <body class="@yield('body_class', 'font-sans antialiased bg-slate-50 lg:overflow-hidden lg:h-full')">
        @yield('body')
    </body>
</html>
