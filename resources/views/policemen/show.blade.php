@extends('layouts.dashboard')
@section('page-subtitle')
    Módulo Policía
@endsection
@section('page-header')
    Policía
@endsection
@section('item-police')
    active
@endsection
@section('item-police-collapse')
    show
@endsection
@section('item-police-list')
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
            <div class="card-header">
                    <div class="row">
                        <div class="col">
                            <h4  class="d-inline">Detalle del policía</h4>
                            @can('policemen.edit')
                            {{-- Si el usuario tiene al menos un rol del sistema web no se presenta la opción de editar --}}
                                @if ($police->getRelationshipStateRolesUsers('policia'))
                                    <a href="{{route('policemen.edit', $police->id)}}" class="btn btn-secondary float-right"><i class="far fa-edit"></i> Editar</a>
                                @endif
                            @endcan
                        </div>
                    </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <p><strong>Nombre:</strong> {{$police->first_name}}</p>
                        <p><strong>Apellidos:</strong> {{$police->last_name}}</p>
                        <p><strong>Corre electrónico:</strong> {{$police->email}}</p>
                        <p><strong>Estado:</strong> {{$police->getRelationshipStateRolesUsers('policia') ? 'Activo': 'Inactivo'}}</p>
                        <p><strong>Número telefónico:</strong> {{$police->number_phone ?: 'No registrado'}}</p>
                        <p><strong>Corre verificado:</strong> {{$police->email_verified_at ?: 'No verificado'}}</p>


                    </div>
                    <div class="col text-center">
                        <img class="rounded-circle dark" style="width: 8rem; height: 8rem; object-fit: cover;" src="{{$police->getAvatar()}}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection