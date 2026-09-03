<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Динамические метатеги --}}
    <title>{{ $title ?? 'УЮТНЫЙДОМ — аренда домов на берегу Ладоги | Карелия' }}</title>
    <meta name="description" content="{{ $description ?? 'Аренда комфортных домов и квартир на берегу Ладожского озера. Идеально для семейного отдыха в Карелии. Бронируйте онлайн!' }}">
    <meta name="keywords" content="{{ $keywords ?? 'аренда дома ладога, карелия отдых, дом у озера, аренда коттеджа ладожское озеро, отдых в карелии с детьми' }}">
    <meta name="author" content="УЮТНЫЙДОМ">
    <meta name="robots" content="index, follow">

    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $title ?? 'УЮТНЫЙДОМ — аренда домов на берегу Ладоги' }}">
    <meta property="og:description" content="{{ $description ?? 'Аренда комфортных домов и квартир на берегу Ладожского озера.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/og-cover.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="УЮТНЫЙДОМ — аренда домов на берегу Ладоги">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:site_name" content="УЮТНЫЙДОМ">

    {{-- Twitter Cards --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'УЮТНЫЙДОМ — аренда домов на берегу Ладоги' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Аренда домов на берегу Ладожского озера в Карелии.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('images/og-cover.jpg') }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <meta name="theme-color" content="#283e46">

    {{-- ✅ Schema.org для организации (через json_encode) --}}
    @isset($organizationSchema)
        <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
    @endisset

    {{-- ✅ Schema.org для объекта недвижимости (через json_encode) --}}
    @isset($propertySchema)
        <script type="application/ld+json">{!! json_encode($propertySchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
    @endisset

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="app"></div>
</body>
</html>
