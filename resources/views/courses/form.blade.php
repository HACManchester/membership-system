@if ($errors->any())
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
        {!! Form::label('name', 'Name', ['class' => '']) !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
        <p class="help-block">
            Aim for a short but descriptive name, i.e. Laser cutting induction
        </p>
        @if ($errors->has('name'))
            <span class="help-block">
                @foreach ($errors->get('name') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group">
    <div class="{{ $errors->has('slug') ? 'has-error' : '' }}">
        {!! Form::label('slug', 'Slug', ['class' => '']) !!}
        {!! Form::text('slug', null, ['class' => 'form-control', 'required']) !!}
        <p class="help-block">
            This is the unique reference for the area, no special characters. i.e. visual-arts or 3d-printing
        </p>
        @if ($errors->has('slug'))
            <span class="help-block">
                @foreach ($errors->get('slug') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group">
    <div class="{{ $errors->has('description') ? 'has-error' : '' }}">
        {!! Form::label('description', 'Description', ['class' => '']) !!}
        {!! Form::textarea('description', null, ['class' => 'form-control']) !!}
        <p class="help-block">
            All the details about this induction that a member should know before signing up or attending.
        </p>
        @if ($errors->has('description'))
            <span class="help-block">
                @foreach ($errors->get('description') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group">
    <div class="{{ $errors->has('format') ? 'has-error' : '' }}">
        {!! Form::label('format', 'Format', ['class' => '']) !!}
        {!! Form::select('format', ['' => 'Please select...'] + \BB\Entities\Course::formatOptions()->toArray(), null, [
            'class' => 'form-control',
            'required',
        ]) !!}
        @if ($errors->has('format'))
            <span class="help-block">
                @foreach ($errors->get('format') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group">
    <div class="{{ $errors->has('format_description') ? 'has-error' : '' }}">
        {!! Form::label('format_description', 'Format Description', ['class' => '']) !!}
        {!! Form::text('format_description', null, ['class' => 'form-control']) !!}
        <p class="help-block">
            Short description about this format. Will be displayed quite thin, so keep it brief.
        </p>
        @if ($errors->has('format_description'))
            <span class="help-block">
                @foreach ($errors->get('format_description') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group">
    <div class="{{ $errors->has('frequency') ? 'has-error' : '' }}">
        {!! Form::label('frequency', 'Frequency', ['class' => '']) !!}
        {!! Form::select('frequency', ['' => 'Please select...'] + \BB\Entities\Course::frequencyOptions()->toArray(), null, [
            'class' => 'form-control',
            'required',
        ]) !!}
        @if ($errors->has('frequency'))
            <span class="help-block">
                @foreach ($errors->get('frequency') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group">
    <div class="{{ $errors->has('frequency_description') ? 'has-error' : '' }}">
        {!! Form::label('frequency_description', 'Frequency Description', ['class' => '']) !!}
        {!! Form::text('frequency_description', null, ['class' => 'form-control']) !!}
        <p class="help-block">
            Short description about this induction's frequency. Will be displayed quite thin, so keep it brief.
        </p>
        @if ($errors->has('frequency_description'))
            <span class="help-block">
                @foreach ($errors->get('frequency_description') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>

<div class="form-group">
    <div class="{{ $errors->has('wait_time') ? 'has-error' : '' }}">
        {!! Form::label('wait_time', 'Wait Time', ['class' => '']) !!}
        {!! Form::text('wait_time', null, ['class' => 'form-control']) !!}
        <p class="help-block">
            Expected wait time. Please enter in the format of "[range] [unit of time]", for example "1-2 weeks"
        </p>
        @if ($errors->has('wait_time'))
            <span class="help-block">
                @foreach ($errors->get('wait_time') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </span>
        @endif
    </div>
</div>
