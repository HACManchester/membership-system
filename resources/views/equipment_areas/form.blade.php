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
        {!! Form::label('name', 'Name', ['class'=>'']) !!}
        {!! Form::text('name', null, ['class'=>'form-control', 'required']) !!}
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
        {!! Form::label('slug', 'Slug', ['class'=>'']) !!}
        {!! Form::text('slug', null, ['class'=>'form-control', 'required']) !!}
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
        {!! Form::label('description', 'Description', ['class'=>'']) !!}
        {!! Form::textarea('description', null, ['class'=>'form-control']) !!}
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
    {!! Form::label('area_coordinators', 'Area Coordinators', ['class'=>'col-sm-3 control-label']) !!}
    
    {!! Form::select('area_coordinators[]', $memberList, null, ['class'=>'form-control js-advanced-dropdown', 'multiple']) !!}
    @if($errors->has('area_coordinators'))
        <span class="help-block">
            @foreach($errors->get('area_coordinators') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </span>
    @endif
</div>