<h1 class="text-xl font-semibold mb-4">
        Incidents
    </h1>

    {{-- Open Incidents --}}
    <h2 class="text-lg font-semibold mb-2 text-red-700">
        Open Incidents
    </h2>

    <table class="w-full text-sm border mb-8">
        <thead class="bg-red-50">
            <tr>
                <th>Service</th>
                <th>Scope</th>
                <th>Failure %</th>
                <th>Started</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($openIncidents as $incident)
                <tr class="bg-red-50">
                    <td>{{ $incident->service_type }}</td>
                    <td>
                        {{ $incident->scope_type }}
                        @if($incident->scope_code)
                            ({{ $incident->scope_code }})
                        @endif
                    </td>
                    <td>{{ $incident->failure_rate }}%</td>
                    <td>{{ $incident->started_at->diffForHumans() }}</td>
                    <td class="font-semibold text-red-700">
                        OPEN
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-gray-500">
                        No open incidents 🎉
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Resolved Incidents --}}
    <h2 class="text-lg font-semibold mb-2 text-gray-700">
        Recently Resolved
    </h2>

    <table class="w-full text-sm border">
        <thead class="bg-gray-100">
            <tr>
                <th>Service</th>
                <th>Scope</th>
                <th>Failure %</th>
                <th>Resolved</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resolvedIncidents as $incident)
                <tr>
                    <td>{{ $incident->service_type }}</td>
                    <td>
                        {{ $incident->scope_type }}
                        @if($incident->scope_code)
                            ({{ $incident->scope_code }})
                        @endif
                    </td>
                    <td>{{ $incident->failure_rate }}%</td>
                    <td>{{ $incident->resolved_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-gray-500">
                        No resolved incidents yet
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
