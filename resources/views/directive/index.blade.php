@extends('layouts.dashboard')
@section('page-subtitle')
    Módulo Directiva
@endsection
@section('page-header')
    Listado de miembros
@endsection
@section('item-directive')
    active
@endsection
@section('item-directive-collapse')
    show
@endsection
@section('item-directive-list')
    active
@endsection
@section('content')
<div class="row">
    <div class="col">
        @include('layouts.alerts')
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="card card-primary">
            <div class="card-body">
                <form action="">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <select class="custom-select" required name="optionSearch">
                                <option value="">Buscar</option>
                                <option value="1">Nombre</option>
                                <option value="2">Apellido</option>
                                <option value="3">Cargo</option>
                            </select>
                        </div>
                        <input type="text" class="form-control" id="inputSearch" name="search" required>
                        <div class="input-group-prepend">
                            <button type="submit" class="btn btn-dark">
                                    <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="card card-primary">
            <div class="card-header">
                <div class="row">
                    <div class="col">
                        <h4 class="d-inline">Miembros de la directiva registrados</h4>
                        <a href="{{route('members.create')}}" class="btn btn-primary float-right">Agregar</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <a href="#" class="btn btn-outline-dark btn-sm"><i class="fas fa-filter"></i> Todos</a>
                        <a href="#" class="btn btn-outline-dark btn-sm"><i class="fas fa-filter"></i> Activos</a>
                        <a href="#" class="btn btn-outline-dark btn-sm"><i class="fas fa-filter"></i> Inactivos</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                            @if (count($members)>0)
                            <table class="table table-light table-hover">
                                <thead>
                                    <tr>
                                        <th class="">Id</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Cargo</th>
                                        <th>Estado</th>
                                        <th colspan="3">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members as $member)
                                        <tr>
                                            
                                            <td>{{ $member->id}}</td>
                                            <td>{{$member->first_name}}</td>
                                            <td>{{$member->last_name}}</td>
                                            <td>{{$member->position->name}}</td>
                                            <td><span class="badge badge-pill {{$member->state ? 'badge-success': 'badge-danger'}}">{{$member->state ? 'Activo': 'Inactivo'}}</span></td>
                                            <td width='10px'>
                                                <a href="{{route('members.show',$member->id)}}" class="btn btn-info">Ver</a>
                                            </td>
                                            <td width='10px'>
                                                <a href="{{route('members.edit',$member->id)}}" class="btn btn-secondary"> Editar</a>
                                            </td>
                                            <td width='10px'>
                                                <a href="#" class="btn btn-danger"  data-toggle="modal" data-target="#deleteMember{{$member->id}}">Eliminar</a>
                                                <!--Modal-->
                                                <div class="modal fade" id="deleteMember{{$member->id}}" tabindex="-1" role="dialog" aria-labelledby="eliminarMiembro" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Confirmar eliminación</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            ¿Está seguro de eliminar al miembro de {{ $member->first_name }}?
                                                        </div>
                                                        <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                        {{-- <button type="button" class="btn btn-primary">Eliminar</button> --}}
                                                        <form action="{{ route('members.destroy', $member->id) }}" method="POST">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                                        </form>
                                                        </div>
                                                    </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-center">Nigún miembros de la directiva registrado</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <p class="text-muted m-0 float-right">Total: {{$members->total()}}</p>
                <nav>
                    {{$members->links()}}
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection