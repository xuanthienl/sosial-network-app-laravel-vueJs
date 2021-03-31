<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Messages;
use App\Events\sendNewNessage;

class MessagesController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function contacts(Request $request) {
        $messages = Messages::whereNull('deleted_at')->where(function ($query) {
            $query->where('from', Auth::user()->id)
                  ->orWhere('to', Auth::user()->id);
        })->orderBy('id', 'desc')->select('from', 'to')->get();
        $arrID = [];
        if(count($messages) > 0) {
            foreach($messages as $message) {
                if($message->from == Auth::user()->id) {
                    $arrID[] = $message->to;
                } else {
                    $arrID[] = $message->from;
                }
            }
            $arrUsers = [];
            foreach(array_unique($arrID) as $id) {
                $user = User::whereNull('deleted_at')->where('id', $id)->select('id', 'username', 'name', 'image_profile')->first();
                $messages = Messages::whereNull('deleted_at')->where('from', $id)->where('to', Auth::user()->id)->where('read', 0)->orderBy('id', 'desc')->get();
                $message = Messages::whereNull('deleted_at')->where(function($q) use ($id) {
                    $q->where('from', Auth::user()->id);
                    $q->where('to', $id);
                })->orWhere(function($q) use ($id) {
                    $q->where('from', $id);
                    $q->where('to', Auth::user()->id);
                })->orderBy('id', 'desc')->first();
                $arrUsers[] = collect($user)->merge(['unread' => count($messages), 'last_message' => $message->created_at]);
            }
            return response()->json($arrUsers, 200);
        }
        return response()->json(['empty' => true], 200);
    }
    public function getMessagesFor(Request $request, $id) {
        Messages::whereNull('deleted_at')->where('from', $id)->where('to', Auth::user()->id)->update(['read' => true]);

        $authID = Auth::user()->id;
        $messages = Messages::whereNull('deleted_at')->where(function($q) use ($id, $authID) {
            $q->where('from', $authID);
            $q->where('to', $id);
        })->orWhere(function($q) use ($id, $authID) {
            $q->where('from', $id);
            $q->where('to', $authID );
        })
        ->get();

        return response()->json($messages);
    }
    public function send(Request $request) {
        $user = Auth::user();

        $message = Messages::create([
            'from' => Auth::user()->id,
            'to' => $request->contact_id,
            'text' => $request->text
        ]);
        broadcast(new sendNewNessage($user, $message))->toOthers();
        return response()->json($message);
    }
    public function addContact(Request $request) {
        if(isset($request->id)) {
            $messages = Messages::whereNull('deleted_at')->where('from', Auth::user()->id)->where('text', null)->forceDelete();
            $message = Messages::create([
                'from' => Auth::user()->id,
                'to' => $request->id,
            ]);
            return response()->json(['success' => true], 200);   
        } else {
            return response()->json(['failed' => true], 404);
        }
    }
    public function searchMessages(Request $request) {
        $keyword = $request->keyword;
        if(isset($keyword) && strlen($keyword) > 0){
            $messages = Messages::whereNull('deleted_at')->where(function ($query) {
                $query->where('from', Auth::user()->id)
                      ->orWhere('to', Auth::user()->id);
            })->orderBy('id', 'desc')->select('from', 'to')->get();
            $arrID = [];
            if(count($messages) > 0) {
                foreach($messages as $message) {
                    if($message->from == Auth::user()->id) {
                        $arrID[] = $message->to;
                    } else {
                        $arrID[] = $message->from;
                    }
                }
                $users = User::whereNull('deleted_at')
                ->whereIn('id', array_unique($arrID))
                ->where(function($query) use ($keyword){
                    $query->where('username', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('phone_number', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('name', 'LIKE', '%' . $keyword . '%');
                })->select('id')->get();
                return response()->json($users, 200);
            }
        }
        return response()->json(['message' => 'keyword not defined.'], 404);
    }
}
