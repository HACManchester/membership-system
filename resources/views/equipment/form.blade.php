@section('trustLevel')
    <div class="alert alert-warning">
        🔒 Some information may only be edited by trainers of this tool or admins.<br/>
    </div>
@stop

<h3>Name</h3>
<div class="row">
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('name', 'has-error has-feedback') }}">
            {!! Form::label('name', 'Name', ['class'=>'']) !!}
            {!! Form::text('name', null, ['class'=>'form-control', 'required']) !!}
            <p class="help-block">Aim for a short but descriptive name, i.e. Metal Bandsaw</p>
            {!! FlashNotification::getErrorDetail('name') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('slug', 'has-error has-feedback') }}">
            {!! Form::label('slug', 'Slug', ['class'=>'']) !!}
            {!! Form::text('slug', null, ['class'=>'form-control', 'required']) !!}
            <p class="help-block">This is the unique reference for the item, no special characters. i.e. metal-bandsaw or cordless-drill-1</p>
            {!! FlashNotification::getErrorDetail('slug') !!}
        
        </div>
    </div>
</div>

<h3>Tool Properties</h3>
<div class="row">
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('family_name', 'has-error has-feedback') }}">
            {!! Form::label('manufacturer', 'Manufacturer', ['class'=>'']) !!}
            {!! Form::text('manufacturer', null, ['class'=>'form-control']) !!}
            {!! FlashNotification::getErrorDetail('manufacturer') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('model_number', 'has-error has-feedback') }}">
            {!! Form::label('model_number', 'Model Number', ['class'=>'']) !!}
            {!! Form::text('model_number', null, ['class'=>'form-control']) !!}
            {!! FlashNotification::getErrorDetail('model_number') !!}
            <p class="help-block"></p>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('serial_number', 'has-error has-feedback') }}">
            {!! Form::label('serial_number', 'Serial Number', ['class'=>'']) !!}
            {!! Form::text('serial_number', null, ['class'=>'form-control']) !!}
            {!! FlashNotification::getErrorDetail('serial_number') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('colour', 'has-error has-feedback') }}">
            {!! Form::label('colour', 'Colour', ['class'=>'']) !!}
            {!! Form::text('colour', null, ['class'=>'form-control']) !!}
            <p class="help-block">A rough guide such as grey or blue/green</p>
            {!! FlashNotification::getErrorDetail('colour') !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('permaloan', 'has-error has-feedback') }}">
            {!! Form::label('permaloan', 'Permaloan', ['class'=>'']) !!}
            {!! Form::select('permaloan', [0=>'No', 1=>'Yes'], null, ['class'=>'form-control']) !!}
            <p class="help-block">Is this item on permanent loan from a member?</p>
            {!! FlashNotification::getErrorDetail('permaloan') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('permaloan_user_id', 'has-error has-feedback') }}">
            {!! Form::label('permaloan_user_id', 'Permaloan Owner', ['class'=>'']) !!}
            {!! Form::select('permaloan_user_id', [''=>'']+$memberList, null, ['class'=>'form-control js-advanced-dropdown']) !!}
            <p class="help-block">If its being loaned who owns it?</p>
            {!! FlashNotification::getErrorDetail('permaloan_user_id') !!}
        </div>
    </div>
</div>


