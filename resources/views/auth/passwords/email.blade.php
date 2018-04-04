@extends('_layouts.app')
@section('pageTitle','Forget Password')

@section('content')
  @component('components.breadcrumb')
    Forget Password
  @endcomponent

  @include('alert::bootstrap')

  @if (session('status'))
    <div class="alert alert-success">
      {{ session('status') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Recover your lost password</h5>

          <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
              <label for="email"> E-Mail address</label>
              <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                     name="email" value="{{ old('email') }}" placeholder="Email" required>

              @if ($errors->has('email'))
                <div class="invalid-feedback">
                  {{ $errors->first('email') }}
                </div>
              @endif
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-envelope"></i> Send Password Reset Link
              </button>
            </div>
          </form>
        </div>
      </div>
      <div class="text-center">
        <a class="btn btn-link" href="{{ route('login') }}">
          Back to Login
        </a>
      </div>
    </div>
  </div>

@endsection
