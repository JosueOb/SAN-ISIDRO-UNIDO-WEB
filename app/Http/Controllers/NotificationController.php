<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function api_problems(Request $request){
        $notifications = $request->user()->notifications;
        $problem_category = Category::where('slug', 'problema')->first();
        // dd($notifications);
        $problem_notifications = $notifications->filter(function($notification, $key) use($problem_category){
            return $notification->data['post']['category_id'] === $problem_category->id;
        }); 
        $unread_notifications = $problem_notifications->filter(function($notification, $key){
            return $notification->read_at == null;
        });

        // dd($problem_notifications);
        return [
            'problem_notifications'=>$problem_notifications, 
            'unread_notifications'=>$unread_notifications
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
