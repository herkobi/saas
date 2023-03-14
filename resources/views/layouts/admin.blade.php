<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.min.css">
        <?php
        if(request()->routeIs('admin.customers')) { ?>
        <link href="https://unpkg.com/bootstrap-table@1.21.3/dist/bootstrap-table.min.css" rel="stylesheet">
        <?php } ?>
        <!-- Scripts -->
        @vite([
            'resources/css/app.css',
            'resources/js/app.js'
        ])
    </head>
    <body class="d-flex flex-column bg-white min-vh-100">
        <header class="site-header bg-white border-bottom">
            @include('admin.partials.header')
        </header>
        <main class="h-100 py-5">
            <div class="container">
                {{ $slot }}
            </div>
        </main>
        <footer class="site-footer bg-white border-top mt-auto py-3">
            @include('admin.partials.footer')
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
        <?php
        if(request()->routeIs('admin.customers')) { ?>
        <script src="https://unpkg.com/bootstrap-table@1.21.3/dist/bootstrap-table.min.js"></script>
        <script src="https://unpkg.com/bootstrap-table@1.21.3/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
        <script src="https://unpkg.com/bootstrap-table@1.21.3/dist/extensions/mobile/bootstrap-table-mobile.min.js"></script>
        <?php } ?>
        <?php
        if(request()->routeIs('admin.settings.contracts.*')) { ?>
        <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
        <script>
        ClassicEditor.create( document.querySelector( '.editor' ), {
                    licenseKey: '',
                } )
                .then( editor => {
                    window.editor = editor;
                } )
                .catch( error => {
                    console.error( 'Oops, something went wrong!' );
                    console.error( 'Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:' );
                    console.warn( 'Build id: shahprfslp1r-itvkwtss2k4f' );
                    console.error( error );
                } );
        </script>
        <?php } ?>
    </body>
</html>
