@section('trustLevel')
    <div class="alert alert-warning">
        🔒 Some information may only be edited by trainers of this tool or admins.<br/>
    </div>
@stop

<h3>Name</h3>
<div class="row">
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('name', 'has-error has-feedback') }}">
            {!! Form::label('name', 'Name', ['class'=>'']) !!}
            {!! Form::text('name', null, ['class'=>'form-control']) !!}
            <p class="help-block">Aim for a short but descriptive name, i.e. Metal Bandsaw</p>
            {!! Notification::getErrorDetail('name') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('slug', 'has-error has-feedback') }}">
            {!! Form::label('slug', 'Slug', ['class'=>'']) !!}
            {!! Form::text('slug', null, ['class'=>'form-control']) !!}
            <p class="help-block">This is the unique reference for the item, no special characters. i.e. metal-bandsaw or cordless-drill-1</p>
            {!! Notification::getErrorDetail('slug') !!}
        
        </div>
    </div>
</div>

<h3>Tool Properties</h3>
<div class="row">
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('family_name', 'has-error has-feedback') }}">
            {!! Form::label('manufacturer', 'Manufacturer', ['class'=>'']) !!}
            {!! Form::text('manufacturer', null, ['class'=>'form-control']) !!}
            {!! Notification::getErrorDetail('manufacturer') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('model_number', 'has-error has-feedback') }}">
            {!! Form::label('model_number', 'Model Number', ['class'=>'']) !!}
            {!! Form::text('model_number', null, ['class'=>'form-control']) !!}
            {!! Notification::getErrorDetail('model_number') !!}
            <p class="help-block"></p>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('serial_number', 'has-error has-feedback') }}">
            {!! Form::label('serial_number', 'Serial Number', ['class'=>'']) !!}
            {!! Form::text('serial_number', null, ['class'=>'form-control']) !!}
            {!! Notification::getErrorDetail('serial_number') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('colour', 'has-error has-feedback') }}">
            {!! Form::label('colour', 'Colour', ['class'=>'']) !!}
            {!! Form::text('colour', null, ['class'=>'form-control']) !!}
            <p class="help-block">A rough guide such as grey or blue/green</p>
            {!! Notification::getErrorDetail('colour') !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('permaloan', 'has-error has-feedback') }}">
            {!! Form::label('permaloan', 'Permaloan', ['class'=>'']) !!}
            {!! Form::select('permaloan', [0=>'No', 1=>'Yes'], null, ['class'=>'form-control']) !!}
            <p class="help-block">Is this item on permanent loan from a member?</p>
            {!! Notification::getErrorDetail('permaloan') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('permaloan_user_id', 'has-error has-feedback') }}">
            {!! Form::label('permaloan_user_id', 'Permaloan Owner', ['class'=>'']) !!}
            {!! Form::select('permaloan_user_id', [''=>'']+$memberList, null, ['class'=>'form-control js-advanced-dropdown']) !!}
            <p class="help-block">If its being loaned who owns it?</p>
            {!! Notification::getErrorDetail('permaloan_user_id') !!}
        </div>
    </div>
</div>


