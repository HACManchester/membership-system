@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group">
    <div class="{{ $errors->has('name') ? 'has-error' : '' }}">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', isset($maintainerGroup) ? $maintainerGroup->name : null) }}">
        <p class="help-block">Aim for a short but descriptive name, i.e. Visual Arts</p>
        @if($errors->has('name'))
            <span class="help-block">
                @foreach($errors->get('name') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group">
    <div class="{{ $errors->has('slug') ? 'has-error' : '' }}">
        <label for="slug">Slug</label>
        <input type="text" name="slug" id="slug" class="form-control" required value="{{ old('slug', isset($maintainerGroup) ? $maintainerGroup->slug : null) }}">
        <p class="help-block">This is the unique reference for the area, no special characters. i.e. woodwork or sewing-machines</p>
        @if($errors->has('slug'))
            <span class="help-block">
                @foreach($errors->get('slug') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif        
    </div>
</div>

<div class="form-group">
    <div class="{{ $errors->has('description') ? 'has-error' : '' }}">
        <label for="description">Description</label>
        <textarea name="description" id="description" class="form-control">{{ old('description', isset($maintainerGroup) ? $maintainerGroup->description : null) }}</textarea>
        @if($errors->has('description'))
            <span class="help-block">
                @foreach($errors->get('description') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif        
    </div>
</div>

<div class="form-group {{ $errors->has('equipment_area_id') ? 'has-error' : '' }}">
    <label for="equipment_area_id" class="col-sm-3 control-label">Represented by area</label>
    
    <select name="equipment_area_id" id="equipment_area_id" class="form-control">
        <option value="">Please select...</option>
        @foreach($equipmentAreaOptions as $id => $name)
            <option value="{{ $id }}" {{ old('equipment_area_id', isset($maintainerGroup) ? $maintainerGroup->equipment_area_id : null) == $id ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
    </select>
    @if($errors->has('equipment_area_id'))
        <span class="help-block">
            @foreach($errors->get('equipment_area_id') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </span>
    @endif
</div>

<div class="form-group {{ $errors->has('maintainers') ? 'has-error' : '' }}">
    <label for="maintainers" class="col-sm-3 control-label">Maintainers</label>
    
    <select name="maintainers[]" id="maintainers" class="form-control js-advanced-dropdown" multiple>
        @foreach($memberList as $id => $name)
            <option value="{{ $id }}" 
                @if(old('maintainers') && is_array(old('maintainers')))
                    {{ in_array($id, old('maintainers')) ? 'selected' : '' }}
                @elseif(isset($maintainerGroup) && $maintainerGroup->maintainers)
                    {{ in_array($id, $maintainerGroup->maintainers->pluck('id')->toArray()) ? 'selected' : '' }}
                @endif
            >{{ $name }}</option>
        @endforeach
    </select>
    @if($errors->has('maintainers'))
        <span class="help-block">
            @foreach($errors->get('maintainers') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </span>
    @endif
</div>