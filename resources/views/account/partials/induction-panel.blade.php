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
            <th>
                @if (Auth::user()->isAdmin())
                Inducted Status
                <span class="label label-danger">Admin</span>
                @endif
            </th>
            <th>
                @if (Auth::user()->isAdmin())
                Can Induct Members
                <span class="label label-danger">Admin</span>
                @endif
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach ($inductions as $item)
        <tr>
            <td>{{ $item->name }}</td>
            <td>&pound;{{ $item->access_fee }}</td>
            <td>
                @if ($item->userInduction && ($item->userInduction->is_trained))
                ✔️ {{ $item->userInduction->trained->toFormattedDateString() }}
                @elseif ($item->userInduction && $item->userInduction->paid)
                🕑 Pending
                @else
                <a href="{{ route('equipment.index') }}/{{ $item->slug }}">Book induction on tool page</a>
                @endif
            </td>
            <td>


                @if (Auth::user()->isAdmin() && $item->userInduction && !$item->userInduction->is_trained)
                {!! Form::open(array('method'=>'PUT', 'route' => ['account.induction.update', $user->id, $item->userInduction->id])) !!}
                {!! Form::text('trainer_user_id', '', ['class'=>'form-control']) !!}
                {!! Form::hidden('mark_trained', '1') !!}
                {!! Form::submit('Inducted By', array('class'=>'btn btn-default btn-xs')) !!}
                {!! Form::close() !!}
                @elseif ($item->userInduction && $item->userInduction->is_trained)
                {{ $item->userInduction->trainer_user->name or 'inducted' }}
                @endif
            </td>
            <td>
                @if (Auth::user()->isAdmin() && $item->userInduction && $item->userInduction->is_trained && !$item->userInduction->is_trainer)
                {!! Form::open(array('method'=>'PUT', 'route' => ['account.induction.update', $user->id, $item->userInduction->id])) !!}
                {!! Form::hidden('is_trainer', '1') !!}
                {!! Form::submit('Make a Trainer', array('class'=>'btn btn-default btn-xs')) !!}
                {!! Form::close() !!}
                @elseif ($item->userInduction && $item->userInduction->is_trained && $item->userInduction->is_trainer)
                Yes
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
