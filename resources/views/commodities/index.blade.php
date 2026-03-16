<x-layout>
    
    <table class="table-uex">

        <thead>
            <tr>
                <th style="width: 80px;">Code</th>
                <th>Commodity</th>
                <th class="text-right">Buy (UEC)</th>
                <th class="text-right">Sell (UEC)</th>
                <th class="text-right">Profit (UEC)</th>
            </tr>
        </thead>

        <tbody>
            @foreach($commodities as $commodity)

                @php
                    // Bereken het verschil als beide prijzen bestaan
                    $buy = $commodity['price_buy'] ?? 0;
                    $sell = $commodity['price_sell'] ?? 0;
                    $diff = ($buy > 0 && $sell > 0) ? ($sell - $buy) : null;
                @endphp

                <tr>                   
                    <!-- ID -->
                    <td class="code-dim" style="color: #666;">
                        {{ $commodity['code'] }}
                    </td>

                    <!-- Name -->
                    <td style="color: #fff; font-weight: 500;">
                        {{ $commodity['name'] }}
                    </td>

                    <!-- Buy Price -->
                    <td class="text-right col-buy">
                        {{ $buy > 0 ? number_format($buy) : '' }}
                    </td>

                    <!-- Sell Price -->
                    <td class="text-right col-sell">
                        {{ $sell > 0 ? number_format($sell) : '' }}
                    </td>

                    <!-- Profit/Difference -->
                    <td class="text-right col-diff">
                        {{ $diff !== null ? number_format($diff) : '' }}
                    </td>
                </tr>

            @endforeach

        </tbody>

    </table>

</x-layout>