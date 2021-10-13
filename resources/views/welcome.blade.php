<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Workers Schedule System</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            a.ok {
                font-size:x-small;
                color:blue;
            }

            a.no {
                font-size:x-small;
                color:red;
            }
        </style>
    </head>
    <body>
        <!-- Preaper database for the first time use -->
        <?php
            use Illuminate\Database\Schema\Blueprint;
            use Illuminate\Support\Facades\Schema;
            use App\User;
            try {
                $result= DB::select("SHOW DATABASES LIKE 'DB::getDatabaseName()'");
                $a_user= DB::select('select * from users');
                $adms = 0;
                $ta_id = 0;
                foreach ($a_user as $row) {if ($row->role == "Admin"){$adms+=1;};if ($row->name == "Temp_Admin"){$ta_id=$row->id;}}
                if ($adms == 0){
                    //Create Temp_Admin if there were no admin
                    $h_pass=Hash::make('Temp_Admin');
                    DB::select("insert into users (name, password, role, job) values ('Temp_Admin', '$h_pass', 'Admin', 'Admin')");
                }else{
                    if ($adms > 1 && $ta_id>0){
                        //Remove Temp_Admin if another admin has been defined
                        $sql= "delete from users where id=$ta_id";
                        $result= DB::delete($sql);
                    }
                }
            } catch (Exception $e) {
                // Create connection
                $servername = Config::get('database.connections.mysql.host');
                $username = Config::get('database.connections.mysql.username');
                $password = Config::get('database.connections.mysql.password');
                $conn = new mysqli($servername, $username, $password);
                // Create database
                $sql = "CREATE DATABASE ".DB::getDatabaseName();
                $result=$conn->query($sql);
                $conn->close();
                // Create tables
                Schema::create('users', function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->string('name');
                    $table->string('password');
                    $table->string('role');
                    $table->string('job');
                    $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                    $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
                });
                Schema::create('requests', function (Blueprint $table) {
                    $table->bigIncrements('req_id');
                    $table->string('name');
                    $table->enum('role', ['Admin', 'Worker']);
                    $table->enum('job', ['Barista', 'Security', 'Waiter', 'Admin']);
                    $table->enum('shift', ['Morning', 'Evening']);
                    $table->date('date');
                });
                Schema::create('shifts', function (Blueprint $table) {
                    $table->bigIncrements('shift_id');
                    $table->string('name');
                    $table->enum('role', ['Admin', 'Worker']);
                    $table->enum('job', ['Barista', 'Security', 'Waiter', 'Admin']);
                    $table->enum('shift', ['Morning', 'Evening']);
                    $table->date('date');
                });
                //Create Temp_Admin
                $h_pass=Hash::make('Temp_Admin');
                DB::select("insert into users (name, password, role, job) values ('Temp_Admin', '$h_pass', 'Admin', 'Admin')");
            }
        ?>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="fixed-top px-5 pt-5 links">
                    <?php 
                        $now = now();
                        $n=$now->format('Y-m-d');
                        if (isset($_GET['week_start'])){
                            $weekStartDate = $_GET['week_start'];
                            $temp_d = str_replace('-', '/', $weekStartDate);
                            $weekEndDate = date('Y-m-d',strtotime($temp_d . "+6 days"));
                        }else{
                            $weekStartDate = $now->startOfWeek()->format('Y-m-d');
                            $weekEndDate = $now->endOfWeek()->format('Y-m-d');
                        }
                        $temp_d = str_replace('-', '/', $weekStartDate);
                        $wdt = new DateTime($temp_d);
                        $week = $wdt->format("W");

                        $free_lnk="";
                        $shift_lnk="";
                    ?>

                    <!-- Home page header -->
                    @auth
                        <?php $logined=1; ?>
                        <div class="links float-left"><a class="nav-link">{{ Auth::user()->name }} [{{ Auth::user()->role }}, {{ Auth::user()->job }}]</a></div>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                        <div class="links float-right"><a class="nav-link" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a></div>

                        <!-- Define record action links -->
                        @if (Auth::user()->role == 'Admin')
                            <div class="links float-right"><a class="nav-link" href="/manage">Manage Users</a></div>
                            <div class="links float-right"><a class="nav-link" href="{{ route('register') }}">Add User</a></div>
                            <?php 
                                $free_lnk_1=""; 
                                $free_lnk_2="";
                                $free_lnk_3="";
                                $free_lnk_4="";

                                $shift_lnk_1="<a href='/shift_confirm?s_req_id=";
                                $shift_lnk_2="' class='ok'>Confirm</a> | ";
                                $shift_lnk_3="<a href='/shift_req_cancel?s_req_id=";
                                $shift_lnk_4="' class='no'>Denay</a>";
                            ?>
                        @else
                            <?php 
                                $free_lnk_1="<a href='/shift_req?shift=morning&s_date=";
                                $free_lnk_2="' class='ok'>Morning</a> | ";
                                $free_lnk_3="<a href='/shift_req?shift=evening&s_date=";
                                $free_lnk_4="' class='ok'>Evening</a>";
                                
                                $shift_lnk_1="";
                                $shift_lnk_2="";
                                $shift_lnk_3="<br><a href='/shift_req_cancel?s_req_id=";
                                $shift_lnk_4="' class='no'>Cancel</a>";
                            ?>
                        @endif

                    @else
                        <?php $logined=0; ?>
                        <div class="links"><a class="nav-link float-right" href="{{ route('login') }}">Login</a></div>
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="float-left"><h3 class="text-secondary"><b>Workers Schedule System</b> [week no. <?php echo $week ?>]</h3></div>
                <div class="float-right">
                    <!-- week browser-->
                    <?php
                        $temp_d = str_replace('-', '/', $weekStartDate);
                        $Pr_weekStart = date('Y-m-d',strtotime($temp_d . "-7 days"));
                        $Nx_weekStart = date('Y-m-d',strtotime($temp_d . "+7 days"));
                    ?>
                    <a href="/?week_start=<?php echo $Pr_weekStart?>"><button type="button"><<<</button></a>  
                    <a href="/?week_start=<?php echo $Nx_weekStart?>"><button type="button">>>></button></a>
                </div>
                <div class="links">
                    <table class="table border border-dark">
                        <thead>
                            <tr>
                                <!-- Column headers -->
                                <?php 
                                    $d=$weekStartDate;
                                    echo "<th scope='row' class='border border-dark'></th>";
                                    while($d<=$weekEndDate){
                                        $temp_d = str_replace('-', '/', $d);
                                        $timestamp = strtotime($temp_d);
                                        if ($d==$n){$tdf=" style='background-color:#e5e5e5;' class='border border-dark text-secondary'";}else{$tdf=" class='border border-dark text-secondary'";}
                                        echo "<th scope='col'".$tdf.">".date('l', $timestamp)."<br>".$d."</th>";
                                        $d = date('Y-m-d',strtotime($temp_d . "+1 days"));
                                    }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
                                    use Illuminate\Support\Facades\DB;
                                    $active_shifts = DB::select('select * from shifts');
                                    $shift_requests = DB::select('select * from requests');

                                    //Barista row
                                    $d=$weekStartDate;
                                    $row_job="Barista";
                                    echo "<th scope='row' style='line-height: 50px;' class='text-secondary border border-dark'>".$row_job."</th>";
                                    while($d<=$weekEndDate){
                                        echo "<th scope='col' class='border border-dark'";
                                        $si=0;
                                        $cs=0;
                                        $rid=0;
                                        $sid=0;
                                        $nm="";
                                        $sft="";

                                        //Scan shifts table
                                        foreach ($active_shifts as $row) {
                                            if ($row->job==$row_job && $row->date==$d){
                                                echo " style='background-color:#95fea4;'>".$row->shift."<br>".$row->name;
                                                $si=1;
                                                $sid=$row->shift_id;
                                                $nm=$row->name;
                                                $sft=$row->shift;
                                            }
                                        }
                                        //Scan requests table
                                        foreach ($shift_requests as $row) {
                                            if ($row->job==$row_job && $row->date==$d){
                                                if ($si==0){echo ">".$row->shift."<br>".$row->name;}
                                                $cs=1;
                                                $rid=$row->req_id;
                                                $nm=$row->name;
                                                $sft=$row->shift;
                                            }
                                        }
                                        //Create cell contents
                                        if($logined){
                                            if ($free_lnk_1 <> ""){$free_lnk = $free_lnk_1.$d.$free_lnk_2.$free_lnk_3.$d.$free_lnk_4;}else{$free_lnk = "";}
                                            if (Auth::user()->role == "Admin" && $si==1){$id=$sid; $shift_lnk_3="<a href='/shift_cancel?shift_id=";}else{$id=$rid;$shift_lnk_3="<a href='/shift_req_cancel?s_req_id=";}
                                            if ($shift_lnk_1 <> ""){$shift_lnk = $shift_lnk_1.$id.$shift_lnk_2.$shift_lnk_3.$id.$shift_lnk_4;}else{$shift_lnk = $shift_lnk_3.$id.$shift_lnk_4;}

                                            if (Auth::user()->role == "Admin") {
                                                if ($cs==1 && $d>$n){
                                                    echo "<br>$shift_lnk";
                                                }
                                                if ($si==1){
                                                    echo "<br>$shift_lnk_3".$sid."$shift_lnk_4";
                                                }
                                            }else{
                                                if (Auth::user()->job == $row_job){
                                                    if ($cs==0 && $d>$n){
                                                        if ($si<>1){echo ">$free_lnk";}
                                                    }
                                                    if ($cs==1 && $d>$n){
                                                        if(Auth::user()->name == $nm){echo "$shift_lnk";}
                                                    }
                                                }
                                            }
                                        }

                                        echo "</th>";
                                        $temp_d = str_replace('-', '/', $d);
                                        $d = date('Y-m-d',strtotime($temp_d . "+1 days"));
                                    }
                                ?>
                            </tr>
                            <tr>
                                <?php
                                    $d=$weekStartDate;

                                    //Security row
                                    $row_job="Security";
                                    echo "<th scope='row' style='line-height: 50px;' class='text-secondary border border-dark'>".$row_job."</th>";
                                    while($d<=$weekEndDate){
                                        echo "<th scope='col' class='border border-dark'";
                                        $si=0;
                                        $cs=0;
                                        $rid=0;
                                        $sid=0;
                                        $nm="";
                                        $sft="";

                                        //Scan shifts table
                                        foreach ($active_shifts as $row) {
                                            if ($row->job==$row_job && $row->date==$d){
                                                echo " style='background-color:#feeb9a;'>".$row->shift."<br>".$row->name;
                                                $si=1;
                                                $sid=$row->shift_id;
                                                $nm=$row->name;
                                                $sft=$row->shift;
                                            }
                                        }
                                        //Scan requests table
                                        foreach ($shift_requests as $row) {
                                            if ($row->job==$row_job && $row->date==$d){
                                                echo ">".$row->shift."<br>".$row->name;
                                                $cs=1;
                                                $rid=$row->req_id;
                                                $nm=$row->name;
                                                $sft=$row->shift;
                                            }
                                        }
                                        //Create cell contents
                                        if($logined){
                                            if ($free_lnk_1 <> ""){$free_lnk = $free_lnk_1.$d.$free_lnk_2.$free_lnk_3.$d.$free_lnk_4;}else{$free_lnk = "";}
                                            if (Auth::user()->role == "Admin" && $si==1){$id=$sid; $shift_lnk_3="<a href='/shift_cancel?shift_id=";}else{$id=$rid;$shift_lnk_3="<a href='/shift_req_cancel?s_req_id=";}
                                            if ($shift_lnk_1 <> ""){$shift_lnk = $shift_lnk_1.$id.$shift_lnk_2.$shift_lnk_3.$id.$shift_lnk_4;}else{$shift_lnk = $shift_lnk_3.$id.$shift_lnk_4;}

                                            if (Auth::user()->role == "Admin") {
                                                if ($cs==1 && $d>$n){
                                                    echo "<br>$shift_lnk";
                                                }
                                                if ($si==1){
                                                    echo "<br>$shift_lnk_3".$sid."$shift_lnk_4";
                                                }
                                            }else{
                                                if (Auth::user()->job == $row_job){
                                                    if ($cs==0 && $d>$n){
                                                        if ($si<>1){echo ">$free_lnk";}
                                                    }
                                                    if ($cs==1 && $d>$n){
                                                        if(Auth::user()->name == $nm){echo "$shift_lnk";}
                                                    }
                                                }
                                            }
                                        }

                                        echo "</th>";
                                        $temp_d = str_replace('-', '/', $d);
                                        $d = date('Y-m-d',strtotime($temp_d . "+1 days"));
                                    }
                                ?>
                            </tr>
                            <tr>
                                <?php
                                    $d=$weekStartDate;

                                    //Waiter row
                                    $row_job="Waiter";
                                    echo "<th scope='row' style='line-height: 50px;' class='text-secondary border border-dark'>".$row_job."</th>";
                                    while($d<=$weekEndDate){
                                        echo "<th scope='col' class='border border-dark'";
                                        $si=0;
                                        $cs=0;
                                        $rid=0;
                                        $sid=0;
                                        $nm="";
                                        $sft="";

                                        //Scan shifts table
                                        foreach ($active_shifts as $row) {
                                            if ($row->job==$row_job && $row->date==$d){
                                                echo " style='background-color:#d1effe;'>".$row->shift."<br>".$row->name;
                                                $si=1;
                                                $sid=$row->shift_id;
                                                $nm=$row->name;
                                                $sft=$row->shift;
                                            }
                                        }
                                        //Scan requests table
                                        foreach ($shift_requests as $row) {
                                            if ($row->job==$row_job && $row->date==$d){
                                                if ($si==0){echo ">".$row->shift."<br>".$row->name;}
                                                $cs=1;
                                                $rid=$row->req_id;
                                                $nm=$row->name;
                                                $sft=$row->shift;
                                            }
                                        }
                                        //Create cell contents
                                        if($logined){
                                            if ($free_lnk_1 <> ""){$free_lnk = $free_lnk_1.$d.$free_lnk_2.$free_lnk_3.$d.$free_lnk_4;}else{$free_lnk = "";}
                                            if (Auth::user()->role == "Admin" && $si==1){$id=$sid; $shift_lnk_3="<a href='/shift_cancel?shift_id=";}else{$id=$rid;$shift_lnk_3="<a href='/shift_req_cancel?s_req_id=";}
                                            if ($shift_lnk_1 <> ""){$shift_lnk = $shift_lnk_1.$id.$shift_lnk_2.$shift_lnk_3.$id.$shift_lnk_4;}else{$shift_lnk = $shift_lnk_3.$id.$shift_lnk_4;}

                                            if (Auth::user()->role == "Admin") {
                                                if ($cs==1 && $d>$n){
                                                    echo "<br>$shift_lnk";
                                                }
                                                if ($si==1){
                                                    echo "<br>$shift_lnk_3".$sid."$shift_lnk_4";
                                                }
                                            }else{
                                                if (Auth::user()->job == $row_job){
                                                    if ($cs==0 && $d>$n){
                                                        if ($si<>1){echo ">$free_lnk";}
                                                    }
                                                    if ($cs==1 && $d>$n){
                                                        if(Auth::user()->name == $nm){echo "$shift_lnk";}
                                                    }
                                                }
                                            }
                                        }

                                        echo "</th>";
                                        $temp_d = str_replace('-', '/', $d);
                                        $d = date('Y-m-d',strtotime($temp_d . "+1 days"));
                                    }
                                ?>
                            </tr>
                        </tbody>
                    </table>      
                </div>
            </div>
        </div>
    </body>
</html>
