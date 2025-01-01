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
        @foreach ($inductions as $item)
        <tr>
            <td><a href="{{ route('equipment.index') }}/{{ $item->slug }}">{{ $item->name }}</a></td>
            <td>&pound;{{ $item->access_fee }}</td>
            <td>
                @if ($item->userInduction && ($item->userInduction->trained))
                ✔️ {{ $item->userInduction->trained->toFormattedDateString() }}
                @elseif ($item->userInduction && $item->userInduction->paid)
                🕑 Pending
                @else
                <a href="{{ route('equipment.index') }}/{{ $item->slug }}">Book induction on tool page</a>
                @endif
            </td>
            <td>
                @if ($item->access_code)
                    @if ($item->userInduction && ($item->userInduction->trained))
                        <code>{{ $item->access_code }}</code>
                    @else
                        <span>🔒</span>
                    @endif
                @endif
            </td>
            @if (Auth::user()->isAdmin())
                <td>
                    @if ($item->userInduction && !$item->userInduction->trained)
                        Awaiting training
                    @elseif ($item->userInduction && $item->userInduction->trained)
                        {{ $item->userInduction->trainer_user->name ?? 'Inducted' }}
                    @endif
                </td>
            @endif
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
