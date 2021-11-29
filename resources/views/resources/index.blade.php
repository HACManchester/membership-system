@extends('layouts.main')

@section('meta-title')
Resources
@stop

@section('page-title')
Resources
@stop

@section('content')

    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Forum & Telegram Groups</h3></div>
            <div class="panel-body">
                <p>
                    Our two main communication channels are our forum/list and our telegram group. 
                </p>
                <ul class="list-unstyled">
                    <li><a href="https://list.hacman.org.uk">Forum/List</a></li>
                    <li><a href="https://t.me/HACManchester">Telegram Group</a></li>
                </ul>
                <p>
                    Hackspace Manchester also has a <a href="https://docs.hacman.org.uk/">documentation system</a>
                </p>
            </div>
            <div class="panel-heading"><h3 class="panel-title">Area & Team groups</h3></div>
            <div class="panel-body">
                <ul>
                    <li><a href="https://t.me/joinchat/AYtZgkk7n1MqvkN9N2fmsA">Woodwork</a></li>
                    <li><a href="https://t.me/joinchat/B8-OC1MTETWTM8vBexJOag">Metalwork</a></li>
                    <li><a href="https://t.me/joinchat/DZNJNRJimIP7XoyvDArnUg">3d Printing</a></li>
                    <li><a href="https://t.me/joinchat/AYtZgkh3BSRgpoA9LvQ3Hg">CNC</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Announcements & Members Meetings</h3></div>
            <div class="panel-body">
                <p class="lead">Announcements</p>
                <p>
                    You can find the main <a href="https://list.hacman.org.uk/c/announcements/23">announcements</a> on the forum Announcements category.
                </p>
                <p class="lead">Member Meetings</p>
                <p>
                    Our Members Meetings take place every 1-2 months, around the first Monday of the month.<br/>
                    They're called on the <a href="https://list.hacman.org.uk/c/announcements/member-meetings/35">forum</a>
                </p>
                <p class="lead">Board Meetings</p>
                Board announcements and meetings can also be found on the <a href="https://list.hacman.org.uk/c/announcements/board/34">forum</a>. 
                Members can ask public questions here with regards to space management, but any private or sensitive questions should be emailed to board@hacman.org.uk
            </div>
            
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Rules and Policies</h3></div>
            <div class="panel-body">
                <p>The space has core rules, as well as various policies to keep the space working for the community.</p>

                <strong>Member Handbook</strong>
                <p>
                    The <a href="https://list.hacman.org.uk/t/member-handbook/2890/1">handbook</a> is an overview of the space, policies, and functioning of the space. Do take a read!
                </p>
                <strong>Core Rules</strong>
                <ul>
                    <li><a href="https://hacman.org.uk/rules">Rules</a></li>
                    <li><a href="{{ route('resources.policy.view', 'code-of-conduct') }}">Code of Conduct</a></li>
                </ul>

                <strong>Policies</strong>
                <ul>
                    <li><a href="{{ route('resources.policy.view', '3-week-bins') }}">3 Week Bins</a></li>
                    <li><a href="{{ route('resources.policy.view', 'member-storage') }}">Member Storage</a></li>
                    <li><a href="{{ route('resources.policy.view', 'trusted-member') }}">How To Get In</a></li>

                </ul>
                <strong>Bank Details</strong>
                <ul>
                    <li>Name: Manchester Makers Ltd</a></li>
                    <li>Sort Code: 20-25-43</a></li>
                    <li>Account Number: 6323 7958</li>
                </ul>

            </div>
        </div>
    </div>




@stop
