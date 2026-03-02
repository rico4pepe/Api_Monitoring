<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            API Monitor Dashboard
        </h2>
    </x-slot>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">

            <div class="p-4 bg-white border">
                <div class="text-sm text-gray-500">Total</div>
                <div class="text-xl font-bold">{{ $stats->total}}</div>
            </div>

            <div class="p-4 bg-white border">
                <div class="text-sm text-gray-500">Success</div>
                <div class="text-xl font-bold text-green-600"> {{ $stats->success }}</div>
            </div>

            <div class="p-4 bg-white border">
                <div class="text-sm text-gray-500">Failed</div>
                <div class="text-xl font-bold text-red-600">{{ $stats->failed }}</div>
            </div>

            <div class="p-4 bg-white border">
            <div class="text-sm text-gray-500">Pending</div>
            <div class="text-xl font-bold text-yellow-600">
                {{ $stats->pending}}
            </div>
        </div>

            <div class="p-4 bg-white border">
                <div class="text-sm text-gray-500">Failure %</div>
                <div class="text-xl font-bold">
                    {{  $stats->failure_rate   ?? 0 }}%
                </div>
            </div>

            <div class="p-4 bg-white border">
                <div class="text-sm text-gray-500">Avg Latency</div>
                <div class="text-xl font-bold">
                    {{ $stats->avg_latency  ?? '—' }} ms
                    
                </div>
            </div>
        </div>

         <h2 class="text-lg font-semibold mt-8 mb-2">
            Volume by Service Type
        </h2>

        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th>Service</th>
                    <th>Total</th>
                    <th>Failed</th>
                    <th>Failure %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byService as $row)
                    <tr class="{{ $row->failure_rate > 5 ? 'bg-red-50' : '' }}">
                        <td>{{ $row->service_type }}</td>
                        <td>{{ $row->total }}</td>
                        <td>{{ $row->failed }}</td>
                        <td>{{ $row->failure_rate }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

                <h2 class="text-lg font-semibold mt-8 mb-2">
                Health by Client (Banks)
            </h2>

            <table class="w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th>Client</th>
                        <th>Total</th>
                        <th>Failed</th>
                        <th>Failure %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($byClient as $row)
                        <tr class="{{ $row->failure_rate > 5 ? 'bg-red-50' : '' }}">
                            <td>
                                <a href="{{ route('monitor.client.show', $row->client_code) }}"
                                class="text-blue-600 hover:underline">
                                    {{ $row->client_code }}
                                </a>
                            </td>
                            <td>{{ $row->total }}</td>
                            <td>{{ $row->failed }}</td>
                            <td>{{ $row->failure_rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500">
                                No client activity
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>


        <h2 class="text-lg font-semibold mt-8 mb-2">
            Health by Vendor
        </h2>

        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th>Vendor</th>
                    <th>Total</th>
                    <th>Failed</th>
                    <th>Failure %</th>
                </tr>
            </thead>
            <tbody>
                    @forelse($byVendor as $row)
                    <tr class="{{ $row->failure_rate > 5 ? 'bg-red-50' : '' }}">
                        <td>  <a href="{{ route('monitor.vendor.show', $row->vendor_code) }}"
                            class="text-blue-600 hover:underline">
                                {{ $row->vendor_code }}
                            </a>
                        </td>
                        <td>{{ $row->total }}</td>
                        <td>{{ $row->failed }}</td>
                        <td>{{ $row->failure_rate }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500">
                            No SMS activity in this window
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>


        <h2 class="text-lg font-semibold mt-8 mb-2">
            Raw Status Distribution
        </h2>

        <table class="w-full text-sm border">
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


       



        <h2 class="text-lg font-semibold mt-8 mb-2">
            SMS Volume Trend (last 15 minutes)
        </h2>

        {{-- <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th>Time</th>
                    <th>Total</th>
                    <th>Failed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($smsTrend as $row)
                    <tr>
                        <td>{{ $row->time_bucket }}</td>
                        <td>{{ $row->total }}</td>
                        <td class="{{ $row->failed > 0 ? 'text-red-600 font-semibold' : '' }}">
                            {{ $row->failed }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray-500">
                            No SMS activity in this window
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table> --}}
    </div>

</x-app-layout>

