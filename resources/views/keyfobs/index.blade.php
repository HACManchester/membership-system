@extends('layouts.main')

@section('meta-title')
    Manage your access methods
@stop

@section('page-title')
    Manage your access methods
@stop

@section('content')
    <div class="panel panel-info">
        <div class="panel-heading">Access methods - key fobs and access codes</div>
        <div class="panel-body">
            @if (!$user->online_only)
                @if (!$user->isAdmin() && !$user->induction_completed && $user->keyFobs()->count() === 0)
                    <div class="alert alert-warning">
                        You need to have been given the general induction before you can add access methods.
                    </div>
                @else

                    <div class="row">
                        <div class="col-md-6">
                            <h4>How 24/7 access works</h4>
                            <p>
                                Active members have two ways to access the space:
                            <ul>
                                <li>Using a fob - this is the primary method - enter the ID of the fob below to add a fob.
                                </li>
                                <li>Using an access code - once you have a fob, you may generate an access code which is
                                    auto-generated and cannot be edited.</li>
                            </ul>
                            Entries to the space are securely logged to prevent abuse of the space.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h4>Your access to the space</h4>
                            @if ($user->keyFobs()->count() == 0)
                                <div class="alert alert-warning">
                                    <strong>You have no entry methods</strong> and won't be able to access the space outside
                                    of open evenings.
                                </div>
                            @else
                                <!--
                                    <p>
                                        @if ($user->announce_name)
    🎉 Your announce name is set to: <code>{{ $user->announce_name }}</code> (<a href="#announce_name">edit</a>)
@else
    🗣️ You don't have an announce name set, why not <a href="#announce_name">make an entrance</a> and set an announce name? (optional)
    @endif
                                        <br><br>
                                        Announce names are announced in the space and on the Hackscreen chat when you enter - have fun, set a conversation starter, just don't add anything rude, offensive or personal.
                                    </p>
                                    -->

                                @if ($user->keyFobs()->count() > 0)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <ol class="list-group">
                                                @foreach ($user->keyFobs()->get() as $fob)
                                                    <form method="POST" action="{{ route('keyfobs.destroy', [$user->id, $fob->id]) }}" class="form-horizontal">
                                                        @csrf
                                                        @method('DELETE')
                                                        <li class="list-group-item row">
                                                            <div class="col-md-6">
                                                                @if (substr($fob->key_id, 0, 2) !== 'ff')
                                                                    <h4>
                                                                        <span class="label label-info"
                                                                            style="background:forestgreen">
                                                                            🔑 Fob
                                                                        </span>
                                                                    </h4>
                                                                    <h4>Fob ID:{{ $fob->key_id }}</h4>
                                                                @else
                                                                    <h4>
                                                                        <span class="label label-info"
                                                                            style="background:tomato">
                                                                            🔢 Access Code
                                                                        </span>
                                                                    </h4>
                                                                    <h4>
                                                                        Code: {{ str_replace('f', '', $fob->key_id) }} #
                                                                    </h4>
                                                                @endif

                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class=" pull-right">
                                                                    @if (substr($fob->key_id, 0, 2) !== 'ff')
                                                                        <small>(added
                                                                            {{ $fob->created_at->toFormattedDateString() }})</small>
                                                                        <button type="submit" class="btn btn-default">Mark Fob Lost</button>
                                                                    @else
                                                                        <small>(added
                                                                            {{ $fob->created_at->toFormattedDateString() }})</small>
                                                                        <button type="submit" class="btn btn-default">Mark Code Lost</button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </form>
                                                @endforeach
                                            </ol>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>



                    <h3>Add a new entry method</h3>
                    @if (($user->keyFobs()->count() < 2 && $user->induction_completed) || $user->isAdmin())
                        <p>
                            You may add a fob or generate an access code (you'll be assigned a random 8 digit number).
                            Present the fob or access code to the keypad to get into the space.
                            <br>Please leave five minutes after updating your details for them to work on the door.
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <h4>Add a keyfob</h4>
                                <p><strong>In the hackspace?</strong> Select a fob from the pot, select the text box below,
                                    then scan your fob with the reader. The ID will be typed in.</p>

                                <form method="POST" action="{{ route('keyfobs.store', $user->id) }}" class="form-horizontal">
                                    @csrf
                                    <div class="form-group {{ $errors->has('key_id') ? 'has-error' : '' }}">
                                        <div class="col-sm-5">
                                            <input type="text" name="key_id" value="" class="form-control">
                                            Characters A-F and numbers 0-9 only.
                                            @if($errors->has('key_id'))
                                                <span class="help-block">
                                                    @foreach($errors->get('key_id') as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </span>
                                            @endif
                                        </div>
                                        <div class="col-sm-3">
                                            <input type="hidden" name="type" value="keyfob">
                                            <button type="submit" class="btn btn-primary">Add a new fob</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <h4>Request access code</h4>
                                <p>You'll be assigned a random 8 digit access code.</p>
                                <form method="POST" action="{{ route('keyfobs.store', $user->id) }}" class="form-horizontal">
                                    @csrf
                                    <div class="form-group">
                                        <div class="col-sm-3">
                                            {{-- Do not use 'old' helper as that might pull 'type' from previous form submissions. --}}
                                            <input type="hidden" name="type" value="access_code" />
                                            <button type="submit" class="btn btn-info">Request access code</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <p>You have added the maximum number of entry methods permitted.</p>
                    @endif
                @endif
            @else
                <div class="alert alert-danger">
                    <b>Online User Only</b> You can't add access methods as you're an online only user.
                </div>
            @endif
        </div>
    </div>
    </div>

@stop
