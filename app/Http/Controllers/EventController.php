<?php

namespace App\Http\Controllers;

use App\Category;
use App\Helpers\OnesignalNotification;
use App\Http\Requests\EventRequest;
use App\Phone;
use App\Post;
use App\Resource;
use App\Http\Middleware\PotectedReportPosts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\HelpersClass\AdditionalData as AdditionalDataCls;
use App\Notifications\PostNotification;

class EventController extends Controller
{
    protected $additionalData;

    public function __construct()
    {
        $this->additionalData = new AdditionalDataCls();
        $this->middleware(PotectedReportPosts::class)->only('show','edit','update','destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category_event = Category::where('slug', 'evento')->first();
        $events = $category_event->posts()->paginate();
        return view('events.index',[
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = Category::where('slug', 'evento')->first();
        $subcategories = $category->subcategories()->get();
        return view('events.create',[
            'subcategories'=>$subcategories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EventRequest $request)
    {
        $category_event = Category::where('slug', 'evento')->first();

        $validated = $request->validated();
        //se decodifica un string JSON en un array recursivo
        $ubication = json_decode($validated['ubication'], true);
        //Se le agrega al arreglo el detalle de la descripción de ubicación
        $ubication['description'] = $validated['ubication-description'];

        $this->additionalData->setInfoEvent([
            'responsible' => $validated['responsible'],
            "range_date" => [
                'start_date' => $validated['start-date'],
                    'end_date' => $validated['end-date'],
                    'start_time' => $validated['start-time'],
                    'end_time' => $validated['end-time'],
            ]
        ]);

        $event = new Post();
        $event->title = $validated['title'];
        $event->description = $validated['description'];
        $event->state = true;
        $event->ubication = $ubication;
        $event->user_id = $request->user()->id;
        $event->category_id = $category_event->id;
        $event->subcategory_id = $validated['id'];
        // $event->additional_data = json_encode($additional_data);
        $event->additional_data = $this->additionalData->getAll();
        $event->save();

        $phones = $validated['phone_numbers'];
        foreach($phones as $phone){
            $phone_number = new Phone(['phone_number' => $phone]);
            $event->phones()->save($phone_number);
        }

        if($request->file('new_images')){
            foreach($request->file('new_images') as $image){
                Resource::create([
                    'url'=> $image->store('event_images', 's3'),
                    'post_id' => $event->id,
                    'type'=>'image',
                ]);
            }
        }
        //Notificar a todos los usuarios afiliados de la aplicación móvil
        $title_notification_event = $event->title;
        $description_notification_event = "El usuario " . $request->user()->getFullName() . " ha reportado un evento";
        // $request->user()->notify(new PostNotification($event, $title_notification_event, $description_notification_event));
        OnesignalNotification::sendNotificationBySegments($title_notification_event, $description_notification_event, [
            "post" => $event
        ]);

        session()->flash('success', 'Servicio público registrado con éxito');
        return response()->json(['success'=>'Datos recibidos correctamente']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        $additional_data = $post->additional_data;
        $event_range_date = $additional_data['event']['range_date'];
        $event_responsible = $additional_data['event']['responsible'];
        $ubication = $post->ubication;
        $images = $post->resources()->where('type', 'image')->get();
        return view('events.show', [
            'event' => $post,
            'event_range_date' => $event_range_date,
            'event_responsible' => $event_responsible,
            'ubication' => $ubication,
            'images'=> $images,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        $category = Category::where('slug', 'evento')->first();
        $subcategories = $category->subcategories()->get();
        $additional_data = $post->additional_data;
        $event_range_date = $additional_data['event']['range_date'];
        $event_responsible = $additional_data['event']['responsible'];
        $ubication = $post->ubication;
        $images = $post->resources()->where('type', 'image')->get();
        return view('events.edit',[
            'event'=>$post,
            'subcategories'=>$subcategories,
            'event_range_date' => $event_range_date,
            'event_responsible' => $event_responsible,
            'ubication' => $ubication,
            'images'=> $images,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EventRequest $request, Post $post)
    {
        $validated = $request->validated();
        //se decodifica un string JSON en un array recursivo
        $ubication = json_decode($validated['ubication'], true);
        //Se le agrega al arreglo el detalle de la descripción de ubicación
        $ubication['description'] = $validated['ubication-description'];

        $additional_data = [
            'event'=>[
                'responsible'=> $validated['responsible'],
                'range_date' => [
                    'start_date' => $validated['start-date'],
                    'end_date' => $validated['end-date'],
                    'start_time' => $validated['start-time'],
                    'end_time' => $validated['end-time'],
                ]
            ]
        ];

        $post->title = $validated['title'];
        $post->description = $validated['description'];
        $post->ubication = $ubication;//Se devuelve una representación de un JSON;
        $post->subcategory_id = $validated['id'];
        $post->additional_data = $additional_data;
        $post->save();

        $newPhones = $validated['phone_numbers'];
        $oldPhones = $post->phones;

        $this->deleteOldPhones($oldPhones, $newPhones);
        $this->saveNewPhones($newPhones, $oldPhones, $post);

        //Se verifica si alguna imagen del envento registrado anteriormenete, haya sido eliminada
        $oldEventImages = $request['old_images'];
        $oldCollectionEventImages = $post->resources()->where('type', 'image')->get();

        if($oldEventImages){
            foreach($oldCollectionEventImages as $oldImageEvent){
                $oldImageUrl = $oldImageEvent->url;

                if($this->searchDeletedImages($oldImageUrl, $oldEventImages)){
                    //Eliminar a la imagen de la bdd y del local storage
                    $post->resources()->where('type', 'image')
                                        ->where('url', $oldImageUrl)->delete();
                    if(Storage::disk('s3')->exists($oldImageUrl)){
                        Storage::disk('s3')->delete($oldImageUrl);
                    }
                }
            }
        }else{
            //En caso no recibir el arreglo de las imagenes registradas con el reporte,
            //se verifica si el reporte contiene imágenes
            if(count($oldCollectionEventImages) > 0){
                //Si el reporte contiene imágenes, se procede a eliminar todas las imágenes
                foreach ($oldCollectionEventImages as $oldImage) {
                    $oldImageUrl = $oldImage->url;
                    if(Storage::disk('s3')->exists($oldImageUrl)){
                        Storage::disk('s3')->delete($oldImageUrl);
                    }
                }

                $post->resources()->where('type', 'image')->delete();
            }
        }

        if($request->file('new_images')){
            foreach($request->file('new_images') as $image){

                Resource::create([
                    'url'=> $image->store('event_images', 's3'),
                    'post_id' => $post->id,
                    'type'=>'image',
                ]);
            }
        }

        //Notificar a todos los usuarios afiliados de la aplicación móvil
        $title_notification_event = $post->title;
        $description_notification_event = "Evento actualizado!";
        // $request->user()->notify(new PostNotification($post, $title_notification_event, $description_notification_event));
        OnesignalNotification::sendNotificationBySegments($title_notification_event, $description_notification_event, [
            "post" => $post
        ]);

        session()->flash('success', 'Servicio público actualizado con éxito');
        return response()->json([
            'success'=>'Datos recibidos correctamente', 
            'redirect'=>route('events.index'),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $message = '';
        if($post->state){
            $post->state = false;
            $message='desactivado';
        }else{
            $post->state = true;
            $message='activado';
        }
        $post->save();
        
        return back()->with('success', "Evento $message con éxito");
    }
    /**
     * Check if any images were deleted
     *
     * @param  string  $search
     * @param  array  $array
     * @return boolean $imageIsDeleted
     */

    public function searchDeletedImages($search, $array){
        $imageIsDeleted = true;
        foreach($array as $image){
            if($image === $search){
                $imageIsDeleted = false;
            }
        }
        return $imageIsDeleted;
    }

      /**
     * Función que permite detemrinar la existencia de un número telefónico en un arreglo dado.
     *
     * @param  string $phone_search 
     * @param  Collection $phone_array
     * @return boolean
     */
    public function isThereAPhoneNumber($phone_search, $phone_array){
        foreach($phone_array as $phone){
            if($phone === $phone_search){
                return true;
            }
        }
        return false;
    }

    /**
     * Función que elimina los teléfonos registrado a partir de la existencia del 
     * mismo en un determinado arreglo
     */
    public function deleteOldPhones($oldPhones, $newPhones){
        foreach($oldPhones as $oldPhone){
            //Si el teléfono fue elimnado
            if(!$this->isThereAPhoneNumber($oldPhone->phone_number, $newPhones)){
                $oldPhone->delete();
            }
        }
    }

    /**
     * Función que guarda en la base de datos los números telefónicos que sean diferentes
     * a los almacenados anteriormente
     */
    public function saveNewPhones($newPhones, $oldPhones, $publicService){
        $oldPhones = $oldPhones->pluck('phone_number')->toArray();
        foreach($newPhones as $newPhone){
            //Si el teléfono es nuevo
            if(!$this->isThereAPhoneNumber($newPhone, $oldPhones)){
                $phone_number = new Phone(['phone_number' => $newPhone]);
                $publicService->phones()->save($phone_number);
            }
        }
    }
}
