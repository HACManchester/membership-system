@if ($user->online_only)
    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2 pull-left">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Online Only user</h3>
                </div>
                <div class="panel-body">
                    <h4>You're an online only user, and not a member of the space yet.</h4>
                    <p>
                        To become a member, you'll need to edit your account and:
                    </p>
                    <ul>
                        <li>Untick "Online only user"</li>
                        <li>Add address details</li>
                        <li>Add emergency contact details</li>
                        <li>Decide how much to pay for your membership</li>
                    </ul>
                    <p>
                        Once you've edited your account, you'll be able to set up your monthly direct debit.
                    </p>
                    <a class="btn btn-secondary" href="{{ route('account.edit', [$user->id]) }}">
                        <i class="material-icons">mode_edit</i> 
                        Edit your account to become a member
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif