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
                    Hackspace Manchester also has a <a href="https://wiki.hacman.org.uk/">Wiki</a> with a large amount of information regarding the space contained within this.  - </a>
                </p>
            </div>
            <div class="panel-heading"><h3 class="panel-title">Special interest & Team groups</h3></div>
            <div class="panel-body">
                <ul>
                    <p> A number of our Teams alongside Members special interests have telegram groups including: </a>
                    <li><a href="https://t.me/joinchat/Ef01rU1SrP9xJNmwXjHtWA">Hackscreen</a></li>
                    <li><a href="https://t.me/joinchat/Agaj2UBrygQwTBBHDW8jyA">Documentation</a></li>
                    <li><a href="https://t.me/joinchat/E4DcrUDWGvEzlsmJHNNkuQ">Events</a></li>
                    <li><a href="https://t.me/joinchat/D5l4WUPaGRQovcFCLd1X7g">Procurement</a></li>
                    <li><a href="https://t.me/joinchat/AYtZgkk7n1MqvkN9N2fmsA">Woodwork</a></li>
                    <li><a href="https://t.me/joinchat/B8-OC1MTETWTM8vBexJOag">Metalwork</a></li>
                    <li><a href="https://t.me/joinchat/B3NEGUNG_uwIUSveltLMxQ">Laser Training</a></li>
                    <li><a href="https://t.me/joinchat/DZNJNRJimIP7XoyvDArnUg">3d Printing</a></li>
                    <li><a href="https://t.me/joinchat/D5l4WVMQOkytQcFk-tRjuQ">Diversity</a></li>
                    <li><a href="https://t.me/joinchat/AYtZgkh3BSRgpoA9LvQ3Hg">CNC</a></li>
                    <li><a href="https://t.me/joinchat/AoAv41LtXqjULCvqsbGwrA">Space Bikes</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Members Meetings</h3></div>
            <div class="panel-body">
                <p>Our Members Meetings take place every 2 months on the 1st of each month (unless that occurs on a Wednesday then the meeting will take place on the 2nd)</p>

                <strong>2019 Dates</strong>
                <ul>
                    <li>Friday 1st February 7.30pm</li>
                    <li>Monday  1st April 7.30pm</li>
                    <li>Saturday 1st June 7.30pm</li>
                    <li>Thursday 1st August 7.30pm</li>
                    <li>Tuesday 1st October 7.30pm</li>
                    <li>Sunday 1st December 7.30pm</li>
                </ul>
            </div>
            
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Rules and Policies</h3></div>
            <div class="panel-body">
                <p>We have started to improve some of the various polices that govern Hackspace Manchester, as they are clarified and confirmed they will appear here.</p>

                <strong>Core Rules</strong>
                <ul>
                    <li><a href="{{ route('resources.policy.view', 'rules') }}">Rules</a></li>
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
