<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Vendor Detail: {{ $vendor }}
        </h2>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm p-6">

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <div class="p-4 border">
                <div class="text-sm text-gray-500">Total</div>
                <div class="text-xl font-bold">{{ $stats->total }}</div>
            </div>

            <div class="p-4 border">
                <div class="text-sm text-gray-500">Failed</div>
                <div class="text-xl font-bold text-red-600">{{ $stats->failed }}</div>
            </div>

            <div class="p-4 border">
                <div class="text-sm text-gray-500">Pending</div>
                <div class="text-xl font-bold text-yellow-600">{{ $stats->pending }}</div>
            </div>

            <div class="p-4 border">
                <div class="text-sm text-gray-500">Failure %</div>
                <div class="text-xl font-bold">{{ $stats->failure_rate }}%</div>
            </div>
        </div>

        {{-- Product Breakdown --}}
        <h2 class="text-lg font-semibold mb-2">Product Health</h2>

        <table class="w-full text-sm border mb-8">
            <thead class="bg-gray-100">
                <tr>
                    <th>Product</th>
                    <th>Total</th>
                    <th>Failed</th>
                    <th>Pending</th>
                    <th>Failure %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($byProduct as $row)
                    <tr class="{{ $row->failure_rate > 5 ? 'bg-red-50' : '' }}">
                        <td>{{ $row->product_name }}</td>
                        <td>{{ $row->total }}</td>
                        <td>{{ $row->failed }}</td>
                        <td>{{ $row->pending }}</td>
                        <td>{{ $row->failure_rate }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500">
                            No product activity
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Recent Failures --}}
        <h2 class="text-lg font-semibold mb-2">Recent Failed Transactions</h2>

        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th>Product</th>
                    <th>Phone</th>
                    <th>Raw Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentFailures as $row)
                    <tr>
                        <td>{{ $row->product_name }}</td>
                        <td>{{ $row->phone }}</td>
                        <td>{{ $row->raw_status }}</td>
                        <td>{{ $row->occurred_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500">
                            No recent failures
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</x-app-layout>
