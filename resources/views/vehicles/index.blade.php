<x-layout>

    <!-- FILTERS -->
    <filters>
        <div class="filter-group">
            <form method="GET">
                <select name="company" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Manufacturers</option>
                    @php
                        $companies = collect($vehicles)
                            ->pluck('company_name')
                            ->filter()
                            ->unique()
                            ->sort();
                    @endphp
                    @foreach($companies as $company)
                        <option value="{{ $company }}" 
                            {{ request('company') == $company ? 'selected' : '' }}>
                            {{ $company }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </filters>

    <!-- TABLE -->
    <table class="table-uex">
        @php
            $sort = request('sort');
            $direction = request('direction', 'asc');

            function sortDirection($column, $sort, $direction) {
                return $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
            }

            function sortIndicator($column, $sort, $direction) {
                if ($sort === $column) {
                    $arrow = $direction === 'asc' ? '↑' : '↓';
                    // wrap the arrow in a span with class 'yellow'
                    return " <span class='yellow'>{$arrow}</span>";
                }
                return '';
            }

            // Filtered vehicles
            $filteredVehicles = collect($vehicles)
                ->filter(fn($v) => !request('company') || $v['company_name'] === request('company'));

            // Apply sorting
            if ($sort) {
                $filteredVehicles = $direction === 'desc'
                    ? $filteredVehicles->sortByDesc($sort)
                    : $filteredVehicles->sortBy($sort);
            }
        @endphp

        <thead>
            <tr>
                <th>
                    <a href="{{ request()->fullUrlWithQuery([
                        'sort' => 'company_name',
                        'direction' => sortDirection('company_name', $sort, $direction)
                    ]) }}">
                        Manufacturer{!! sortIndicator('company_name', $sort, $direction) !!}
                    </a>
                </th>

                <th>
                    <a href="{{ request()->fullUrlWithQuery([
                        'sort' => 'name',
                        'direction' => sortDirection('name', $sort, $direction)
                    ]) }}">
                        Ship{!! sortIndicator('name', $sort, $direction) !!}
                    </a>
                </th>

                <th>
                    <a href="{{ request()->fullUrlWithQuery([
                        'sort' => 'scu',
                        'direction' => sortDirection('scu', $sort, $direction)
                    ]) }}">
                        Cargo{!! sortIndicator('scu', $sort, $direction) !!}
                    </a>
                </th>

                <th>
                    <a href="{{ request()->fullUrlWithQuery([
                        'sort' => 'price_buy',
                        'direction' => sortDirection('price_buy', $sort, $direction)
                    ]) }}">
                        Price{!! sortIndicator('price_buy', $sort, $direction) !!}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $filteredVehicles = collect($vehicles)
                    ->filter(function ($v) {
                        return !request('company') || $v['company_name'] === request('company');
                    });

                // SORTING
                $sort = request('sort');
                $direction = request('direction', 'asc');

                if ($sort) {
                    $filteredVehicles = $direction === 'desc'
                        ? $filteredVehicles->sortByDesc($sort)
                        : $filteredVehicles->sortBy($sort);
                }
            @endphp
            @foreach($filteredVehicles as $v)
                @php
                    // TYPE
                    $type = $v['is_ground_vehicle'] ? 'Ground' : 'Ship';

                    // CONTAINER SIZES
                    $sizes = !empty($v['container_sizes'])
                        ? array_map('intval', explode(',', $v['container_sizes']))
                        : [];

                    $maxSize = count($sizes) ? max($sizes) : null;
                @endphp

                <tr class="route-row">
                    <td>
                        {{ $v['company_name'] }}
                    </td>

                    <td class="yellow">
                        {{ $v['name'] }}
                    </td>

                    <!-- CARGO -->
                    <td>
                        <span>{{ $v['scu'] ?? 0 }}</span>
                    </td>

                    <td>
                        {{ $v['price_buy'] ? number_format($v['price_buy'], 0) : '-' }}
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>

</x-layout>