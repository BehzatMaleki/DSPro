@extends('layouts.app')

@section('content')
@auth
@if (Auth::user()->role == 'Admin')
<div class="container">
    <div class="row justify-content-center mt-5">
    <div class="card m-2">
        <div class="card-header">Admins</div>
        <div class="card-body">
            <table class="table border border-dark table-striped">
                <thead>
                    <tr>
                        <th scope='row' class='border border-dark'>User Name</th>
                        <th scope='row' class='border border-dark'>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $active_users = DB::select("select * from users where role='Admin' order by name");
                        foreach ($active_users as $row) {
                            echo "<tr>";
                            echo "<td class='border border-dark'>".$row->name."</td>";
                                echo "<td class='border border-dark'>";
                                    if(Auth::user()->id <>$row->id){echo "<a href='/user_remove?u_id=".$row->id."' class='no'>Remove</a>";}
                                echo "</td>";
                            echo "</tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card m-2">
        <div class="card-header">Workers</div>
        <div class="card-body">
            <table class="table border border-dark table-striped">
                <thead>
                    <tr>
                        <th scope='row' class='border border-dark'>User Name</th>
                        <th scope='row' class='border border-dark'>Job</th>
                        <th scope='row' class='border border-dark'>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $active_users = DB::select("select * from users where role='Worker' order by job, name");
                        foreach ($active_users as $row) {
                            echo "<tr>";
                            echo "<td class='border border-dark'>".$row->name."</td>";
                                echo "<td class='border border-dark'>".$row->job."</td>";
                                echo "<td class='border border-dark'>";
                                    if(Auth::user()->id <>$row->id){echo "<a href='/user_remove?u_id=".$row->id."' class='no'>Remove</a>";}
                                echo "</td>";
                            echo "</tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
    <div class="text-center text-secondary px-5 pt-5 links"><H5>Only Admin users have access to this page.</H5></div>
@endif
@else
    <div class="text-center px-5 pt-2 links"><a href="{{ route('login') }}">Login</a></div>
@endauth
@endsection
