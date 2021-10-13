<script>
    function roleChange(){
            var items;
            if (document.getElementById("role").value == "Admin") {
                items = ["Admin"];
            } else {
                items = ["Barista", "Security", "Waiter"]
            }
            var str = ""
            for (var item of items) {
                str += "<option>" + item + "</option>"
            }
            document.getElementById("job").innerHTML = str;
    }
</script>
@extends('layouts.app')

@section('content')
@auth
@if (Auth::user()->role == 'Admin')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Add New User</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="role" class="col-md-4 col-form-label text-md-right">{{ __('Role') }}</label>

                            <div class="col-md-6">
                                <select name="role" id="role" class="form-control" required autofocus onChange="roleChange()">
                                <option value="Worker">Worker</option>
                                <option value="Admin">Admin</option>
                                </select>
                                @error('role')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="job" class="col-md-4 col-form-label text-md-right">{{ __('Job') }}</label>

                            <div class="col-md-6">
                                <select name="job" id="job" class="form-control" required autofocus>
                                <option value="Barista">Barista</option>
                                <option value="Security">Security</option>
                                <option value="Waiter">Waiter</option>
                                </select>

                                @error('job')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Add
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
