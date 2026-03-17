<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kake's Trade Routes</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header>
        <div class="logo">Kake's Trade Routes</div>
        <nav style="font-size: 0.8rem; color: #888;">v0.1</nav>
    </header>

    <nav class="main-nav">
        <ul class="nav-links">
            <li><a href="{{ route('routes.index') }}" class="{{ request()->routeIs('routes.*') ? 'active' : '' }}">Routes</a></li>
            <li><a href="{{ route('commodities.index') }}" class="{{ request()->routeIs('commodities.*') ? 'active' : '' }}">Commodities</a></li>
            <li><a href="{{ route('vehicles.index') }}" class="{{ request()->routeIs('vehicles.*') ? 'active' : '' }}">Vehicles</a></li>
        </ul>
    </nav>

    <main>
        {{ $slot }}
    </main>

    <footer>
        &copy; {{ date('Y') }} - Kake's Trade Routes - <a href="https://uexcorp.space/api/documentation" target="_blank">Data from UEX API</a>
    </footer>
</body>
</html>