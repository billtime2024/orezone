<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>orezone - Community Sharing Platform</title>
    <meta name="description"
        content="orezone: Your community sharing platform. Share rides, food, home services, skills, and more. One account, many services — built by the community, for the community.">
    <meta name="keywords"
        content="orezone, community sharing, ride sharing, local services, marketplace, food services, home services, community platform">
    <meta name="author" content="Themesbrand">

    <!-- Social Media Meta Tags -->
    <meta property="og:title" content="orezone - Community Sharing Platform">
    <meta property="og:description"
        content="Share rides, food, home services, skills, and more. One account, many services — built by the community.">
    <meta property="og:image" content="URL to the template's logo or featured image">
    <meta property="og:url" content="URL to the template's webpage">
    <meta name="twitter:card" content="summary_large_image">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('image/favicon.ico') }}">

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body>
    @inertia
</body>

</html>
