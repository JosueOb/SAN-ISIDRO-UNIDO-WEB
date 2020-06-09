<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function api_problems(Request $request){
        $notifications = $request->user()->notifications;
        // dd($notifications);
        $problem_category = Category::where('slug', 'problema')->first();
        $problem_notifications = $notifications->filter(function($notification) use($problem_category){
            return $notification->data['post']['category_id'] === $problem_category->id;
        }); 
        // dd($problem_notifications);

        $unread_notifications = $problem_notifications->filter(function($notification){
            return $notification->unread();
        });
        // dd($unread_notifications);

        return [
            'problem_notifications'=>array_values($problem_notifications->toArray()),//se re-indexa 
            'unread_notifications'=>array_values($unread_notifications->toArray()),//se re-indexa
        ];
    }
    public function problems(Request $request){

        $user = $request->user();
        $notifications = $user->notifications;
        $problem_category = Category::where('slug', 'problema')->first();

        $problem_notifications = $notifications->filter(function($notification, $key) use($problem_category){
            return $notification->data['post']['category_id'] === $problem_category->id;
        }); 

        return view('notifications.problem',[
            'all_problem_notifications'=>$problem_notifications,
        ]);
    }
}