<h3>Location and status</h3>
<div class="row">
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('room', 'has-error has-feedback') }}">
            {!! Form::label('room', 'Room', ['class'=>'']) !!}
            {!! Form::select('room', ['woodwork'=>'Woody Dusty', 'metalworking'=>'Metalwork', 'visual-arts'=>'Visual Arts', 'electronics'=>'Electronics','main-room'=>'Main Room'], null, ['class'=>'form-control']) !!}
            {!! Notification::getErrorDetail('room') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('detail', 'has-error has-feedback') }}">
            {!! Form::label('detail', 'Detail', ['class'=>'']) !!}
            {!! Form::text('detail', null, ['class'=>'form-control']) !!}
            <p class="help-block">Where in the room is it kept?</p>
            {!! Notification::getErrorDetail('detail') !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('working', 'has-error has-feedback') }}">
            {!! Form::label('working', 'Working', ['class'=>'']) !!}
            {!! Form::select('working', [1=>'Yes', 0=>'No'], null, ['class'=>'form-control']) !!}
            <p class="help-block">Is the equipment ready for use?</p>
            {!! Notification::getErrorDetail('working') !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="{{ Notification::hasErrorDetail('managing_role_id', 'has-error has-feedback') }}">
            {!! Form::label('managing_role_id', 'Managing Group', ['class'=>'']) !!}
            {!! Form::select('managing_role_id', [''=>'']+$roleList, null, ['class'=>'form-control js-advanced-dropdown']) !!}
            <p class="help-block">Is a group is responsible for this piece of equipment?</p>
            {!! Notification::getErrorDetail('managing_role_id') !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="{{ Notification::hasErrorDetail('description', 'has-error has-feedback') }}">
            {!! Form::label('description', 'Description', ['class'=>'']) !!}
            {!! Form::textarea('description', null, ['class'=>'form-control']) !!}
            <p class="help-block">Use markdown for formatting</p>
            {!! Notification::getErrorDetail('description') !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group {{ Notification::hasErrorDetail('docs', 'has-error has-feedback') }}">
            {!! Form::label('docs', 'Link to documentation system', ['class'=>'col-sm-3 control-label']) !!}
            <div class="col-sm-9 col-lg-7">
                {!! Form::text('docs', null, ['class'=>'form-control']) !!}
                <p class="help-block">
                    Enter a link to the documentation system for this tool.
                </p>
                {!! Notification::getErrorDetail('docs') !!}
            </div>
        </div>
    </div>
</div>

<div class="form-group {{ Notification::hasErrorDetail('help_text', 'has-error has-feedback') }}">
    {!! Form::label('help_text', 'Help Text', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::textarea('help_text', null, ['class'=>'form-control']) !!}
        <p class="help-block">Helpful hints - make sure to keep most information on the documentation system.</p>
        {!! Notification::getErrorDetail('help_text') !!}
    </div>
</div>


<h4>Health, Safety, Training and Inductions</h4>
<div class="alert alert-info">
    To maintain the integrity of H&S and the training system, only admins and trainers can edit this section.<br/>
    <b>{{ $isTrainerOrAdmin ? "✔️ You can read/write the fields in this area" : "🔒 The fields in this area can not be edited at the moment"}}</b>
</div>

<div class="form-group {{ Notification::hasErrorDetail('ppe', 'has-error has-feedback') }}">
    {!! Form::label('ppe', 'PPE', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('ppe[]', [''=>'']+$ppeList, null, ['class'=>'form-control js-advanced-dropdown', 'multiple', $isTrainerOrAdmin ? "" : "disabled"]) !!}
        {!! Notification::getErrorDetail('ppe') !!}
    </div>
</div>


<div class="form-group alert-danger {{ Notification::hasErrorDetail('dangerous', 'has-error has-feedback') }}">
    {!! Form::label('dangerous', 'Is Bloody Dangerous?', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('dangerous', [0=>'No', 1=>'Yes'], null, ['class'=>'form-control', $isTrainerOrAdmin ? "" : "disabled"]) !!}
        {!! Notification::getErrorDetail('dangerous') !!}
    </div>
</div>

<div class="form-group {{ Notification::hasErrorDetail('requires_induction', 'has-error has-feedback') }}">
    {!! Form::label('requires_induction', 'Requires Induction', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::select('requires_induction', [0=>'No', 1=>'Yes'], null, ['class'=>'form-control', $isTrainerOrAdmin ? "" : "disabled"]) !!}
        {!! Notification::getErrorDetail('requires_induction') !!}
    </div>
</div>

<div class="form-group {{ Notification::hasErrorDetail('induction_category', 'has-error has-feedback') }}">
    {!! Form::label('induction_category', 'Induction Category', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('induction_category', null, ['class'=>'form-control', $isTrainerOrAdmin ? "" : "disabled"]) !!}
        <p class="help-block">By getting inducted on this piece of equipment they are inducted to this category meaning they have access to any other piece of equipment in the same category. i.e. access to all 3D Printers.</p>
        {!! Notification::getErrorDetail('induction_category') !!}
    </div>
</div>

<div class="form-group {{ Notification::hasErrorDetail('access_fee', 'has-error has-feedback') }}">
    {!! Form::label('access_fee', 'Access Fee', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        <div class="input-group">
            <div class="input-group-addon">&pound;</div>
            {!! Form::input('number', 'access_fee', null, ['class'=>'form-control', 'min'=>'0', 'step'=>'1', $isTrainerOrAdmin ? "" : "disabled"]) !!}
        </div>
        <p class="help-block">Is an access fee being charged?</p>
        {!! Notification::getErrorDetail('access_fee') !!}
    </div>
</div>

<div class="form-group {{ Notification::hasErrorDetail('usage_cost', 'has-error has-feedback') }}">
    {!! Form::label('usage_cost', 'Usage Cost', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        <div class="input-group">
            <div class="input-group-addon">&pound;</div>
            {!! Form::input('number', 'usage_cost', null, ['class'=>'form-control', 'min'=>'0', 'step'=>'0.01', $isTrainerOrAdmin ? "" : "disabled"]) !!}
            <div class="input-group-addon">
            Per {!! Form::select('usage_cost_per', [''=>'-', 'hour'=>'hour', 'gram'=>'gram', 'page'=>'page', $isTrainerOrAdmin ? "" : "disabled"], null, ['class'=>'']) !!}
            </div>
        </div>
        <p class="help-block">Does the equipment cost anything to use?</p>
        {!! Notification::getErrorDetail('usage_cost') !!}
    </div>
</div>

<div class="form-group {{ Notification::hasErrorDetail('access_code', 'has-error has-feedback') }}">
    {!! Form::label('access_code', 'Access Code (e.g. for padlock)', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! $isTrainerOrAdmin ? Form::text('access_code', null, ['class'=>'form-control']) : View::getSections()['trustLevel'] !!}
        <p class="help-block">The access code, if applicable, for this tool to be used.</p>
        {!! Notification::getErrorDetail('access_code') !!}
    </div>
</div>

<div class="form-group {{ Notification::hasErrorDetail('induction_instructions', 'has-error has-feedback') }}">
    {!! Form::label('induction_instructions', 'Induction Instructions', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! $isTrainerOrAdmin ? Form::textarea('induction_instructions', null, ['class'=>'form-control']) : View::getSections()['trustLevel'] !!}
        <p class="help-block">Instructions on what to do once an induction is requested. e.g. visit a telegram group.</p>
        <p class="help-block">Use markdown for formatting</p>
        {!! Notification::getErrorDetail('induction_instructions') !!}
    </div>
</div>

<div class="form-group {{ Notification::hasErrorDetail('trained_instructions', 'has-error has-feedback') }}">
    {!! Form::label('trained_instructions', 'Trained Instructions', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! $isTrainerOrAdmin ? Form::textarea('trained_instructions', null, ['class'=>'form-control']) : View::getSections()['trustLevel']  !!}
        <p class="help-block">Instructions for trained users - such as reminders or access codes</p>
        <p class="help-block">Use markdown for formatting</p>
        {!! Notification::getErrorDetail('trained_instructions') !!}
    </div>
</div>

<div class="form-group {{ Notification::hasErrorDetail('trainer_instructions', 'has-error has-feedback') }}">
    {!! Form::label('trainer_instructions', 'Instructions for Trainers', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! $isTrainerOrAdmin ? Form::textarea('trainer_instructions', null, ['class'=>'form-control']) : View::getSections()['trustLevel']  !!}
        <p class="help-block">Only trainers see this - e.g. documents to risk assessments</p>
        <p class="help-block">Use markdown for formatting</p>
        {!! Notification::getErrorDetail('trainer_instructions') !!}
    </div>
</div>

<h3>Misc</h3>
<div class="form-group {{ Notification::hasErrorDetail('asset_tag_id', 'has-error has-feedback') }}">
    {!! Form::label('asset_tag_id', 'Asset Tag ID', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('asset_tag_id', null, ['class'=>'form-control']) !!}
        <p class="help-block">If an asset tag is being placed onto this piece of equipment whats the ID?</p>
        {!! Notification::getErrorDetail('asset_tag_id') !!}
    </div>
</div>

<div class="form-group {{ Notification::hasErrorDetail('device_key', 'has-error has-feedback') }}">
    {!! Form::label('device_key', 'Device Key', ['class'=>'col-sm-3 control-label']) !!}
    <div class="col-sm-9 col-lg-7">
        {!! Form::text('device_key', null, ['class'=>'form-control']) !!}
        <p class="help-block">The id of a ACS device already setup in the database</p>
        {!! Notification::getErrorDetail('device_key') !!}
    </div>
</div>


