@section('trustLevel')
    <div class="alert alert-warning">
        🔒 Some information may only be edited by trainers of this tool or admins.<br/>
    </div>
@stop

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<h3>Name</h3>
<div class="row">
    <div class="col-md-6">
        <div class="{{ $errors->has('name') ? 'has-error' : '' }}">
            {!! Form::label('name', 'Name', ['class'=>'']) !!}
            {!! Form::text('name', null, ['class'=>'form-control', 'required']) !!}
            <p class="help-block">Aim for a short but descriptive name, i.e. Metal Bandsaw</p>
            @if($errors->has('name'))
                <span class="help-block">
                    @foreach($errors->get('name') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ $errors->has('slug') ? 'has-error' : '' }}">
            {!! Form::label('slug', 'Slug', ['class'=>'']) !!}
            {!! Form::text('slug', null, ['class'=>'form-control', 'required']) !!}
            <p class="help-block">This is the unique reference for the item, no special characters. i.e. metal-bandsaw or cordless-drill-1</p>
            @if($errors->has('slug'))
                <span class="help-block">
                    @foreach($errors->get('slug') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif        
        </div>
    </div>
</div>

<h3>Tool Properties</h3>
<div class="row">
    <div class="col-md-6">
        <div class="{{ $errors->has('manufacturer') ? 'has-error' : '' }}">
            {!! Form::label('manufacturer', 'Manufacturer', ['class'=>'']) !!}
            {!! Form::text('manufacturer', null, ['class'=>'form-control']) !!}
            @if($errors->has('manufacturer'))
                <span class="help-block">
                    @foreach($errors->get('manufacturer') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ $errors->has('model_number') ? 'has-error' : '' }}">
            {!! Form::label('model_number', 'Model Number', ['class'=>'']) !!}
            {!! Form::text('model_number', null, ['class'=>'form-control']) !!}
            @if($errors->has('model_number'))
                <span class="help-block">
                    @foreach($errors->get('model_number') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
            <p class="help-block"></p>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="{{ $errors->has('serial_number') ? 'has-error' : '' }}">
            {!! Form::label('serial_number', 'Serial Number', ['class'=>'']) !!}
            {!! Form::text('serial_number', null, ['class'=>'form-control']) !!}
            @if($errors->has('serial_number'))
                <span class="help-block">
                    @foreach($errors->get('serial_number') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ $errors->has('colour') ? 'has-error' : '' }}">
            {!! Form::label('colour', 'Colour', ['class'=>'']) !!}
            {!! Form::text('colour', null, ['class'=>'form-control']) !!}
            <p class="help-block">A rough guide such as grey or blue/green</p>
            @if($errors->has('colour'))
                <span class="help-block">
                    @foreach($errors->get('colour') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="{{ $errors->has('permaloan') ? 'has-error' : '' }}">
            {!! Form::label('permaloan', 'Permaloan', ['class'=>'']) !!}
            {!! Form::select('permaloan', [0=>'No', 1=>'Yes'], null, ['class'=>'form-control']) !!}
            <p class="help-block">Is this item on permanent loan from a member?</p>
            @if($errors->has('permaloan'))
                <span class="help-block">
                    @foreach($errors->get('permaloan') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ $errors->has('permaloan_user_id') ? 'has-error' : '' }}">
            {!! Form::label('permaloan_user_id', 'Permaloan Owner', ['class'=>'']) !!}
            {!! Form::select('permaloan_user_id', [''=>'']+$memberList, null, ['class'=>'form-control js-advanced-dropdown']) !!}
            <p class="help-block">If its being loaned who owns it?</p>
            @if($errors->has('permaloan_user_id'))
                <span class="help-block">
                    @foreach($errors->get('permaloan_user_id') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
</div>


<h3>Location and status</h3>
<div class="row">
    <div class="col-md-6">
        <div class="{{ $errors->has('room') ? 'has-error' : '' }}">
            {!! Form::label('room', 'Room', ['class'=>'']) !!}
            {!! Form::select('room', ['' => '','welding' => 'Welding','woodwork'=>'Woody Dusty', 'metalworking'=>'Metalwork', 'visual-arts'=>'Visual Arts', 'electronics'=>'Electronics','main-room'=>'Main Room'], null, ['class'=>'form-control', 'required']) !!}
            @if($errors->has('room'))
                <span class="help-block">
                    @foreach($errors->get('room') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ $errors->has('detail') ? 'has-error' : '' }}">
            {!! Form::label('detail', 'Detail', ['class'=>'']) !!}
            {!! Form::text('detail', null, ['class'=>'form-control']) !!}
            <p class="help-block">Where in the room is it kept?</p>
            @if($errors->has('detail'))
                <span class="help-block">
                    @foreach($errors->get('detail') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="{{ $errors->has('working') ? 'has-error' : '' }}">
            {!! Form::label('working', 'Working', ['class'=>'']) !!}
            {!! Form::select('working', [1=>'Yes', 0=>'No'], null, ['class'=>'form-control']) !!}
            <p class="help-block">Is the equipment ready for use?</p>
            @if($errors->has('working'))
                <span class="help-block">
                    @foreach($errors->get('working') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ $errors->has('managing_role_id') ? 'has-error' : '' }}">
            {!! Form::label('managing_role_id', 'Managing Group', ['class'=>'']) !!}
            {!! Form::select('managing_role_id', [''=>'']+$roleList, null, ['class'=>'form-control js-advanced-dropdown']) !!}
            <p class="help-block">Is a group is responsible for this piece of equipment?</p>
            @if($errors->has('managing_role_id'))
                <span class="help-block">
                    @foreach($errors->get('managing_role_id') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="{{ $errors->has('description') ? 'has-error' : '' }}">
            {!! Form::label('description', 'Description', ['class'=>'']) !!}
            {!! Form::textarea('description', null, ['class'=>'form-control']) !!}
            <p class="help-block">Use markdown for formatting</p>
            @if($errors->has('description'))
                <span class="help-block">
                    @foreach($errors->get('description') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group {{ $errors->has('docs') ? 'has-error' : '' }}">
            {!! Form::label('docs', 'Link to documentation system', ['class'=>'col-sm-3 control-label']) !!}
            <div class="col-sm-9 col-lg-7">
                {!! Form::text('docs', null, ['class'=>'form-control']) !!}
                <p class="help-block">
                    Enter a link to the documentation system for this tool.
                </p>
                @if($errors->has('docs'))
                    <span class="help-block">
                        @foreach($errors->get('docs') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="form-group {{ $errors->has('help_text') ? 'has-error' : '' }}">
    {!! Form::label('help_text', 'Help Text', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::textarea('help_text', null, ['class'=>'form-control']) !!}
        <p class="help-block">Helpful hints - make sure to keep most information on the documentation system.</p>
        @if($errors->has('help_text'))
            <span class="help-block">
                @foreach($errors->get('help_text') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>


<h4>Health & Safety</h4>
<div class="alert alert-info">
    To maintain the integrity of H&S and the training system, only admins, members of the <a href="<?=URL::to('/')?>/groups/equipment">equipment</a> team and trainers can edit this section.<br/>
</div>

<div class="form-group {{ $errors->has('ppe') ? 'has-error' : '' }}">
    {!! Form::label('ppe', 'PPE', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('ppe[]', [''=>'']+$ppeList, null, ['class'=>'form-control js-advanced-dropdown', 'multiple']) !!}
        @if($errors->has('ppe'))
            <span class="help-block">
                @foreach($errors->get('ppe') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>


<div class="form-group alert-danger {{ $errors->has('dangerous') ? 'has-error' : '' }}">
    {!! Form::label('dangerous', 'Is Bloody Dangerous?', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('dangerous', [0=>'No', 1=>'Yes'], null, ['class'=>'form-control']) !!}
        @if($errors->has('dangerous'))
            <span class="help-block">
                @foreach($errors->get('dangerous') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>


<h3>Training & Inductions</h3>

<div class="form-group {{ $errors->has('requires_induction') ? 'has-error' : '' }}">
    {!! Form::label('requires_induction', 'Requires Induction', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('requires_induction', [0=>'No', 1=>'Yes'], null, ['class'=>'form-control']) !!}
        @if($errors->has('requires_induction'))
            <span class="help-block">
                @foreach($errors->get('requires_induction') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group {{ $errors->has('accepting_inductions') ? 'has-error' : '' }}">
    {!! Form::label('accepting_inductions', 'Accepting Induction Requests', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('accepting_inductions', [null => '', 0=>'No', 1=>'Yes'], null, ['class'=>'form-control']) !!}
        <div class="help-block">Ability to enable/disable inductions, depending on maintainer/trainer workload.</div>
        @if($errors->has('accepting_inductions'))
            <span class="help-block">
                @foreach($errors->get('accepting_inductions') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group {{ $errors->has('induction_category') ? 'has-error' : '' }}">
    {!! Form::label('induction_category', 'Induction Category', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('induction_category', null, ['class'=>'form-control']) !!}
        <p class="help-block">By getting inducted on this piece of equipment they are inducted to this category meaning they have access to any other piece of equipment in the same category. i.e. access to all 3D Printers.</p>
        @if($errors->has('induction_category'))
            <span class="help-block">
                @foreach($errors->get('induction_category') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group {{ $errors->has('induction_instructions') ? 'has-error' : '' }}">
    {!! Form::label('induction_instructions', 'Instructions to those awaiting training', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::textarea('induction_instructions', null, ['class'=>'form-control']) !!}
        <p class="help-block">Shown to members after they have requested training. Possible uses: Linking to Telegram group to request training, or schedule of frequent training sessions. Uses markdown for formatting.</p>
        <p class="help-block"></p>
        @if($errors->has('induction_instructions'))
            <span class="help-block">
                @foreach($errors->get('induction_instructions') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group {{ $errors->has('trained_instructions') ? 'has-error' : '' }}">
    {!! Form::label('trained_instructions', 'Instructions for trained users', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::textarea('trained_instructions', null, ['class'=>'form-control']) !!}
        <p class="help-block">Instructions for those who have been trained. You could use this to remind them of important notes about the equipment, or share access/padlock codes to use the equipment. Use markdown for formatting.</p>
        @if($errors->has('trained_instructions'))
            <span class="help-block">
                @foreach($errors->get('trained_instructions') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group {{ $errors->has('trainer_instructions') ? 'has-error' : '' }}">
    {!! Form::label('trainer_instructions', 'Instructions for Trainers', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::textarea('trainer_instructions', null, ['class'=>'form-control']) !!}
        <p class="help-block">Only trainers see this - e.g. documents to risk assessments. Use markdown for formatting.</p>
        @if($errors->has('trainer_instructions'))
            <span class="help-block">
                @foreach($errors->get('trainer_instructions') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>


<h3>Misc</h3>

<div class="form-group {{ $errors->has('access_fee') ? 'has-error' : '' }}">
    {!! Form::label('access_fee', 'Access Fee', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        <div class="input-group">
            <div class="input-group-addon">&pound;</div>
            {!! Form::input('number', 'access_fee', null, ['class'=>'form-control', 'min'=>'0', 'step'=>'1']) !!}
        </div>
        <p class="help-block">Is an access fee being charged?</p>
        @if($errors->has('access_fee'))
            <span class="help-block">
                @foreach($errors->get('access_fee') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group {{ $errors->has('usage_cost') ? 'has-error' : '' }}">
    {!! Form::label('usage_cost', 'Usage Cost', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        <div class="input-group">
            <div class="input-group-addon">&pound;</div>
            {!! Form::input('number', 'usage_cost', null, ['class'=>'form-control', 'min'=>'0', 'step'=>'0.01', 'required']) !!}
            <div class="input-group-addon">
            Per {!! Form::select('usage_cost_per', [''=>'-', 'hour'=>'hour', 'gram'=>'gram', 'page'=>'page'], null, ['class'=>'', 'required']) !!}
            </div>
        </div>
        <p class="help-block">Does the equipment cost anything to use?</p>
        @if($errors->has('usage_cost'))
            <span class="help-block">
                @foreach($errors->get('usage_cost') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group {{ $errors->has('access_code') ? 'has-error' : '' }}">
    {!! Form::label('access_code', 'Access Code (e.g. for padlock)', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('access_code', null, ['class'=>'form-control']) !!}
        <p class="help-block">The access code, if applicable, for this tool to be used.</p>
        @if($errors->has('access_code'))
            <span class="help-block">
                @foreach($errors->get('access_code') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group {{ $errors->has('asset_tag_id') ? 'has-error' : '' }}">
    {!! Form::label('asset_tag_id', 'Asset Tag ID', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('asset_tag_id', null, ['class'=>'form-control']) !!}
        <p class="help-block">If an asset tag is being placed onto this piece of equipment whats the ID?</p>
        @if($errors->has('asset_tag_id'))
            <span class="help-block">
                @foreach($errors->get('asset_tag_id') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group {{ $errors->has('device_key') ? 'has-error' : '' }}">
    {!! Form::label('device_key', 'Device Key', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('device_key', null, ['class'=>'form-control']) !!}
        <p class="help-block">The id of a ACS device already setup in the database</p>
        @if($errors->has('device_key'))
            <span class="help-block">
                @foreach($errors->get('device_key') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

{{-- Allow anybody to set on creation, but only admins/trainers to edit --}}
<div class="form-group {{ $errors->has('obtained_at') ? 'has-error' : '' }}">
    {!! Form::label('obtained_at', 'Date Obtained', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('obtained_at', (isset($equipment) && $equipment) ? ($equipment->obtained_at ? $equipment->obtained_at->toDateString() : null) : null, ['class'=>'form-control js-date-select', "pattern" => "\d{4}-\d{2}-\d{2}"]) !!}
        <p class="help-block">When did Hackspace Manchester obtain/purchase the item? Formatted as <code>YYYY-MM-DD</code></p>
        @if($errors->has('obtained_at'))
            <span class="help-block">
                @foreach($errors->get('obtained_at') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

{{-- Only show on edit page--}}
@if (isset($equipment))
    <div class="form-group {{ $errors->has('removed_at') ? 'has-error' : '' }}">
        {!! Form::label('removed_at', 'Date Removed', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('removed_at', (isset($equipment) && $equipment) ? ($equipment->removed_at ? $equipment->removed_at->toDateString() : null) : null, ['class'=>'form-control js-date-select', "pattern" => "\d{4}-\d{2}-\d{2}"]) !!}
            <p class="help-block">When did Hackspace Manchester get rid of it? Formatted as <code>YYYY-MM-DD</code></p>
            @if($errors->has('removed_at'))
                <span class="help-block">
                    @foreach($errors->get('removed_at') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </span>
            @endif
        </div>
    </div>
@endif
