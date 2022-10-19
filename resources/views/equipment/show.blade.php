@extends('layouts.main')

@section('page-title')
<a href="{{ route('equipment.index') }}">Tools &amp; Equipment</a> > {{ $equipment->name }}
@stop

@section('meta-title')
Tools and Equipment
@stop

@section('page-action-buttons')
    @if (!Auth::guest() && !Auth::user()->online_only)
        <a class="btn btn-secondary" href="{{ route('equipment.edit', $equipment->slug) }}">Edit</a>
    @endif
@stop

@section('main-tab-bar')

@stop


@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="well">
            <div class="row">
                <div class="col-sm-6">
                    <h2>{{ $equipment->name }}</h2>
                    @if ($equipment->requiresInduction())<b style="color:red">⚠️ Induction required</b><br /> @endif
                    @if ($equipment->present()->livesIn) 🏠 Lives in: {{ $equipment->present()->livesIn }}<br />@endif
                    @if ($equipment->present()->manufacturerModel) 🔧 Make: {{ $equipment->present()->manufacturerModel }}<br />@endif
                    @if ($equipment->present()->purchaseDate) Purchased: {{ $equipment->present()->purchaseDate }}<br />@endif
                    @if ($equipment->hasUsageCharge())
                        💵 Usage Cost: {!! $equipment->present()->usageCost() !!}<br />
                    @endif
                    @if ($equipment->isManagedByGroup())
                        🤗 Managed By: <a href="{{ route('groups.show', $equipment->role->name) }}">{{ $equipment->role->title }}</a>
                    @endif
                    @if ($equipment->isPermaloan())<h4><span class="label label-warning">Permaloan</span></h4>@endif
                    @if (!$equipment->isWorking())<h4><span class="label label-danger">Out of action</span></h4>@endif
                    
                    @if ($equipment->isDangerous())
                        <div style="padding: 1em;
                            color: white;
                            font-weight: bold;
                            background: repeating-linear-gradient( 45deg, #f00, #f00 10px, #e00 10px, #e00 20px );
                            margin: 1em 0;
                            border: 3px solid #e00;
                            ">
                            This Tool Is Bloody Dangerous
                        </div>
                    @endif

                    @if ($equipment->hasPhoto())
                        <h3>Equipment photos</h3>
                        <p>Select a photo to enlarge it</p>
                        @for($i=0; $i < $equipment->getNumPhotos(); $i++)
                            <img src="{{ $equipment->getPhotoUrl($i) }}" width="120" data-toggle="modal" data-target="#image{{ $i }}" style="margin:3px 1px; padding:0;" />

                            <div class="modal fade" id="image{{ $i }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-body">
                                            <img src="{{ $equipment->getPhotoUrl($i) }}" width="100%" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    
                    @endif
                </div>
                <div class="col-sm-6">
                    @if ($equipment->requiresInduction())
                            <h3>Induction @if (!$userInduction)required @endif</h3>
                            @if (!$userInduction)
                                <p>
                                ⚠️ An induction is required before you may use this tool. The access fee goes towards equipment maintenance.<br />
                                <strong>Equipment access fee: &pound{{ $equipment->access_fee }}</strong><br />
                                </p>
    
                                @if(Auth::user()->online_only)
                                    <h4>Online Only users may not sign up for tools</h4>
                                @else
                                    <div class="paymentModule" data-reason="induction" data-display-reason="Equipment Access Fee" data-button-label="{{ $equipment->access_fee == 0 ? 'Book Free Induction' : 'Pay Induction Fee' }}" data-methods="balance" data-amount="{{ $equipment->access_fee }}" data-ref="{{ $equipment->induction_category }}"></div>
                                @endif
    
                            @elseif ($userInduction->is_trained)
                                <h4>
                                    <span class="label label-success">You have been inducted and can use this equipment</span>
                                </h4>
    
                            @elseif ($userInduction)
                                <h4>
                                    <span class="label label-info">Access fee paid, induction to be completed</span>
                                </h4>
                            @endif
                    @endif
                    @if ($userInduction)
                        @if ($userInduction->is_trained)
                            @if ($userInduction->is_trainer && $equipment->trainer_instructions)
                                <div style="border-left: 3px solid tomato; padding-left: 1em;">
                                    <h4>Trainer Instructions</h4>
                                    <p>{{ $equipment->trainer_instructions }}</p>
                                    <br/>
                                </div>
                            @endif

                            @if ($equipment->trained_instructions)
                                <div style="border-left: 3px solid green; padding-left: 1em;">
                                    <h4>Instructions for Use</h4>
                                    <p>{{ $equipment->trained_instructions }}</p>
                                    <br/>
                                </div>
                            @endif

                            @if (in_array($equipment->slug, ['laser', 'laser-1', 'printer-lfp-1', '3dprint-mendel90', '3dprint-mendelmax', 'vac-former-1', 'ultimaker']))
                                <div style="border-left: 3px solid burlywood; padding-left: 1em;">
                                    <h4>Pay for usage</h4>
                                    <p>
                                        Make a payment for your usage of this equipment below.
                                    </p>

                                    <div class="paymentModule" data-reason="equipment-fee" data-display-reason="Usage Fee" data-button-label="Pay Now" data-methods="balance" data-ref="{{ $equipment->slug }}"></div>
                                </div>
                            @endif
                        @else
                            @if ($equipment->induction_instructions)
                                <div style="border-left: 3px solid red; padding-left: 1em;">
                                    <h4>🔴 Induction Next Steps</h4>
                                    <p>{{ $equipment->induction_instructions }}</p>
                                    <br/>
                                </div>
                            @endif
                        @endif
                    @endif
                </div>
            </div>

            <hr/>

            {!! $equipment->present()->description !!}
            <br />

            @if ($equipment->help_text || $equipment->docs)
                <a data-toggle="modal" data-target="#helpModal" href="#" class="btn btn-info">View Documentation and Help</a>
                <br /><br />
            @endif

            <h3>Personal Protective Equipment</h3>
            @if(strlen($equipment->present()->ppe) > 20)
                <p>The following PPE is required</p>
                {!! $equipment->present()->ppe !!}
            @else
                <p>No specific PPE is required. You must still be aware of risks and use relevent PPE to mitigate those risks.</p>
            @endif

        </div>
    </div>

</div>

@if ($equipment->requiresInduction())
    <div class="row">
        <div class="col-sm-12 col-md-4">
            <div class="row">
            <h3>🎓 Trainers/Maintainers</h3>
            <p>These people can train others and maintain the tool.</p>
            <div class="list-group">
                @foreach($trainers as $trainer)
                    <div class="list-group-item">
                        <a href="{{ route('members.show', $trainer->user->id) }}">
                            {!! HTML::memberPhoto($trainer->user->profile, $trainer->user->hash, 25, '') !!}
                        </a>
                        {{ $trainer->user->name }}
                        @if ($isTrainerOrAdmin)
                            {!! Form::open(array('method'=>'PUT', 'style'=>'display:inline;float:right;', 'route' => ['account.induction.update', $trainer->user->id, $trainer->id])) !!}
                            {!! Form::hidden('not_trainer', '1') !!}
                            {!! Form::hidden('slug', $equipment->slug) !!}
                            {!! Form::submit('❌', array('class'=>'btn btn-default btn-xs')) !!}
                            {!! Form::close() !!}
                        @endif
                    </div>

                @endforeach
            </div>
            </div>
        </div>
    
        <div class="col-sm-12 col-md-4">
            <h3>✔️ Trained Users</h3>
            <p>These people are trained to use this tool</p>

            <div class="list-group">
                @foreach($trainedUsers as $trainedUser)
                    <div class="list-group-item">
                        <a href="{{ route('members.show', $trainedUser->user->id) }}">
                            {!! HTML::memberPhoto($trainedUser->user->profile, $trainedUser->user->hash, 25, '') !!}
                            {{ $trainedUser->user->name }}
                        </a>
                        @if ($isTrainerOrAdmin)
                            {!! Form::open(array('method'=>'PUT', 'style'=>'display:inline;float:right;', 'route' => ['account.induction.update', $trainedUser->user->id, $trainedUser->id])) !!}
                            {!! Form::hidden('mark_untrained', '1') !!}
                            {!! Form::hidden('slug', $equipment->slug) !!}
                            {!! Form::submit('❌', array('class'=>'btn btn-default btn-xs')) !!}
                            {!! Form::close() !!}

                            {!! Form::open(array('method'=>'PUT', 'style'=>'display:inline;float:right;', 'route' => ['account.induction.update', $trainedUser->user->id, $trainedUser->id])) !!}
                            {!! Form::hidden('is_trainer', '1') !!}
                            {!! Form::hidden('slug', $equipment->slug) !!}
                            {!! Form::submit('🎓', array('class'=> $trainedUser->is_trainer ? 'btn btn-xs disabled' : 'btn btn-xs btn-default')) !!}
                            {!! Form::close() !!}
                        
                        @endif

                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-sm-12 col-md-4">
            <h3>🛂 Awaiting Inductions</h3>
            <p>People who have requested an induction</p>
            <div class="list-group">
                @foreach($usersPendingInduction as $trainedUser)
                    <div class="list-group-item">
                        <a href="{{ route('members.show', $trainedUser->user->id) }}">
                            {!! HTML::memberPhoto($trainedUser->user->profile, $trainedUser->user->hash, 25, '') !!}
                            {{ $trainedUser->user->name }}
                        </a>
                        @if ($isTrainerOrAdmin)
                            {!! Form::open(array('method'=>'DELETE', 'style'=>'display:inline;float:right;', 'route' => ['account.induction.destroy', $trainedUser->user->id, $trainedUser->id])) !!}
                            {!! Form::hidden('trainer_user_id', Auth::user()->id) !!}
                            {!! Form::hidden('slug', $equipment->slug) !!}
                            {!! Form::submit('❌', array('class'=>'btn btn-default btn-xs')) !!}
                            {!! Form::close() !!}
                            {!! Form::open(array('method'=>'PUT', 'style'=>'display:inline;float:right;', 'route' => ['account.induction.update', $trainedUser->user->id, $trainedUser->id])) !!}
                            {!! Form::hidden('trainer_user_id', Auth::user()->id) !!}
                            {!! Form::hidden('mark_trained', '1') !!}
                            {!! Form::hidden('slug', $equipment->slug) !!}
                            {!! Form::submit('✔️', array('class'=>'btn btn-default btn-xs')) !!}
                            {!! Form::close() !!}
                        @endif
                    </div>
                @endforeach
                @if ($isTrainerOrAdmin)
                    <div class="list-group-item">
                        <p>Add a member</p>
                        {!! Form::open(array('method'=>'POST', 'route' => ['equipment_training.create'])) !!}
                        {!! Form::select('user_id', [''=>'Add a member']+$memberList, null, ['class'=>'form-control js-advanced-dropdown']) !!}
                        {!! Form::hidden('slug', $equipment->slug) !!}
                        {!! Form::submit('✔️', array('class'=>'btn btn-default btn-xs')) !!}
                        {!! Form::close() !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
<br/>
<div class="row">
    <div class="col col-sm-12">
        <div class="panel panel-warning">
            <div class="panel-heading">Incorrect information?</div>
            <div class="panel-body">If something is wrong or missing on this page, please raise the issue on the Forum or Telegram.</div>
        </div>
    </div>
</div>

    @if ($equipment->hasActivity())
    <h3>Activity Log</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Used for</th>
                <th>Member</th>
                <th>Reason</th>
                @if (Auth::user()->isAdmin() || Auth::user()->hasRole($equipmentId))
                <th></th>
                @endif
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="5">
                    <strong>Total times in minutes:</strong>
                    Billed: {{ number_format($usageTimes['billed']) }} |
                    Unbilled: {{ number_format($usageTimes['unbilled']) }} |
                    Training: {{ number_format($usageTimes['training']) }} |
                    Testing: {{ number_format($usageTimes['testing']) }}
                </td>
            </tr>
        </tfoot>
        <tbody>
        @foreach($equipmentLog as $log)
            <tr>
                <td>{{ $log->present()->started }}</td>
                <td>{{ $log->present()->timeUsed }}</td>
                <td><a href="{{ route('members.show', $log->user->id) }}">{{ $log->user->name }}</a></td>
                <td>{{ $log->present()->reason }}</td>
                @if (Auth::user()->isAdmin() || Auth::user()->hasRole($equipmentId))
                <td>
                    @if (empty($log->reason))
                    {!! Form::open(['method'=>'POST', 'route'=>['equipment_log.update', $log->id], 'name'=>'equipmentLog']) !!}
                    {!! Form::select('reason', ['testing'=>'Testing', 'training'=>'Training'], $log->reason, ['class'=>'']) !!}
                    {!! Form::submit('Update', ['class'=>'btn btn-primary btn-xs']) !!}
                    {!! Form::close() !!}
                    @endif
                </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="panel-footer">
        <?php echo $equipmentLog->render(); ?>
    </div>
    @endif

    <div class="modal fade" id="helpModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Documentation and Help</h4>
                </div>
                <div class="modal-body">
                    @if ($equipment->present()->help_text)
                        <h1>{{ $equipment->name }} Help</h1>
                        <div class="well">
                            {!! $equipment->present()->help_text !!}
                        </div>
                    @endif
                    
                    @if ($equipment->docs)
                        <h1>{{ $equipment->name }} Documentation</h1>
                        <a target="_blank" class="btn btn-info" href="{{ $equipment->docs }}">➡️ View in full on the documentation system</a>
                        <br/><br/>
                        <div class="well alert-info" style="background:#eee">
                            {!! $docs !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@stop
