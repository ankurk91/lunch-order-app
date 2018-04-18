@extends('_layouts.app')

@section('pageTitle','Products')

@section('content')
  <section class="row d-flex">
    <div class="col">
      @component('components.breadcrumb')
        Products
      @endcomponent
    </div>
    <div class="col pt-1 text-right">
      <ul class="nav justify-content-end">
        <li class="nav-item">
          <a class="nav-link" href="{{route('admin.products.create')}}">
            <i class="fas fa-plus-circle"></i> Add new product
          </a>
        </li>
      </ul>
    </div>
  </section>


  @include('alert::bootstrap')

  <section class="card mb-3">
    <div class="card-body">
      <form id="search-form" class="form-inline" method="GET"
            action="{{route('admin.products.index')}}">

        <select class="form-control mb-2 mb-sm-0 mr-sm-2" name="per_page">
          <option disabled>Per page</option>
          @foreach([10,30,50] as $n)
            <option value="{{$n}}" @if(request('per_page') == $n) selected @endif>{{$n}}</option>
          @endforeach
        </select>

        <select class="form-control mb-2 mb-sm-0 mx-sm-2 text-capitalize" name="active_status">
          <option disabled>Status</option>
          @foreach(['active','inactive','all'] as $status)
            <option value="{{$status}}"
                    @if(request('active_status','active') === $status) selected @endif>{{$status}}</option>
          @endforeach
        </select>

        <input type="text" class="form-control mb-2 mb-sm-0 mr-sm-2" placeholder="Search" name="search"
               value="{{request('search')}}" autofocus>

        <button type="submit" value="1" class="btn btn-primary mb-0 mb-sm-0 mr-sm-2">
          <i class="fa fa-search fa-fw"></i>Search
        </button>

      </form>
    </div>
  </section>

  <section class="table-responsive">
    <table class="table table-striped table-light table-bordered table-hover" id="list-table">
      <thead>
      <tr>
        <th class="w-25">Name</th>
        <th>Unit price</th>
        @if(request('active_status') === 'all')
          <th>Status</th>
        @endif
        <th class="w-25">Actions</th>
      </tr>
      </thead>
      <tbody>
      @forelse($products as $product)
        <tr>
          <td class="align-middle">{{$product->name}}</td>
          <td class="align-middle">{{money($product->unit_price)}}</td>
          @if(request('active_status') === 'all')
            <td class="align-middle h5">
              @if($product->active)
                <span class="badge badge-success">Active</span>
              @else
                <span class="badge badge-danger">In-active</span>
              @endif
            </td>
          @endif
          <td class="text-center">
            <a href="{{route('admin.products.edit',$product)}}" class="btn btn-sm btn-secondary mb-0"><i
                class="fas fa-edit"></i> Edit</a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" class="text-center">No records found</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </section>

  @if($products->total() > 1)
    <div class="row">
      <div class="col-md-4 mb-sm-0 mb-3 text-center text-sm-left">
        <h5 class="mt-sm-2 mt-0 mb-0">Found {{$products->total()}} entries</h5>
      </div>
      <div class="col-md-8 d-flex">
        <div class="mx-auto ml-sm-auto mr-sm-0 table-responsive-sm">
          {{$products->appends(request()->all())->links()}}
        </div>
      </div>
    </div>
  @endif

@endsection