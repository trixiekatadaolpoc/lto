@extends('layouts.login')


@section('title')
MQUE
@stop




@section('content')

<style type="text/css">

    ul li{
        display: inline-block;
        position: relative;
        line-height: 21px;
        text-align: left;
    }
</style>



<title>Index</title>
<body>
<div class="header">
    <div class="container">
        <div class="logo">
            <h1><a href="#">MQUES</a></h1>
        </div>
    
    <div class="top-nav">
        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">About Us</a></li>
            <li><a href="#"><i class="glyphicon glyphicon-user"></i>{{ $session['firstname'] }}</a></li>
            <li><a href="{{ URL::to('/teller/logout') }}">Logout</a></li>
        </ul>
    </div>
    </div>
</div>
<div class="container">

    <br>
        <div class="premiumm">
            <div class="pre-topp">
            <h5>{{ $counter_label }} Counter</h5>
            <p>It may change by the teller. Suspend queue manually.</p>
            </div>
        </div>
        <br><br><br>

         
<br><br><br>
<ul align="left">
<div class="pending">
    <h3>Pending Number : {{ $queue_pending }}</h3>
    <input type="text">
</div>
<div class="total">
    <h3>Total Number : </h3>
    <input type="text">
</div>
<div class="duration">
    <h3>Duration time of teller {{ $session->counter_id }} : </h3>
    <input id="minutes" type="text" style="width: 60px; border: none; background-color:none; font-size: 50px; font-weight: bold;">:
  <input id="seconds" type="text" style="width: 60px; border: none; background-color:none; font-size: 50px; font-weight: bold;">
    
</div>
<li><div class="project-fur">
<div align="center"><br><br>
    <h2>Teller {{ $session->counter_id }}</h2>
</div>
<div class="fur">
<div class="fur1"><br>
    <h6 class="fur-name">Serving Queue Number : {{ $current_serve }} </h6>
<br><br><br><br>
<div align="center">
<h1>
    
</h1></div><br><br><br><br>
<span>For Evaluation</span>

<span>Customer Name: {{ (isset($client_info->name) ? $client_info->name : '') }}</span>
<span>Transaction: </span>
</div>
</div>     
</div></li>

</ul>

<div class="future">                            
        <form method="post" action="">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <input type="hidden" name="current_serve" value="{{ $current_serve }}" />
            <button class="hvr-sweep-to-right" style="float:right">Next Queue</button>
        </form>
        </div><br><br>
       
    </div>
</div>
<br><br><br><br><br>
<br>
	<div class="footer-bottom">
		<div class="container">
			<div class="col-md-4 footer-logo">
				<h2><a href="index.html">MQUES</a></h2>
			</div>
			<div class="col-md-8 footer-class">
				<p >© 2015 MQUES. Team Artisan. </p>
			</div>
		<div class="clearfix"> </div>
	 	</div>
	</div>

<script type="text/javascript">

    $(function(){
        setInterval( function(){
            var formData = new FormData();
            formData.append('current_serve', {{ $current_serve }});
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
              url: '/dashboard',
              data: formData,
              processData: false,
              contentType: false,
              type: 'POST',
              success: function(data){
                window.location.reload();
              }
            });
        }, 100000);
    });


         // set minutes
    var mins = 10;

     // calculate the seconds (don't change this! unless time progresses at a      different speed for you...)
    var secs = mins * 10;
    var timeout;

    function countdown() {
      timeout = setTimeout('Decrement()', 1000);
    }

    function Decrement() {
      if (document.getElementById) {
        minutes = document.getElementById("minutes");
        seconds = document.getElementById("seconds");
        // if less than a minute remaining
        if (seconds < 59) {
          seconds.value = secs;
        } else {
          minutes.value = getminutes();
          seconds.value = getseconds();
        }
        secs--;
        if (secs < 0) {
          clearTimeout(timeout);
          return;
        }
        countdown();
      }
    }

    function getminutes() {
      // minutes is seconds divided by 60, rounded down
      mins = Math.floor(secs / 60);
      return ("0" + mins).substr(-2);
    }

    function getseconds() {
      // take mins remaining (as seconds) away from total seconds remaining
      return ("0" + (secs - Math.round(mins * 60))).substr(-2);
    }
    {!! ( $current_serve > 0 ) ? 'countdown();':'' !!}
    

    </script>
</body>
@stop