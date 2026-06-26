<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Equipment Inductions</h3>
    </div>
    <div class="panel-body">
        <p>
            Some equipment requires an induction prior to you being able to use it.<br />
            <a href="{{ route('equipment.index') }}">➡️ Visit the equipment page to book inductions</a>
        </p>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Cost</th>
            <th>Induction</th>
            <th>Lone Working</th>
            <th>Access Code</th>
            @if (Auth::user()->isAdmin())
                <th>
                    Inducted Status
                    <span class="label label-danger">Admin</span>
                </th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach ($equipmentRequiringInduction as $item)
        <tr>
            <td><a href="{{ route('equipment.index') }}/{{ $item->slug }}">{{ $item->name }}</a></td>
            <td>&pound;{{ $item->access_fee }}</td>
            <td>
                @if ($item->userTrainingRecord && ($item->userTrainingRecord->trained))
                ✔️ {{ $item->userTrainingRecord->trained->toFormattedDateString() }}
                @elseif ($item->userTrainingRecord && $item->userTrainingRecord->paid)
                🕑 Pending
                @else
                <a href="{{ route('equipment.index') }}/{{ $item->slug }}">Book induction on tool page</a>
                @endif
            </td>
            <td>
                @if (!$item->lone_working)
                    <span class="label label-danger">No lone working</span>
                @endif
            </td>
            <td>
                @if ($item->access_code)
                    @if ($item->userTrainingRecord && ($item->userTrainingRecord->trained))
                        <code>{{ $item->access_code }}</code>
                    @else
                        <span>🔒</span>
                    @endif
                @endif
            </td>
            @if (Auth::user()->isAdmin())
                <td>
                    @if ($item->userTrainingRecord && !$item->userTrainingRecord->trained)
                        Awaiting training
                    @elseif ($item->userTrainingRecord && $item->userTrainingRecord->trained)
                        {{ $item->userTrainingRecord->trainer_user->name ?? 'Inducted' }}
                    @endif
                </td>
            @endif
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
