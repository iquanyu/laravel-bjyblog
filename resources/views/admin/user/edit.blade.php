@extends('admin.layouts.admin')

@section('title', translate('Edit Administrator'))

@section('nav', translate('Edit Administrator'))

@section('content')
    <form class="form-inline" action="{{ url('admin/user/update', [$data->id]) }}" method="post">
        {{ csrf_field() }}
        <table class="table table-striped table-bordered table-hover">
            <tr>
                <th>{{ translate('User Name') }}</th>
                <td>
                    <input class="form-control" type="text" name="name" value="{{ $data->name }}">
                </td>
            </tr>
            <tr>
                <th>{{ translate('Email') }}</th>
                <td>
                    <input class="form-control" type="text" name="email" value="{{ $data->email }}">
                </td>
            </tr>
            <tr>
                <th>{{ translate('Password') }}</th>
                <td>
                    <input class="form-control" type="text" name="password">
                </td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <input class="btn btn-success" type="submit" value="{{ translate('Submit') }}">
                </td>
            </tr>
        </table>
    </form>
@endsection
