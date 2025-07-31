@extends('layouts.main')

@section('page-title')
<a href="{{ route('equipment.index') }}">Tools &amp; Equipment</a> > {{ $equipment->name }}
@stop

@section('meta-title')
{{ $equipment->name }}
@stop

@section('page-action-buttons')
    @can('update', $equipment)
        <a class="btn btn-secondary" href="{{ route('equipment.edit', $equipment->slug) }}">Edit</a>
    @endcan
    @can('delete', $equipment)
        <button class="btn btn-danger" data-toggle="modal" data-target="#equipment-deletion-modal">Delete</button>
    @endcan
@stop

@section('main-tab-bar')

@stop


@section('content')

<div class="row">
    <div class="col-sm-12">
        <h2>{{ $equipment->name }}</h2>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="member-status-bar">
            @if($equipment->requiresInduction())
                @if (!$userInduction)
                    <h4><span class="label label-danger">Training is required</span></h4>
                @elseif ($userInduction->trained)
                    <h4><span class="label label-success">You have been inducted and can use this equipment</span></h4>
                    @if ($equipment->access_code)
                        <h4><span class="label label-info">Access code: {{$equipment->access_code}}</span></h4>
                    @endif
                @elseif ($userInduction)
                    <h4><span class="label label-warning">Training to be completed</span></h4>
                @endif
            @endif
            @if (!$equipment->isWorking())
                <h4><span class="label label-info">Out of action</span></h4>
            @endif
            @if ($equipment->dangerous)
                <h4><span class="label label-danger">Bloody Dangerous</h4>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="well">
            <div class="row">
                <div class="col-sm-6">

                    @if ($equipment->requiresInduction())
                        @if (!$userInduction)
                            <div class="well infobox">
                                <h3>This tool requires an induction</h3>
                                <ul>
                                    <li>An induction is required before you may use this tool.</li>
                                    @if (\BB\Entities\Course::isLive() && $equipment->courses->count() > 0)
                                        <li>Training for this equipment is covered by:
                                            @foreach($equipment->courses as $course)
                                                <a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a>@if(!$loop->last), @endif
                                            @endforeach
                                        </li>
                                    @else
                                        <li>Inductions are given by other members, request an induction for details on next steps.</li>
                                    @endif
                                </ul>
                                
                                @if (!($equipment->courses->count() > 0 && $equipment->courses->first()->live))
                                    @if ($equipment->accepting_inductions)
                                        @if(Auth::user()->online_only)
                                            <h4>Online Only members may not use tools or request inductions.</h4>
                                        @else
                                            <form method="POST" action="{{ route('equipment_training.create', ['equipment' => $equipment]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-primary">Request induction</button>
                                            </form>
                                        @endif
                                    @else
                                        <div class="alert alert-warning">
                                            <strong>Inductions are currently paused for {{ $equipment->name }}.</strong>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif
                    @endif
                    
                    @if ($userInduction)
                        @if ($userInduction->trained)
                            @if ($userInduction->is_trainer && $equipment->trainer_instructions)
                                <h3>Trainer Instructions</h3>
                                <div class="infobox well">
                                    <p>{!! $equipment->present()->trainer_instructions !!}</p>
                                    <br/>
                                </div>
                            @endif

                            @if ($equipment->trained_instructions)
                                <h3>Instructions for Use</h3>
                                <div class="infobox well">
                                    <p>{!! $equipment->present()->trained_instructions !!}</p>
                                    <br/>
                                </div>
                            @endif

                            @if (in_array($equipment->slug, ['laser', 'laser-1', 'printer-lfp-1', '3dprint-mendel90', '3dprint-mendelmax', 'vac-former-1', 'ultimaker']))
                                <h3>Pay for usage</h3>
                                <div class="infobox well">
                                    <p>
                                        Make a payment for your usage of this equipment below.
                                    </p>
                                </div>
                            @endif
                        @elseif (!$userInduction && $equipment->courses->count() > 0)
                            <div class="alert alert-info">
                                <h3>🔴 Training Required</h3>
                                <p>Training for this equipment is covered by the following course(s):</p>
                                <ul>
                                    @foreach($equipment->courses as $course)
                                        <li><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            @if ($equipment->induction_instructions)
                                <div class="alert alert-info">
                                    <h3>🔴 Training Next Steps</h3>
                                    {!! $equipment->present()->induction_instructions !!}
                                </div>
                            @endif
                        @endif
                    @endif

                    <div class="tool-info">
                        @if ($equipment->present()->livesIn)
                            <div class="tool-info__detail">
                                <div class="tool-info__key">
                                    Lives in
                                </div>
                                <div class="tool-info__value">
                                    🏠 {{ $equipment->present()->livesIn }}
                                </div>
                            </div>
                        @endif
                        @if ($equipment->maintainerGroup)
                            <div class="tool-info__detail">
                                <div class="tool-info__key">
                                    Maintainer by
                                </div>
                                <div class="tool-info__value">
                                    <a href="{{ route('maintainer_groups.show', $equipment->maintainerGroup) }}">
                                        🛠️ {{ $equipment->maintainerGroup->name }}
                                    </a>
                                </div>
                            </div>
                        @endif
                        
                        <div class="tool-info__detail">
                            <div class="tool-info__key">
                                Tool working?
                            </div>
                            <div class="tool-info__value">
                                {{ $equipment->isWorking() ? "🟢 Yes" : "🔴 No" }}
                            </div>
                        </div>
                        
                        <div class="tool-info__detail">
                            <div class="tool-info__key">
                                Induction required?
                            </div>
                            <div class="tool-info__value">
                                {{ $equipment->requiresInduction() ? "🔴 Yes" : "🟢 No" }}
                            </div>
                        </div>
                        
                        @if ($equipment->present()->manufacturerModel)
                            <div class="tool-info__detail">
                                <div class="tool-info__key">
                                    Manufacturer Model
                                </div>
                                <div class="tool-info__value">
                                    🔧 {{ $equipment->present()->manufacturerModel }}
                                </div>
                            </div>
                        @endif
                        
                        @if ($equipment->present()->purchaseDate)
                            <div class="tool-info__detail">
                                <div class="tool-info__key">
                                    Purchase Date
                                </div>
                                <div class="tool-info__value">
                                    📅 {{ $equipment->present()->purchaseDate }}
                                </div>
                            </div>
                        @endif
                      
                        <div class="tool-info__detail">
                            <div class="tool-info__key">
                                Usage Cost
                            </div>
                            <div class="tool-info__value">
                                💸 {{ $equipment->hasUsageCharge() ? $equipment->present()->usageCost : "No usage charge" }}    
                            </div>
                        </div>
                    

                        <div class="tool-info__detail">
                            <div class="tool-info__key">
                                Is permaloan?
                            </div>
                            <div class="tool-info__value">
                                {{ $equipment->isPermaloan() ? "🟢 Yes" : "🔴 No" }}    
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="col-sm-6">
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
                    <h3>Personal Protective Equipment</h3>
                    @if(strlen($equipment->present()->ppe) > 20)
                        <p>The following PPE is required</p>
                        {!! $equipment->present()->ppe !!}
                    @else
                        <p>No specific PPE is required. You must still be aware of risks and use relevent PPE to mitigate those risks.</p>
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
            </div>
            <br>
            <div class="row">
                <div class="col-sm-12">
                    <div class="infobox well">
                        <h4>Description</h4>
                        {!! $equipment->present()->description !!}
                
                        <br>
                        @if ($equipment->present()->help_text)
                        <h4>{{ $equipment->name }} Help</h4>
                        {!! $equipment->present()->help_text !!}
                        @endif
                        
                        <br>
                        @if ($equipment->docs)
                            <h4>Documentation for the {{ $equipment->name }} is available</h4>
                            <a target="_blank" class="btn btn-success" href="{{ $equipment->docs }}">View documentation</a>
                        @endif
                    </div>

                </div>    
            </div>
        </div>
    </div>
