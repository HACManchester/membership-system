@if ($active)
    <label class="label label-success" data-toggle="tooltip" data-placement="top" title="You are allowed to use the space">Access to the space</label>
@else
    <label class="label label-danger" data-toggle="tooltip" data-placement="top" title="You do not have permission to use the space">No access to the space</label>
@endif
