@extends('layouts.dashboard')
@section('page-subtitle')
    Módulo Roles
@endsection
@section('page-header')
    Editar rol
@endsection
@section('item-role')
    active
@endsection
@section('item-role-collapse')
    show
@endsection
@section('item-role-list')
    active
@endsection
@section('content')
<div class="row">
    <div class="col">
        <div class="card card-primary">
            <div class="card-body">
                <form action="{{route('roles.update', $role->id)}}" method="POST" class="needs-validation">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="form-group col-12 col-md-6">
                            <label for="name">Nombre</label>
                            <input id="name" type="text" class="form-control  @error('name') is-invalid @enderror" name="name" value="{{old('name') ?: $role->name}}" autofocus required>
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label for="slug">URL amigable</label>
                            <input id="slug" type="text" class="form-control @error('slug') is-invalid @enderror" name="slug" value="{{old('slug') ?: $role->slug}}">
                            @error('slug')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" rows="5">{{ old('description') ?: $role->description }}</textarea>
                        {{-- <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description') ?: $role->description }}"> --}}
                        @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <hr>
                    <div class="form-group">
                        <h3>Permiso especial</h3>
    
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="all-access" name="special" value="all-access" class="custom-control-input @error('special') is-invalid @enderror"  {{ $role->special == 'all-access' ? 'checked' : ''}}>
                            <label class="custom-control-label" for="all-access">Acceso total</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="no-access" name="special" value="no-access" class="custom-control-input @error('special') is-invalid @enderror" {{ $role->special == 'no-access' ? 'checked' : ''}}>
                            <label class="custom-control-label" for="no-access">Ningún acceso</label>
                        </div>
                        <button type="button" class="btn btn-outline-dark btn-sm" id="unselect" onclick="event.preventDefault();
                        document.querySelectorAll('[name=special]').forEach((x) => x.checked=false);">Desseleccionar</button>
                        @error('special')
                        <div class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                    <hr>
                    <h3>Asignar permisos</h3>
                    <div class="form-group">
                        <ul class="list-unstyled">
                            @foreach ($permissions as $permission)
                                <li>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="permissions[]" class="custom-control-input @error('permissions') is-invalid @enderror" id="{{$permission->name}}" value="{{$permission->id}}" 
                                        @foreach ($rolePermissions as $rolPermission)
                                            @if ($rolPermission->id == $permission->id)
                                                {{'checked'}}
                                            @endif
                                        @endforeach>
                                        <label class="custom-control-label" for="{{$permission->name}}">
                                            {{$permission->name}}
                                            <em>({{$permission->description ?: 'Sin descripción'}})</em>
                                            </label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        @error('permissions')
                        <div class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
    
                    <div class="form-group col-4 offset-4">
                        <button type="submit" class="btn btn-primary btn-block">
                            Guardar
                            <i class="far fa-save"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection