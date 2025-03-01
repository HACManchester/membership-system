@extends('layouts.main')

@section('meta-title')
Member Roles and Groups
@stop

@section('page-title')
Member Roles and Groups
@stop

@section('content')

<p>
    Update group names and descriptions.<br />
    Assign members to specific roles in order to control how much access they have and what they can do
</p>

    @foreach($roles as $role)
        <hr />

        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-8">
                <form method="POST" action="{{ route('roles.update', $role->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" value="{{ $role->title }}" class="form-control input-lg" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="2" placeholder="Short description">{{ $role->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="email_public">Public Email</label>
                        <input type="text" name="email_public" id="email_public" value="{{ $role->email_public }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="email_private">Private Email</label>
                        <input type="text" name="email_private" id="email_private" value="{{ $role->email_private }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="slack_channel">Telegram Channel</label>
                        <input type="text" name="slack_channel" id="slack_channel" value="{{ $role->slack_channel }}" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-default">Save</button>
                </form>
                <small>{{ $role->name }}</small>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
                <table class="table">
                @foreach($role->users as $user)
                    <tr>
                        <td width="50%">{{ $user->name }}</td>
                        <td>
                        <form method="POST" action="{{ route('roles.users.destroy', [$role->id, $user->id]) }}" class="form-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-default btn-xs">Remove</button>
                        </form>
                        </td>
                    </tr>
                @endforeach
                    <tr>
                        <form method="POST" action="{{ route('roles.users.store', $role->id) }}" class="form-inline">
                            @csrf
                            <td>
                                <select name="user_id" class="form-control js-advanced-dropdown">
                                    <option value="">Add a member</option>
                                    @foreach($memberList as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <button type="submit" class="btn btn-default btn-sm">Add</button>
                            </td>
                        </form>
                    </tr>
                </table>
            </div>
        </div>

    @endforeach

@stop