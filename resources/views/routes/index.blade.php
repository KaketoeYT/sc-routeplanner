@php
    //Error and success notifs testing

    // session(['success' => 'Previewing SUCCESS alert']);
    // session(['error' => 'Previewing ERROR alert']);
    // session(['success' => '']);
    // session(['error' => '']);
@endphp

@php
    // cleanup the location name
    function shortenLocation($string) {
        // Take last part after " - "
        $parts = explode(' - ', $string);
        $result = end($parts);

        // Remove parentheses and their contents
        $result = preg_replace('/\s*\(.*?\)/', '', $result);

        return trim($result);
    }
@endphp

<x-layout>
    @if(session('success'))
        <div class="alert-uex alert-success">
            <span class="alert-icon">●</span>
            <div class="alert-content">
                <span class="alert-title">SYSTEM UPDATE</span>
                <span class="alert-msg">{{ session('success') }}</span>
            </div>
            <button class="alert-close">&times;</button> {{-- close button --}}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-uex alert-error">
            <span class="alert-icon">!</span>
            <div class="alert-content">
                <span class="alert-title">SYNC FAILURE</span>
                <span class="alert-msg">{{ session('error') }}</span>
            </div>
            <button class="alert-close">&times;</button> {{-- close button --}}
        </div>
    @endif

    <filters>

        {{-- Ship filter --}}
        <div class="filter-group" data-label="SELECT SHIP">
            <div class="custom-select-wrapper" id="ship-filter-wrapper">
                <div class="custom-select-trigger">
                    <span>ALL SHIPS</span>
                    <div class="arrow"></div>
                </div>
                
                <div class="custom-options">
                    <div class="option selected" data-value="">ALL SHIPS</div>
                    
                    @foreach($vehiclesGrouped as $company => $ships)
                        @php
                            $shipsWithScu = collect($ships)
                                ->filter(fn($v) => ($v['scu'] > 0) && !$v['is_concept']);
                        @endphp

                        @if($shipsWithScu->isNotEmpty())
                            <div class="optgroup-label">// {{ strtoupper($company) }}</div>
                            @foreach($shipsWithScu as $vehicle)
                                <div class="option" data-value="{{ $vehicle['scu'] }}">
                                    {{ $vehicle['name'] }} <span class="opt-scu">({{ $vehicle['scu'] }} SCU)</span>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
                <input type="hidden" id="ship-filter" value="">
            </div>
        </div>

        {{-- Origin Filter --}}
        <div class="filter-group" data-label="ORIGIN">
            <div class="custom-select-wrapper" id="origin-filter-wrapper">
                <div class="custom-select-trigger">
                    <span>
                        @php
                            $originLabel = 'ALL ORIGINS';
                            if ($selectedOrigin) {
                                [, $originValue] = explode('|', $selectedOrigin, 2);
                                $originLabel = shortenLocation($originValue);
                            }
                        @endphp
                        {{ $originLabel }}
                    </span>
                    <div class="arrow"></div>
                </div>

                <div class="custom-options">
                    <div class="option {{ !$selectedOrigin ? 'selected' : '' }}" data-value="">
                        ALL ORIGINS
                    </div>
                    
                    @foreach($origins as $system => $planets)
                        <div class="optgroup-label">// {{ $system }} SYSTEM</div>
                        
                        {{-- SYSTEM OPTION --}}
                        <div class="option option-system" data-value="system|{{ $system }}">
                            {{ $system }}
                        </div>

                        @foreach($planets as $planet => $locations)
                            {{-- PLANET OPTION --}}
                            <div class="option option-planet" data-value="planet|{{ $planet }}">
                                > {{ $planet ? $planet : 'SPACE STATIONS / GATEWAYS' }}
                            </div>

                            @foreach($locations as $location)
                                {{-- TERMINAL OPTION --}}
                                <div class="option option-terminal" data-value="terminal|{{ $location }}">
                                    - {{ shortenLocation($location) }}
                                </div>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
                <input type="hidden" id="origin-filter" value="{{ $selectedOrigin }}">
            </div>
        </div>

        {{-- Destination Filter --}}
        <div class="filter-group" data-label="DESTINATION">
            <div class="custom-select-wrapper" id="destination-filter-wrapper">
                <div class="custom-select-trigger">
                    <span>
                        @php
                            $destinationLabel = 'ALL DESTINATIONS';
                            if ($selectedDestination) {
                                [, $destinationValue] = explode('|', $selectedDestination, 2);
                                $destinationLabel = shortenLocation($destinationValue);
                            }
                        @endphp
                        {{ $destinationLabel }}
                    </span>
                    <div class="arrow"></div>
                </div>

                <div class="custom-options">
                    <div class="option {{ !$selectedDestination ? 'selected' : '' }}" data-value="">
                        ALL DESTINATIONS
                    </div>

                    @foreach($destinations as $system => $planets)
                        <div class="optgroup-label">// {{ $system }} SYSTEM</div>

                        {{-- SYSTEM OPTION --}}
                        <div class="option option-system" data-value="system|{{ $system }}">
                            {{ $system }}
                        </div>

                        @foreach($planets as $planet => $locations)
                            {{-- PLANET OPTION --}}
                            <div class="option option-planet" data-value="planet|{{ $planet }}">
                                > {{ $planet ? $planet : 'SPACE STATIONS / GATEWAYS' }}
                            </div>

                            @foreach($locations as $location)
                                {{-- TERMINAL OPTION --}}
                                <div class="option option-terminal" data-value="terminal|{{ $location }}">
                                    - {{ shortenLocation($location) }}
                                </div>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
                <input type="hidden" id="destination-filter" value="{{ $selectedDestination }}">
            </div>
        </div>

        {{-- investment filter --}}
        <div class="filter-group investment-filter" data-label="MAX INVESTMENT (aUEC)">
            <form method="GET">
                <input 
                    type="number" 
                    name="investment" 
                    class="filter-input"
                    style="width: 100%; box-sizing: border-box;"
                    placeholder="e.g. 500000" 
                    value="{{ request('investment') }}"
                    autocomplete="off"
                >
            </form>
        </div>

        {{-- sync database data --}}
        @php
            $cooldownActive = Cache::has('routes_last_synced');
        @endphp

        <div class="filter-group sync-group" data-label="DATABASE STATUS">
            <div class="sync-controls">
                @if($lastSynced)
                    <span id="sync-timestamp" class="sync-timestamp" data-timestamp="{{ $lastSynced->timestamp }}" style="font-size: 0.75rem; color: #888; display: block; margin-bottom: 5px;">
                        {{ strtoupper($lastSynced->diffForHumans()) }}
                    </span>
                @else
                    <span class="sync-timestamp" style="font-size: 0.75rem; color: #888; display: block; margin-bottom: 5px;">CAN_REFRESH_IN: NOW</span>
                @endif

                <form method="POST" action="{{ route('routes.sync') }}">
                    @csrf
                    <button type="submit" class="btn-sync {{ $cooldownActive ? 'is-cooldown' : '' }}" {{ $cooldownActive ? 'disabled' : '' }} style="width: 100%; padding: 8px; background: #222; border: 1px solid #3d3d3d; color: #f1c40f; cursor: pointer;">
                        <span class="sync-icon"></span>
                        {{ $cooldownActive ? 'COOLDOWN ACTIVE' : 'SYNC DATABASE' }}
                    </button>
                </form>
            </div>
        </div>
    </filters>


    <table class="table-uex">
        <thead>
            <tr>
                <th>Commodity</th>
                <th style="text-align: center;">
                    Route = (Origin <span style="color: #666;">&gt;</span> Destination)
                </th>
                <th class="text-right">Distance</th>
                <th class="text-right">Total Buy</th>
                <th class="text-right">Total Sell</th>
                <th class="text-right">Net Profit</th>
            </tr>
        </thead>

        <tbody>
            @foreach($routes as $route)

                <tr class="route-row"
                    data-route-scu="{{ $route['scu_origin'] }}"
                    data-price-origin="{{ $route['price_origin'] }}"
                    data-price-destination="{{ $route['price_destination'] }}"
                    data-scu-origin="{{ $route['scu_origin'] }}"
                    data-scu-destination="{{ $route['scu_destination'] }}"
                >
                    <td style="color: #fff; font-weight: 500;">
                        {{ $route['commodity_name'] }}
                    </td>

                    <td class="table-flex">
                        <div class="location-content">
                            <div class="location-names">
                                <span class="location-dim">{{ $route['origin_star_system_name'] }}</span> 
                                <span class="location-terminal">{{ $route['origin_planet_name'] }}</span>
                                <span class="yellow">{{ shortenLocation($route['origin_terminal_name']) }}</span>
                            </div>
                            <div class="location-data">
                                <div class="container-box-wrapper">
                                    @php 
                                        $originSizes = explode(',', $route['container_sizes_origin']); 
                                    @endphp
                                    
                                    @foreach($originSizes as $size)
                                        <span class="size-box {{ $loop->last ? 'max-size' : '' }}">
                                            {{ trim($size) }}
                                        </span>
                                    @endforeach
                                </div>
                                <span class="location-scu route-scu-origin">
                                    {{ number_format($route['used_scu']) }} SCU
                                </span>
                            </div>
                        </div>
                    </td>

                    <td class="table-flex">
                        <div class="location-content">
                            <div class="location-names">
                                <span class="location-dim">{{ $route['destination_star_system_name'] }}</span> 
                                <span class="location-terminal">{{ $route['destination_planet_name'] }}</span>
                                <span class="yellow">{{ shortenLocation($route['destination_terminal_name']) }}</span>
                            </div>
                                <div class="location-data">
                                    <div class="container-box-wrapper">
                                        @php 
                                            $originSizes = explode(',', $route['container_sizes_destination']); 
                                        @endphp
                                        
                                        @foreach($originSizes as $size)
                                            <span class="size-box {{ $loop->last ? 'max-size' : '' }}">
                                                {{ trim($size) }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <span class="location-scu route-scu-destination">
                                        {{ number_format($route['used_scu']) }} SCU
                                    </span>
                                </div>
                        </div>
                    </td>

                    <td class="text-right col-distance">
                        {{ $route['distance'] }} GM
                    </td>

                    <td class="text-right col-buy">
                        <span class="route-buy">
                            {{ number_format($route['buy_total']) }}
                        </span>
                    </td>

                    <td class="text-right col-sell">
                        <span class="route-sell">
                            {{ number_format($route['sell_total']) }}
                        </span>
                    </td>

                    <td class="text-right col-diff route-profit">
                        {{ number_format($route['profit']) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>


    {{-- Scripts --}}
    <script>
        document.getElementById('ship-filter').addEventListener('change', function () {

            const shipScu = parseInt(this.value) || Infinity; // Max SCU of selected ship
            const tbody = document.querySelector('.table-uex tbody');
            const rows = Array.from(tbody.querySelectorAll('.route-row')); // convert NodeList to array for sorting

            rows.forEach(row => {

                const routeScuOrigin = parseInt(row.dataset.scuOrigin);
                const routeScuDest   = parseInt(row.dataset.scuDestination);
                const priceOrigin    = parseFloat(row.dataset.priceOrigin);
                const priceDestination = parseFloat(row.dataset.priceDestination);

                // Limit SCU to ship capacity and route max SCU
                const usedScuOrigin = Math.min(shipScu, routeScuOrigin);
                const usedScuDest   = Math.min(shipScu, routeScuDest);

                // Update SCU displayed
                const originScuCell = row.querySelector('.route-scu-origin');
                if (originScuCell) originScuCell.textContent = usedScuOrigin.toLocaleString() + " SCU";

                const destScuCell = row.querySelector('.route-scu-destination');
                if (destScuCell) destScuCell.textContent = usedScuDest.toLocaleString() + " SCU";

                // Use the smaller SCU between origin and destination for profit/buy/sell
                const achievableScu = Math.min(usedScuOrigin, usedScuDest);

                // Update profit
                const profitCell = row.querySelector('.route-profit');
                const profit = (priceDestination * achievableScu) - (priceOrigin * achievableScu);
                if (profitCell) profitCell.textContent = profit.toLocaleString();

                // Store profit in dataset for sorting
                row.dataset.profit = profit;

                // Update total buy/sell
                const buyCell = row.querySelector('.route-buy');
                if (buyCell) buyCell.textContent = (priceOrigin * achievableScu).toLocaleString();

                const sellCell = row.querySelector('.route-sell');
                if (sellCell) sellCell.textContent = (priceDestination * achievableScu).toLocaleString();

            });

            // Sort rows by profit descending
            rows.sort((a, b) => parseFloat(b.dataset.profit) - parseFloat(a.dataset.profit));

            // Append rows back to tbody in sorted order
            rows.forEach(row => tbody.appendChild(row));

        });
    </script>

    {{-- Custom Dropdown --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            function initCustomDropdown(wrapperId, inputId, queryKey) {

                const wrapper = document.getElementById(wrapperId);

                if (!wrapper) return;

                const trigger = wrapper.querySelector('.custom-select-trigger');
                const options = wrapper.querySelectorAll('.option');
                const hiddenInput = document.getElementById(inputId);
                const triggerText = trigger.querySelector('span');

                // Open / close
                trigger.addEventListener('click', () => {
                    wrapper.classList.toggle('open');
                });

                // Click outside
                document.addEventListener('click', (e) => {
                    if (!wrapper.contains(e.target)) {
                        wrapper.classList.remove('open');
                    }
                });

                // Option click
                options.forEach(option => {

                    option.addEventListener('click', function () {

                        const val = this.dataset.value;

                        options.forEach(opt => opt.classList.remove('selected'));

                        this.classList.add('selected');

                        triggerText.innerText = this.innerText;

                        wrapper.classList.remove('open');

                        hiddenInput.value = val;

                        // Preserve existing params
                        const params = new URLSearchParams(window.location.search);

                        if (val) {
                            params.set(queryKey, val);
                        } else {
                            params.delete(queryKey);
                        }

                        window.location.href = `?${params.toString()}`;
                    });

                });
            }

            // Ship
            initCustomDropdown(
                'ship-filter-wrapper',
                'ship-filter',
                'ship_scu'
            );

            // Origin
            initCustomDropdown(
                'origin-filter-wrapper',
                'origin-filter',
                'origin'
            );

            // Destination
            initCustomDropdown(
                'destination-filter-wrapper',
                'destination-filter',
                'destination'
            );

        });
        </script>

    {{-- realtime database cooldown --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const timestampEl = document.getElementById('sync-timestamp');
            const button = document.querySelector('.btn-sync');

            if (!timestampEl) return;

            const cooldownMinutes = 10;
            const cooldownMs = cooldownMinutes * 60 * 1000;
            const lastSynced = parseInt(timestampEl.dataset.timestamp) * 1000; // convert to ms

            function updateCooldown() {
                const now = Date.now();
                const elapsed = now - lastSynced;
                const remaining = cooldownMs - elapsed;

                if (remaining <= 0) {
                    // Cooldown finished
                    timestampEl.textContent = 'CAN_REFRESH_IN: NOW';
                    button.disabled = false;
                    button.classList.remove('is-cooldown');
                    button.textContent = 'SYNC DATABASE'; // <-- add this line
                } else {
                    // Cooldown active, show countdown
                    const minutes = Math.floor(remaining / 60000);
                    const seconds = Math.floor((remaining % 60000) / 1000);
                    timestampEl.textContent = `CAN_REFRESH_IN: ${minutes}:${seconds.toString().padStart(2,'0')}`;
                }
            }

            // initial call
            updateCooldown();

            // update every second
            const interval = setInterval(updateCooldown, 1000);
        });
    </script>

    {{-- close buttons voor success/error notificaties --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const closeButtons = document.querySelectorAll('.alert-close');

            closeButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const alert = btn.closest('.alert-uex');
                    if (alert) {
                        alert.remove(); // remove alert from DOM
                    }
                });
            });
        });
    </script>
</x-layout>