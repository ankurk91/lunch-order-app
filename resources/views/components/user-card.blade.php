<section class="card mb-3">
  <div class="card-body text-center text-sm-left">
    <div class="row">
      <div class="col-md-4">
        @if(optional($user->profile)->avatar)
          <a href="{{$user->profile->avatar}}" target="_blank">
            <img class="rounded-circle border" src="{{$user->profile->avatar}}?sz=100" alt="avatar" height="100">
            <span class="sr-only">View large image</span>
          </a>
        @else
          <i class="fas fa-6x fa-user-circle text-muted"></i>
        @endif
      </div>
      <div class="col-md-8">
        <p class="h5 my-1 text-truncate" title="{{$user->email}}">{{$user->email}}</p>
        <p class="text-capitalize text-muted">{{implode(', ',$user->roles->pluck('name')->toArray())}}</p>
        <p class="small text-muted mb-0">
          Joined -
          <time data-local="time-ago"
                data-format="%B %e, %Y %l:%M%P"
                datetime="{{$user->created_at->toIso8601String()}}">
            {{$user->created_at}}</time>
        </p>
      </div>
    </div>
  </div>
</section>
