@extends('layouts.app')

@section('content')

    <div class="mx-auto mt-10" x-data="updateModal()">
        <h1 class="text-2xl font-bold mb-4">Welcome, {{ $account->name }}</h1>

        @if (session()->has('masquerading_as'))
            <div class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-blue-800 font-semibold">Masquerading as account #{{ session('masquerading_as') }}</p>
                        <p class="text-sm text-blue-700">Any actions you take will affect this investor.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.investor.stopMasquerade') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">
                            Stop Masquerading
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-8 bg-white shadow rounded p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xl font-semibold">Notifications</h2>
                @if($notifications->count())
                    <form method="POST" action="{{ route('investor.notifications.read_all') }}">
                        @csrf
                        <button type="submit" class="text-sm text-blue-600 hover:underline">Mark all read</button>
                    </form>
                @endif
            </div>
            @if($notifications->isEmpty())
                <p class="text-gray-500 text-sm">No notifications yet.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($notifications as $notification)
                        <li class="py-2 flex items-start justify-between {{ $notification->read_at ? 'text-gray-500' : 'text-gray-900' }}">
                            <div>
                                <p class="text-sm">{{ $notification->message }}</p>
                                <p class="text-xs text-gray-400">{{ $notification->created_at?->format('d M Y H:i') }}</p>
                                @if($notification->link)
                                    <a href="{{ $notification->link }}" class="text-xs text-blue-600 hover:underline">View</a>
                                @endif
                            </div>
                            @if(!$notification->read_at)
                                <form method="POST" action="{{ route('investor.notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 hover:underline">Mark read</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <p class="text-gray-700 mb-4">
            Here’s your investor dashboard. From here, you’ll be able to view your investment history,
            download documents, track payouts, and raise support tickets.
        </p>

        <h2 class="text-3xl mb-8">Your Investments</h2>

        @foreach ($investments as $projectId => $projectInvestments)
            <div class="flex flex-wrap items-center justify-between gap-4 mb-2">
                <h3 class="text-2xl">{{ $projectInvestments->first()->project->name ?? 'Unknown Project' }}</h3>
                <div>
                    <button
                        class="text-sm text-blue-600 hover:underline"
                        onclick="document.getElementById('support-modal-{{ $projectId }}').showModal()"
                    >
                        Raise Support Request
                    </button>
                </div>
            </div>
            @php $project = $projectInvestments->first()->project; @endphp
            @php $documents = $projectDocuments[$projectId] ?? collect(); @endphp
            @php $documentLogs = $projectDocumentLogs[$projectId] ?? collect(); @endphp
            @php $payouts = $projectPayouts[$projectId] ?? collect(); @endphp
            @php $totalPaid = $payouts->where('paid', 1)->sum('amount'); @endphp
            @php $timeline = $projectTimelines[$projectId] ?? null; @endphp

            <div id="project-{{ $projectId }}" class="bg-white rounded shadow overflow-x-auto mb-8">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Documents
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($projectInvestments as $inv)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <p class="text-2xl">{!! money($inv->amount) !!}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ human_date($inv->paid_on) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $inv->type_label }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex flex-wrap gap-3 items-center">
                                        @forelse($documents as $document)
                                            <a href="{{ $document->url }}"
                                               target="_blank"
                                               class="inline-flex flex-col items-center justify-center group"
                                               title="{{ $document->name ?? 'Download document' }}">
                                                <i class="{{ $document->icon }} text-2xl mb-1 group-hover:scale-110 transition-transform"></i>
                                                <span class="text-xs text-gray-600 group-hover:text-gray-900 text-center max-w-[80px] truncate">
                                                    {{ $document->name ?? 'Document' }}
                                                </span>
                                            </a>
                                        @empty
                                            <span class="text-gray-400">No documents yet</span>
                                        @endforelse
                                    </div>
                                    @if($documents->count())
                                        <form method="POST" action="{{ route('investor.documents.email', $project->project_id) }}" class="mt-2">
                                            @csrf
                                            <button type="submit" class="text-xs text-purple-700 underline hover:text-purple-900">
                                                Email me these documents
                                            </button>
                                        </form>
                                    @endif
                                    @if($documentLogs->count())
                                        <div class="mt-3">
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">Recent Document Emails</p>
                                            <ul class="text-xs text-gray-600 space-y-1">
                                                @foreach($documentLogs as $log)
                                                    <li>
                                                        {{ $log->document_name ?? 'Document' }} &middot;
                                                        sent {{ $log->sent_at?->format('d M Y H:i') ?? '' }}
                                                        to {{ $log->recipient }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @php $updates = $projectUpdates[$projectId] ?? null; @endphp
            @if($updates && $updates->count())
                <div class="mb-4 bg-gray-50 border-l-4 border-blue-400 p-4">
                    <h4 class="font-semibold mb-2 text-blue-700">Project Updates</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left">Date</th>
                                    <th class="px-4 py-2 text-left">Update</th>
                                    <th class="px-4 py-2 text-left w-24"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($updates as $update)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap font-semibold">{{ $update->sent_on ? $update->sent_on->format('d M Y') : '' }}</td>
                                        <td class="px-4 py-2">
                                            {!! nl2br(e($update->comment_preview ?? '')) !!}
                                        </td>
                                        <td>
                                            <button 
                                                class="ml-2 text-white bg-indigo-600 hover:bg-indigo-700 text-xs font-semibold py-1 px-2 rounded" 
                                                @click="showUpdate({{ $update->id }})"
                                                type="button"
                                            >Read more</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- Update Modal (global, outside loop) -->
                    <div class="mt-2">
                        {{-- Pagination links with unique page parameter for each project --}}
                        {{ $updates->appends(request()->except('updates_page_' . $projectId))->links() }}
                    </div>
                </div>
            @endif

            @if($payouts->count())
                <div class="mb-8 bg-gray-50 border-l-4 border-green-400 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-green-700">Payout History</h4>
                        <div class="text-sm text-gray-600">
                            Total Paid: {!! money($totalPaid) !!}
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left">Due On</th>
                                    <th class="px-4 py-2 text-left">Amount</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payouts as $payout)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ optional($payout->quarterlyUpdate)->due_on ? $payout->quarterlyUpdate->due_on->format('d M Y') : '—' }}</td>
                                        <td class="px-4 py-2">{!! money($payout->amount ?? 0) !!}</td>
                                        <td class="px-4 py-2">
                                            @if($payout->paid)
                                                <span class="text-green-700 font-semibold">Paid {{ $payout->paid_on ? $payout->paid_on->format('d M Y') : '' }}</span>
                                            @else
                                                <span class="text-yellow-600 font-semibold">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($timeline)
                <div class="mb-8 bg-white border border-gray-100 rounded p-4">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                        <div>
                            <h4 class="text-lg font-semibold">Payment Timeline</h4>
                            <p class="text-sm text-gray-500">From due diligence to payout.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Forecast Payout</p>
                            <p class="text-lg font-semibold">
                                @if($timeline['expected_payout'])
                                    {{ $timeline['expected_payout']->format('d M Y') }}
                                @else
                                    TBC
                                @endif
                            </p>
                            @if($timeline['investment_term'])
                                <p class="text-xs text-gray-500">{{ $timeline['investment_term'] }} month term</p>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($timeline['stages'] as $stage)
                            <div class="flex items-center justify-between px-3 py-2 border rounded {{ $stage['completed'] ? 'border-green-200 bg-green-50' : 'border-gray-100 bg-gray-50' }}">
                                <div>
                                    <p class="text-sm font-semibold {{ $stage['completed'] ? 'text-green-800' : 'text-gray-700' }}">{{ $stage['label'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $stage['date'] ? $stage['date']->format('d M Y') : 'Pending' }}</p>
                                </div>
                                @if($stage['completed'])
                                    <span class="text-green-600 text-lg">✔</span>
                                @else
                                    <span class="text-gray-400 text-lg">•</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <dialog id="support-modal-{{ $projectId }}" class="rounded-lg w-full max-w-2xl p-0">
                <form method="POST" action="{{ route('investor.support.store', $project->project_id) }}">
                    @csrf
                    <div class="p-6 border-b">
                        <h4 class="text-xl font-semibold">Support Request · {{ $project->name }}</h4>
                        <p class="text-sm text-gray-500 mt-1">Tell us what you need help with; the team will reply by email.</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Subject</label>
                            <input type="text" name="subject" required class="w-full border rounded px-3 py-2" placeholder="e.g. Questions about Q3 payout" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Details</label>
                            <textarea name="message" rows="5" required class="w-full border rounded px-3 py-2" placeholder="Give us as much detail as possible..."></textarea>
                        </div>
                    </div>
                    <div class="p-4 border-t flex justify-end gap-3 bg-gray-50 rounded-b-lg">
                        <button type="button" class="px-4 py-2 text-sm text-gray-600" onclick="document.getElementById('support-modal-{{ $projectId }}').close()">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">Send request</button>
                    </div>
                </form>
            </dialog>
            </div>
        @endforeach
    <!-- Modal rendered once, outside the loop -->
    <div 
        x-show="open" 
        style="display: none;" 
        class="fixed inset-0 z-50 flex items-center justify-center"
    >
        <!-- Modal background -->
        <div 
            class="absolute inset-0 bg-black opacity-50 transition-opacity" 
            @click="close"
        ></div>
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-lg max-w-lg w-full p-6 z-10">
            <button @click="close" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">&times;</button>
            <template x-if="loading">
                <div class="text-center py-8">Loading...</div>
            </template>
            <template x-if="!loading && update">
                <div>
                    <div class="mb-2 text-xs text-gray-500" x-text="update.sent_on"></div>
                    <div class="font-bold mb-2">Project Update</div>
                    <div class="prose mb-2" x-html="update.comment"></div>
                </div>
            </template>
        </div>
    </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function updateModal() {
    return {
        open: false,
        loading: false,
        update: null,
        showUpdate(id) {
            this.open = true;
            this.loading = true;
            fetch(`/updates/${id}`)
                .then(r => r.json())
                .then(data => {
                    this.update = data;
                    this.loading = false;
                });
        },
        close() {
            this.open = false;
            this.update = null;
        }
    }
}
</script>
@endpush

@endsection