<h3>Location and status</h3>
<div class="row">
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('room', 'has-error has-feedback') }}">
            {!! Form::label('room', 'Room', ['class'=>'']) !!}
            {!! Form::select('room', ['' => '','welding' => 'Welding','woodwork'=>'Woody Dusty', 'metalworking'=>'Metalwork', 'visual-arts'=>'Visual Arts', 'electronics'=>'Electronics','main-room'=>'Main Room'], null, ['class'=>'form-control', 'required']) !!}
            {!! FlashNotification::getErrorDetail('room') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('detail', 'has-error has-feedback') }}">
            {!! Form::label('detail', 'Detail', ['class'=>'']) !!}
            {!! Form::text('detail', null, ['class'=>'form-control']) !!}
            <p class="help-block">Where in the room is it kept?</p>
            {!! FlashNotification::getErrorDetail('detail') !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('working', 'has-error has-feedback') }}">
            {!! Form::label('working', 'Working', ['class'=>'']) !!}
            {!! Form::select('working', [1=>'Yes', 0=>'No'], null, ['class'=>'form-control']) !!}
            <p class="help-block">Is the equipment ready for use?</p>
            {!! FlashNotification::getErrorDetail('working') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ FlashNotification::hasErrorDetail('managing_role_id', 'has-error has-feedback') }}">
            {!! Form::label('managing_role_id', 'Managing Group', ['class'=>'']) !!}
            {!! Form::select('managing_role_id', [''=>'']+$roleList, null, ['class'=>'form-control js-advanced-dropdown']) !!}
            <p class="help-block">Is a group is responsible for this piece of equipment?</p>
            {!! FlashNotification::getErrorDetail('managing_role_id') !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="{{ FlashNotification::hasErrorDetail('description', 'has-error has-feedback') }}">
            {!! Form::label('description', 'Description', ['class'=>'']) !!}
            {!! Form::textarea('description', null, ['class'=>'form-control']) !!}
            <p class="help-block">Use markdown for formatting</p>
            {!! FlashNotification::getErrorDetail('description') !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group {{ FlashNotification::hasErrorDetail('docs', 'has-error has-feedback') }}">
            {!! Form::label('docs', 'Link to documentation system', ['class'=>'col-sm-3 control-label']) !!}
            <div class="col-sm-9 col-lg-7">
                {!! Form::text('docs', null, ['class'=>'form-control']) !!}
                <p class="help-block">
                    Enter a link to the documentation system for this tool.
                </p>
                {!! FlashNotification::getErrorDetail('docs') !!}
            </div>
        </div>
    </div>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('help_text', 'has-error has-feedback') }}">
    {!! Form::label('help_text', 'Help Text', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::textarea('help_text', null, ['class'=>'form-control']) !!}
        <p class="help-block">Helpful hints - make sure to keep most information on the documentation system.</p>
        {!! FlashNotification::getErrorDetail('help_text') !!}
    </div>
</div>


<h4>Health & Safety</h4>
<div class="alert alert-info">
    To maintain the integrity of H&S and the training system, only admins, members of the <a href="<?=URL::to('/')?>/groups/equipment">equipment</a> team and trainers can edit this section.<br/>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('ppe', 'has-error has-feedback') }}">
    {!! Form::label('ppe', 'PPE', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('ppe[]', [''=>'']+$ppeList, null, ['class'=>'form-control js-advanced-dropdown', 'multiple']) !!}
        {!! FlashNotification::getErrorDetail('ppe') !!}
    </div>
</div>


<div class="form-group alert-danger {{ FlashNotification::hasErrorDetail('dangerous', 'has-error has-feedback') }}">
    {!! Form::label('dangerous', 'Is Bloody Dangerous?', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('dangerous', [0=>'No', 1=>'Yes'], null, ['class'=>'form-control']) !!}
        {!! FlashNotification::getErrorDetail('dangerous') !!}
    </div>
</div>


<h3>Training & Inductions</h3>

<div class="form-group {{ FlashNotification::hasErrorDetail('requires_induction', 'has-error has-feedback') }}">
    {!! Form::label('requires_induction', 'Requires Induction', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('requires_induction', [0=>'No', 1=>'Yes'], null, ['class'=>'form-control']) !!}
        {!! FlashNotification::getErrorDetail('requires_induction') !!}
    </div>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('accepting_inductions', 'has-error has-feedback') }}">
    {!! Form::label('accepting_inductions', 'Accepting Induction Requests', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('accepting_inductions', [null => '', 0=>'No', 1=>'Yes'], null, ['class'=>'form-control']) !!}
        <div class="help-block">Ability to enable/disable inductions, depending on maintainer/trainer workload.</div>
        {!! FlashNotification::getErrorDetail('accepting_inductions') !!}
    </div>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('induction_category', 'has-error has-feedback') }}">
    {!! Form::label('induction_category', 'Induction Category', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('induction_category', null, ['class'=>'form-control']) !!}
        <p class="help-block">By getting inducted on this piece of equipment they are inducted to this category meaning they have access to any other piece of equipment in the same category. i.e. access to all 3D Printers.</p>
        {!! FlashNotification::getErrorDetail('induction_category') !!}
    </div>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('induction_instructions', 'has-error has-feedback') }}">
    {!! Form::label('induction_instructions', 'Instructions to those awaiting training', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::textarea('induction_instructions', null, ['class'=>'form-control']) !!}
        <p class="help-block">Shown to members after they have requested training. Possible uses: Linking to Telegram group to request training, or schedule of frequent training sessions. Uses markdown for formatting.</p>
        <p class="help-block"></p>
        {!! FlashNotification::getErrorDetail('induction_instructions') !!}
    </div>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('trained_instructions', 'has-error has-feedback') }}">
    {!! Form::label('trained_instructions', 'Instructions for trained users', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::textarea('trained_instructions', null, ['class'=>'form-control']) !!}
        <p class="help-block">Instructions for those who have been trained. You could use this to remind them of important notes about the equipment, or share access/padlock codes to use the equipment. Use markdown for formatting.</p>
        {!! FlashNotification::getErrorDetail('trained_instructions') !!}
    </div>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('trainer_instructions', 'has-error has-feedback') }}">
    {!! Form::label('trainer_instructions', 'Instructions for Trainers', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::textarea('trainer_instructions', null, ['class'=>'form-control']) !!}
        <p class="help-block">Only trainers see this - e.g. documents to risk assessments. Use markdown for formatting.</p>
        {!! FlashNotification::getErrorDetail('trainer_instructions') !!}
    </div>
</div>


<h3>Misc</h3>

<div class="form-group {{ FlashNotification::hasErrorDetail('access_fee', 'has-error has-feedback') }}">
    {!! Form::label('access_fee', 'Access Fee', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        <div class="input-group">
            <div class="input-group-addon">&pound;</div>
            {!! Form::input('number', 'access_fee', null, ['class'=>'form-control', 'min'=>'0', 'step'=>'1']) !!}
        </div>
        <p class="help-block">Is an access fee being charged?</p>
        {!! FlashNotification::getErrorDetail('access_fee') !!}
    </div>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('usage_cost', 'has-error has-feedback') }}">
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
        {!! FlashNotification::getErrorDetail('usage_cost') !!}
    </div>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('access_code', 'has-error has-feedback') }}">
    {!! Form::label('access_code', 'Access Code (e.g. for padlock)', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('access_code', null, ['class'=>'form-control']) !!}
        <p class="help-block">The access code, if applicable, for this tool to be used.</p>
        {!! FlashNotification::getErrorDetail('access_code') !!}
    </div>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('asset_tag_id', 'has-error has-feedback') }}">
    {!! Form::label('asset_tag_id', 'Asset Tag ID', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('asset_tag_id', null, ['class'=>'form-control']) !!}
        <p class="help-block">If an asset tag is being placed onto this piece of equipment whats the ID?</p>
        {!! FlashNotification::getErrorDetail('asset_tag_id') !!}
    </div>
</div>

<div class="form-group {{ FlashNotification::hasErrorDetail('device_key', 'has-error has-feedback') }}">
    {!! Form::label('device_key', 'Device Key', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('device_key', null, ['class'=>'form-control']) !!}
        <p class="help-block">The id of a ACS device already setup in the database</p>
        {!! FlashNotification::getErrorDetail('device_key') !!}
    </div>
</div>

{{-- Allow anybody to set on creation, but only admins/trainers to edit --}}
<div class="form-group {{ FlashNotification::hasErrorDetail('obtained_at', 'has-error has-feedback') }}">
    {!! Form::label('obtained_at', 'Date Obtained', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('obtained_at', (isset($equipment) && $equipment) ? ($equipment->obtained_at ? $equipment->obtained_at->toDateString() : null) : null, ['class'=>'form-control js-date-select', "pattern" => "\d{4}-\d{2}-\d{2}"]) !!}
        <p class="help-block">When did Hackspace Manchester obtain/purchase the item? Formatted as <code>YYYY-MM-DD</code></p>
        {!! FlashNotification::getErrorDetail('obtained_at') !!}
    </div>
</div>

{{-- Only show on edit page--}}
@if (isset($equipment))
    <div class="form-group {{ FlashNotification::hasErrorDetail('removed_at', 'has-error has-feedback') }}">
        {!! Form::label('removed_at', 'Date Removed', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('removed_at', (isset($equipment) && $equipment) ? ($equipment->removed_at ? $equipment->removed_at->toDateString() : null) : null, ['class'=>'form-control js-date-select', "pattern" => "\d{4}-\d{2}-\d{2}"]) !!}
            <p class="help-block">When did Hackspace Manchester get rid of it? Formatted as <code>YYYY-MM-DD</code></p>
            {!! FlashNotification::getErrorDetail('removed_at') !!}
        </div>
    </div>
@endif
