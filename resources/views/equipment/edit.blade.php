@extends('layouts.main')

@section('meta-title')
    Edit a piece of equipment
@stop

@section('page-title')
    <a href="{{ route('equipment.index') }}">Tools &amp; Equipment</a> > <a href="{{ route('equipment.show', $equipment->slug) }}">{{ $equipment->name }}</a> > Edit
@stop

@section('content')

<div class="row" style="margin-bottom: 20px;">
    <div class="col-xs-12">

        <form action="{{ route('equipment.update', $equipment->slug) }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('equipment/form')

            <div class="row">
                <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>

        </form>

    </div>
</div>

<div class="row">
    <div class="col-xs-12">

        <div class="well">
            <h4>Photos</h4>

            @if ($equipment->hasPhoto())
                <div class="row">
                @for($i=0; $i < $equipment->getNumPhotos(); $i++)
                    <div class="col-xs-12 col-md-4 col-lg-2">
                    <img src="{{ $equipment->getPhotoUrl($i) }}" class="img-thumbnail" width="200" />
                    <form action="{{ route('equipment.photo.destroy', [$equipment->slug, $i]) }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-primary">Delete</button>
                    </form>
                    </div>
                @endfor
                </div>
            @endif


            <form action="{{ route('equipment.photo.store', $equipment->slug) }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
                @csrf

                <div class="form-group {{ $errors->has('photo') ? 'has-error' : ''}}">
                    <label for="photo" class="col-sm-3 control-label">Equipment Photo</label>
                    <div class="col-sm-9 col-lg-7">
                        <input name="photo" class="form-control" type="file" accept="image/*" capture="camera" id="inputPhoto">
                        <p class="help-block">Photos will be cropped to a square so please ensure the item is centered appropriately</p>
                        @if($errors->has('photo'))
                            <span class="help-block">
                                @foreach($errors->get('photo') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </div>

            </form>
        </div>

    </div>
</div>
@stop