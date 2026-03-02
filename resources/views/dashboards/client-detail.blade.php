<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Client Detail: {{ $client }}
        </h2>
    </x-slot>

    <div class="bg-white rounded-xl shadow-sm p-6">

        {{-- Summary --}}
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

        {{-- Telco Breakdown --}}
        <h2 class="text-lg font-semibold mb-2">Telco Health</h2>

        <table class="w-full text-sm border mb-8">
            <thead class="bg-gray-100">
                <tr>
                    <th>Telco</th>
                    <th>Total</th>
                    <th>Failed</th>
                    <th>Pending</th>
                    <th>Failure %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($byTelco as $row)
                    <tr class="{{ $row->failure_rate > 5 ? 'bg-red-50' : '' }}">
                        <td>{{ $row->vendor_code }}</td>
                        <td>{{ $row->total }}</td>
                        <td>{{ $row->failed }}</td>
                        <td>{{ $row->pending }}</td>
                        <td>{{ $row->failure_rate }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500">
                            No telco activity
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Raw Status --}}
        <h2 class="text-lg font-semibold mb-2">Raw Status Distribution</h2>

        <table class="w-full text-sm border mb-8">
            <thead class="bg-gray-100">
                <tr>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($byRawStatus as $row)
                    <tr>
                        <td>{{ $row->raw_status ?? 'UNKNOWN' }}</td>
                        <td>{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-gray-500">
                            No status data
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Recent Failures --}}
        <h2 class="text-lg font-semibold mb-2">Recent Failed SMS</h2>

        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th>Phone</th>
                    <th>Telco</th>
                    <th>Raw Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentFailures as $row)
                    <tr>
                        <td>{{ $row->phone }}</td>
                        <td>{{ $row->vendor_code }}</td>
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
