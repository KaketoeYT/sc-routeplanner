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
        <thead>
            <tr>
                <th>Manufacturer</th>
                <th>Ship</th>
                <th>Cargo</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @php
                $filteredVehicles = collect($vehicles)->filter(function ($v) {
                    return !request('company') || $v['company_name'] === request('company');
                });
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
                        {{-- {{ $v['price_buy'] }} --}}
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>

</x-layout>