</div>

@if ($equipment->requiresInduction() && !($equipment->courses->count() > 0 && $equipment->courses->first()->live))
    <h2>Member statuses for this tool</h2>
    <div class="row">
        <div class="col-sm-12">
            <div class="well">
                <h3>🎓 Trainers</h3>

                <p>These members are permitted to induct other members on this tool.</p>
                <div class="infobox__grid">
                    @foreach($trainers as $trainer)
                        <div class="infobox__grid-item infobox__grid-item--user">
                            <div>
                                    <a href="{{ route('members.show', $trainer->user->id) }}">
                                        @include('partials.components.member-photo', [
                                            'profileData' => $trainer->user->profile,
                                            'userHash' => $trainer->user->hash,
                                            'size' => 25,
                                            'class' => 'hidden-sm hidden-xs'
                                        ])
                                        {{ $trainer->user->name }}
                                    </a>
                                @if ($trainer->user->pronouns)
                                    <span>({{ $trainer->user->pronouns }})</span>
                                @endif
                            </div>
                            <div>
                                @can('demote', $trainer)
                                    <form method="POST" style="display:inline;float:right;" action="{{ route('equipment_training.demote', ['equipment' => $equipment, 'induction' => $trainer]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-default btn-sm">❌</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>

                @can('train', $equipment)
                    <a class="btn btn-danger" href="{{ route('notificationemail.equipment', [$equipment->slug, 'trainer']) }}">
                        📧 Email 
                    </a>
                @endcan
            </div>
        </div>
        
    </div>
    

    <div class="row">
        <div class="col-sm-12">
            <div class="well">
                <h3>Trained Members</h3>
                <p>There are currently <strong>{{ count($trainedUsers) }}</strong> members who are trained to use this tool.</p>
                <div class="infobox__grid">
                    @foreach($trainedUsers as $inductionRecord)
                        <div class="infobox__grid-item infobox__grid-item--user">
                            <div>
                                <a href="{{ route('members.show', $inductionRecord->user->id) }}">
                                    @include('partials.components.member-photo', [
                                        'profileData' => $inductionRecord->user->profile,
                                        'userHash' => $inductionRecord->user->hash,
                                        'size' => 25,
                                        'class' => 'hidden-sm hidden-xs'
                                    ])
                                    {{ $inductionRecord->user->name }}
                                </a>
                                @if ($inductionRecord->user->pronouns)
                                    <span>({{ $inductionRecord->user->pronouns }})</span>
                                @endif
                            </div>
                            <p><strong>Trained:</strong> <span>{{ $inductionRecord->trained->toFormattedDateString() }}</span></p>
                            <div>
                                @can('untrain', $inductionRecord)
                                    <form method="POST" style="display:inline;float:right;" action="{{ route('equipment_training.untrain', ['equipment' => $equipment, 'induction' => $inductionRecord]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-default btn-sm">❌</button>
                                    </form>
                                @endcan

                                @can('promote', $inductionRecord)
                                    <form method="POST" style="display:inline;float:right;" action="{{ route('equipment_training.promote', ['equipment' => $equipment, 'induction' => $inductionRecord]) }}">
                                        @csrf
                                        <button type="submit" class="{{ $inductionRecord->is_trainer ? 'btn btn-sm disabled' : 'btn btn-sm btn-default' }}">🎓</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>

                @can('train', $equipment)
                    <a class="btn btn-danger" href="{{ route('notificationemail.equipment', [$equipment->slug, 'trained']) }}">
                        📧 Email 
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="well infobox">
                <h3>Awaiting Training</h3>
               
                <p>There are currently <strong>{{ count($usersPendingInduction) }}</strong> member(s) who are awaiting training for this tool.</p>
                
                @if ($userInduction && !$userInduction->trained)
                    <div class="alert alert-info">
                        <h3>🔴 Training Next Steps</h3>
                        @if ($equipment->induction_instructions)
                            {!! $equipment->present()->induction_instructions !!}
                        @else
                            <p>To get trained, ask on the forum or on Telegram.</p>
                        @endif
                    </div>
                @endif
                <div class="infobox__grid">

                    @foreach($usersPendingInduction as $inductionRecord)
                        @if (Auth::user()->can('view', $inductionRecord) || $inductionRecord->user->id == Auth::user()->id)
                            <div class="infobox__grid-item infobox__grid-item--user" >
                                <div>
                                    <a href="{{ route('members.show', $inductionRecord->user->id) }}">
                                        @include('partials.components.member-photo', [
                                            'profileData' => $inductionRecord->user->profile,
                                            'userHash' => $inductionRecord->user->hash,
                                            'size' => 25,
                                            'class' => 'hidden-sm hidden-xs'
                                        ])
                                        {{ $inductionRecord->user->name }}
                                    </a>
                                    @if ($inductionRecord->user->pronouns)
                                        <span>({{ $inductionRecord->user->pronouns }})</span>
                                    @endif
                                    <p><strong>Requested:</strong> <span>{{ $inductionRecord->created_at->toFormattedDateString() }} ({{ $inductionRecord->created_at->diff($now)->format("%yy, %mm, %dd") }})</span></p>
                                </div>
                                
                                <div>
                                    @can('delete', $inductionRecord)
                                        <form method="POST" style="display:inline;float:right;" action="{{ route('equipment_training.destroy', ['equipment' => $equipment, 'induction' => $inductionRecord]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-default btn-sm">❌</button>
                                        </form>
                                    @endcan

                                    @can('train', $inductionRecord)
                                        <form method="POST" style="display:inline;float:right;" action="{{ route('equipment_training.train', ['equipment' => $equipment, 'induction' => $inductionRecord]) }}">
                                            @csrf
                                            <input type="hidden" name="trainer_user_id" value="{{ Auth::user()->id }}">
                                            <input type="hidden" name="mark_trained" value="1">
                                            <button type="submit" class="btn btn-default btn-sm">✔️</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endif
                    @endforeach
                    
                    @can('train', $equipment)
                        <div class="infobox__grid-item infobox__grid-item--footer">
                            <p>Add a member</p>
                            <form method="POST" action="{{ route('equipment_training.create', ['equipment' => $equipment]) }}">
                                @csrf
                                <select name="user_id" class="form-control js-advanced-dropdown">
                                    <option value="">Add a member</option>
                                    @foreach($memberList as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-default btn-sm">✔️</button>
                            </form>
                        </div>
                    @endcan
                </div>
            
                @can('train', $equipment)
                    <a class="btn btn-danger" href="{{ route('notificationemail.equipment', [$equipment->slug, 'awaiting_training']) }}">
                        📧 Email 
                    </a>
                @endcan
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
    
    @can('delete', $equipment)
        <div class="modal fade" tabindex="-1" role="dialog" id="equipment-deletion-modal">
            <form class="modal-dialog" role="document" action="{{ route('equipment.destroy', $equipment) }}" method="POST">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}

                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Confirm deletion</h4>
                    </div>
                    <div class="modal-body">
                        <p>Deleting <em>{{ $equipment->name}}</em> will remove it from the members system entirely.</p>
                        <p>Are you sure you want to delete this item?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    @endcan

</div>

@stop
