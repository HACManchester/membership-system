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
        <label for="name">Name</label>
        <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', isset($course) ? $course->name : null) }}">
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
        <label for="slug">Slug</label>
        <input type="text" name="slug" id="slug" class="form-control" required value="{{ old('slug', isset($course) ? $course->slug : null) }}">
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
        <label for="description">Description</label>
        <textarea name="description" id="description" class="form-control">{{ old('description', isset($course) ? $course->description : null) }}</textarea>
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
        <label for="format">Format</label>
        <select name="format" id="format" class="form-control" required>
            <option value="">Please select...</option>
            @foreach(\BB\Entities\Course::formatOptions() as $value => $label)
                <option value="{{ $value }}" {{ old('format', isset($course) ? $course->format : null) == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
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
        <label for="format_description">Format Description</label>
        <input type="text" name="format_description" id="format_description" class="form-control" value="{{ old('format_description', isset($course) ? $course->format_description : null) }}">
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
        <label for="frequency">Frequency</label>
        <select name="frequency" id="frequency" class="form-control" required>
            <option value="">Please select...</option>
            @foreach(\BB\Entities\Course::frequencyOptions() as $value => $label)
                <option value="{{ $value }}" {{ old('frequency', isset($course) ? $course->frequency : null) == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
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
        <label for="frequency_description">Frequency Description</label>
        <input type="text" name="frequency_description" id="frequency_description" class="form-control" value="{{ old('frequency_description', isset($course) ? $course->frequency_description : null) }}">
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
        <label for="wait_time">Wait Time</label>
        <input type="text" name="wait_time" id="wait_time" class="form-control" value="{{ old('wait_time', isset($course) ? $course->wait_time : null) }}">
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