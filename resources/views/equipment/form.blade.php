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
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', isset($equipment) ? $equipment->name : null) }}">
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
            <label for="slug">Slug</label>
            <input type="text" name="slug" id="slug" class="form-control" required value="{{ old('slug', isset($equipment) ? $equipment->slug : null) }}">
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
            <label for="manufacturer">Manufacturer</label>
            <input type="text" name="manufacturer" id="manufacturer" class="form-control" value="{{ old('manufacturer', isset($equipment) ? $equipment->manufacturer : null) }}">
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
            <label for="model_number">Model Number</label>
            <input type="text" name="model_number" id="model_number" class="form-control" value="{{ old('model_number', isset($equipment) ? $equipment->model_number : null) }}">
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
            <label for="serial_number">Serial Number</label>
            <input type="text" name="serial_number" id="serial_number" class="form-control" value="{{ old('serial_number', isset($equipment) ? $equipment->serial_number : null) }}">
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
            <label for="colour">Colour</label>
            <input type="text" name="colour" id="colour" class="form-control" value="{{ old('colour', isset($equipment) ? $equipment->colour : null) }}">
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
            <label for="permaloan">Permaloan</label>
            <select name="permaloan" id="permaloan" class="form-control">
                <option value="0" {{ old('permaloan', isset($equipment) ? $equipment->permaloan : null) == 0 ? 'selected' : '' }}>No</option>
                <option value="1" {{ old('permaloan', isset($equipment) ? $equipment->permaloan : null) == 1 ? 'selected' : '' }}>Yes</option>
            </select>
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
            <label for="permaloan_user_id">Permaloan Owner</label>
            <select name="permaloan_user_id" id="permaloan_user_id" class="form-control js-advanced-dropdown">
                <option value=""></option>
                @foreach($memberList as $id => $name)
                    <option value="{{ $id }}" {{ old('permaloan_user_id', isset($equipment) ? $equipment->permaloan_user_id : null) == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
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
            <label for="room">Room</label>
            <select name="room" id="room" class="form-control" required>
                <option value=""></option>
                @foreach($roomList as $id => $name)
                    <option value="{{ $id }}" {{ old('room', isset($equipment) ? $equipment->room : null) == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
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
            <label for="detail">Detail</label>
            <input type="text" name="detail" id="detail" class="form-control" value="{{ old('detail', isset($equipment) ? $equipment->detail : null) }}">
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
            <label for="working">Working</label>
            <select name="working" id="working" class="form-control">
                <option value="1" {{ old('working', isset($equipment) ? $equipment->working : null) == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('working', isset($equipment) ? $equipment->working : null) == 0 ? 'selected' : '' }}>No</option>
            </select>
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
        <div class="{{ $errors->has('maintainer_group_id') ? 'has-error' : '' }}">
            <label for="maintainer_group_id">Maintainer Group</label>
            <select name="maintainer_group_id" id="maintainer_group_id" class="form-control js-advanced-dropdown">
                <option value="">Please select...</option>
                @foreach($maintainerGroupOptions as $id => $name)
                    <option value="{{ $id }}" {{ old('maintainer_group_id', isset($equipment) ? $equipment->maintainer_group_id : null) == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <p class="help-block">Is a group is responsible for this piece of equipment?</p>
            @if($errors->has('maintainer_group_id'))
                <span class="help-block">
                    @foreach($errors->get('maintainer_group_id') as $error)
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
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control">{{ old('description', isset($equipment) ? $equipment->description : null) }}</textarea>
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
            <label for="docs" class="col-sm-3 control-label">Link to documentation system</label>
            <div class="col-sm-9 col-lg-7">
                <input type="text" name="docs" id="docs" class="form-control" value="{{ old('docs', isset($equipment) ? $equipment->docs : null) }}">
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
    <label for="help_text" class="col-sm-3 control-label">Help Text</label>
    <div class="col-sm-9 col-lg-7">
        <textarea name="help_text" id="help_text" class="form-control">{{ old('help_text', isset($equipment) ? $equipment->help_text : null) }}</textarea>
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
    <label for="ppe" class="col-sm-3 control-label">PPE</label>
    <div class="col-sm-9 col-lg-7">
        <select name="ppe[]" id="ppe" class="form-control js-advanced-dropdown" multiple>
            <option value=""></option>
            @foreach($ppeList as $id => $name)
                <option value="{{ $id }}" 
                    @if(old('ppe', isset($equipment) ? $equipment->ppe : null))
                        @if(is_array(old('ppe')))
                            {{ in_array($id, old('ppe')) ? 'selected' : '' }}
                        @else
                            {{ in_array($id, (isset($equipment) ? $equipment->ppe : [])) ? 'selected' : '' }}
                        @endif
                    @endif
                >{{ $name }}</option>
            @endforeach
        </select>
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
    <label for="dangerous" class="col-sm-3 control-label">Is Bloody Dangerous?</label>
    <div class="col-sm-9 col-lg-7">
        <select name="dangerous" id="dangerous" class="form-control">
            <option value="0" {{ old('dangerous', isset($equipment) ? $equipment->dangerous : null) == 0 ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('dangerous', isset($equipment) ? $equipment->dangerous : null) == 1 ? 'selected' : '' }}>Yes</option>
        </select>
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
    <label for="requires_induction" class="col-sm-3 control-label">Requires Induction</label>
    <div class="col-sm-9 col-lg-7">
        <select name="requires_induction" id="requires_induction" class="form-control">
            <option value="0" {{ old('requires_induction', isset($equipment) ? $equipment->requires_induction : null) == 0 ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('requires_induction', isset($equipment) ? $equipment->requires_induction : null) == 1 ? 'selected' : '' }}>Yes</option>
        </select>
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
    <label for="accepting_inductions" class="col-sm-3 control-label">Accepting Induction Requests</label>
    <div class="col-sm-9 col-lg-7">
        <select name="accepting_inductions" id="accepting_inductions" class="form-control">
            <option value=""></option>
            <option value="0" {{ old('accepting_inductions', isset($equipment) ? $equipment->accepting_inductions : null) === '0' || old('accepting_inductions', isset($equipment) ? $equipment->accepting_inductions : null) === 0 ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('accepting_inductions', isset($equipment) ? $equipment->accepting_inductions : null) === '1' || old('accepting_inductions', isset($equipment) ? $equipment->accepting_inductions : null) === 1 ? 'selected' : '' }}>Yes</option>
        </select>
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
    <label for="induction_category" class="col-sm-3 control-label">Induction Category</label>
    <div class="col-sm-9 col-lg-7">
        <input type="text" name="induction_category" id="induction_category" class="form-control" value="{{ old('induction_category', isset($equipment) ? $equipment->induction_category : null) }}">
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
    <label for="induction_instructions" class="col-sm-3 control-label">Instructions to those awaiting training</label>
    <div class="col-sm-9 col-lg-7">
        <textarea name="induction_instructions" id="induction_instructions" class="form-control">{{ old('induction_instructions', isset($equipment) ? $equipment->induction_instructions : null) }}</textarea>
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
    <label for="trained_instructions" class="col-sm-3 control-label">Instructions for trained users</label>
    <div class="col-sm-9 col-lg-7">
        <textarea name="trained_instructions" id="trained_instructions" class="form-control">{{ old('trained_instructions', isset($equipment) ? $equipment->trained_instructions : null) }}</textarea>
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
    <label for="trainer_instructions" class="col-sm-3 control-label">Instructions for Trainers</label>
    <div class="col-sm-9 col-lg-7">
        <textarea name="trainer_instructions" id="trainer_instructions" class="form-control">{{ old('trainer_instructions', isset($equipment) ? $equipment->trainer_instructions : null) }}</textarea>
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
    <label for="access_fee" class="col-sm-3 control-label">Access Fee</label>
    <div class="col-sm-9 col-lg-7">
        <div class="input-group">
            <div class="input-group-addon">&pound;</div>
            <input type="number" name="access_fee" id="access_fee" class="form-control" min="0" step="1" value="{{ old('access_fee', isset($equipment) ? $equipment->access_fee : null) }}">
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
    <label for="usage_cost" class="col-sm-3 control-label">Usage Cost</label>
    <div class="col-sm-9 col-lg-7">
        <div class="input-group">
            <div class="input-group-addon">&pound;</div>
            <input type="number" name="usage_cost" id="usage_cost" class="form-control" min="0" step="0.01" required value="{{ old('usage_cost', isset($equipment) ? $equipment->usage_cost : null) }}">
            <div class="input-group-addon">
                Per 
                <select name="usage_cost_per" id="usage_cost_per" required>
                    <option value="">-</option>
                    <option value="hour" {{ old('usage_cost_per', isset($equipment) ? $equipment->usage_cost_per : null) == 'hour' ? 'selected' : '' }}>hour</option>
                    <option value="gram" {{ old('usage_cost_per', isset($equipment) ? $equipment->usage_cost_per : null) == 'gram' ? 'selected' : '' }}>gram</option>
                    <option value="page" {{ old('usage_cost_per', isset($equipment) ? $equipment->usage_cost_per : null) == 'page' ? 'selected' : '' }}>page</option>
                </select>
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
    <label for="access_code" class="col-sm-3 control-label">Access Code (e.g. for padlock)</label>
    <div class="col-sm-9 col-lg-7">
        <input type="text" name="access_code" id="access_code" class="form-control" value="{{ old('access_code', isset($equipment) ? $equipment->access_code : null) }}">
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
    <label for="asset_tag_id" class="col-sm-3 control-label">Asset Tag ID</label>
    <div class="col-sm-9 col-lg-7">
        <input type="text" name="asset_tag_id" id="asset_tag_id" class="form-control" value="{{ old('asset_tag_id', isset($equipment) ? $equipment->asset_tag_id : null) }}">
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

{{-- Allow anybody to set on creation, but only admins/trainers to edit --}}
<div class="form-group {{ $errors->has('obtained_at') ? 'has-error' : '' }}">
    <label for="obtained_at" class="col-sm-3 control-label">Date Obtained</label>
    <div class="col-sm-9 col-lg-7">
        <input type="text" name="obtained_at" id="obtained_at" class="form-control js-date-select" pattern="\d{4}-\d{2}-\d{2}" value="{{ old('obtained_at', (isset($equipment) && $equipment) ? ($equipment->obtained_at ? $equipment->obtained_at->toDateString() : null) : null) }}">
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
        <label for="removed_at" class="col-sm-3 control-label">Date Removed</label>
        <div class="col-sm-9 col-lg-7">
            <input type="text" name="removed_at" id="removed_at" class="form-control js-date-select" pattern="\d{4}-\d{2}-\d{2}" value="{{ old('removed_at', (isset($equipment) && $equipment) ? ($equipment->removed_at ? $equipment->removed_at->toDateString() : null) : null) }}">
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
