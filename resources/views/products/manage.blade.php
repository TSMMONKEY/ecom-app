@extends('layouts.admin')

@section('title', 'Dashboard')
@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible text-white" role="alert">
            <span class="text-sm">{{ session('success') }}</span>
            <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-lg-12 col-md-12 mb-md-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7 d-flex justify-content-between align-items-center">
                            <!-- Flash message for success -->
                            <h6 class="mb-0">All Products</h6>
                            <!-- Moved button directly under All Products and aligned left -->
                        </div>
                        <div class="mt-2 text-start"> <!-- Added text-start class for left alignment -->
                            <a href="{{ route('product.add') }}" class="btn btn-primary">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                                <span class="font-weight-bold ms-1">Add Product</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Companies</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Description</th> <!-- New column added -->
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Members</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Budget</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        More</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    <img src="storage/{{ $product->image }}" class="avatar avatar-sm me-3"
                                                        alt="xd">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $product->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-sm">
                                            <span class="text-xs font-weight-bold text-truncate"
                                                style="display: inline-block; max-width: 200px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                                                {{ $product->paragraph }}
                                            </span>
                                        </td> <!-- New description added -->
                                        <td>
                                            <div class="avatar-group mt-2">
                                                <a href="javascript:;" class="avatar avatar-xs rounded-circle"
                                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                    title="Ryan Tompson">
                                                    <img src="dashboard/./assets/img/team-1.jpg" alt="team1">
                                                </a>
                                                <a href="javascript:;" class="avatar avatar-xs rounded-circle"
                                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                    title="Romina Hadid">
                                                    <img src="dashboard/./assets/img/team-2.jpg" alt="team2">
                                                </a>
                                                <a href="javascript:;" class="avatar avatar-xs rounded-circle"
                                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                    title="Alexander Smith">
                                                    <img src="dashboard/./assets/img/team-3.jpg" alt="team3">
                                                </a>
                                                <a href="javascript:;" class="avatar avatar-xs rounded-circle"
                                                    data-bs-toggle="tooltip" data-bs-placement="bottom" title="Jessica Doe">
                                                    <img src="dashboard/./assets/img/team-4.jpg" alt="team4">
                                                </a>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="text-xs font-weight-bold"> ${{ $product->price }} </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="progress-wrapper mx-auto">
                                                <div
                                                    class="dropdown text-center
                                            ">
                                                    <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fa fa-ellipsis-v text-secondary" aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu px-2 py-3 ms-sm-n4 ms-n5"
                                                        aria-labelledby="dropdownTable">
                                                        <li><a class="dropdown-item border-radius-md"
                                                                href="javascript:;">Action</a></li>
                                                        <li><a class="dropdown-item border-radius-md"
                                                                href="javascript:;">Another action</a>
                                                        </li>
                                                        <li><a class="dropdown-item border-radius-md"
                                                                href="javascript:;">Something else
                                                                here</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
