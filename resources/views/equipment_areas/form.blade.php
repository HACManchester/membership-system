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
        <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', isset($equipmentArea) ? $equipmentArea->name : null) }}">
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
        <input type="text" name="slug" id="slug" class="form-control" required value="{{ old('slug', isset($equipmentArea) ? $equipmentArea->slug : null) }}">
        <p class="help-block">This is the unique reference for the area, no special characters. i.e. visual-arts or 3d-printing</p>
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
        <textarea name="description" id="description" class="form-control">{{ old('description', isset($equipmentArea) ? $equipmentArea->description : null) }}</textarea>
        @if($errors->has('description'))
            <span class="help-block">
                @foreach($errors->get('description') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif        
    </div>
</div>

<div class="form-group {{ $errors->has('area_coordinators') ? 'has-error' : '' }}">
    <label for="area_coordinators" class="col-sm-3 control-label">Area Coordinators</label>
    
    <select name="area_coordinators[]" id="area_coordinators" class="form-control js-advanced-dropdown" multiple>
        @foreach($memberList as $id => $name)
            <option value="{{ $id }}" 
                @if(old('area_coordinators') && is_array(old('area_coordinators')))
                    {{ in_array($id, old('area_coordinators')) ? 'selected' : '' }}
                @elseif(isset($equipmentArea) && $equipmentArea->areaCoordinators)
                    {{ in_array($id, $equipmentArea->areaCoordinators->pluck('id')->toArray()) ? 'selected' : '' }}
                @endif
            >{{ $name }}</option>
        @endforeach
    </select>
    @if($errors->has('area_coordinators'))
        <span class="help-block">
            @foreach($errors->get('area_coordinators') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </span>
    @endif
</div>