<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function create($roomId)
    {
        $room = Room::findOrFail($roomId);
        return view('frontend.bookings.create', compact('room'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'payment_method' => 'required|in:stripe,paypal,cash',
        ]);

        $user = Auth::user();

        if (!$user) {
            // Create a new user
            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(Str::random(8)), // Generate a random password
            ]);

            Auth::login($user);
        }

        Booking::create([
            'room_id' => $request->room_id,
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'payment_method' => $request->payment_method,
        ]);

        return redirect()->route('frontend.bookings.index')->with('success', 'Booking confirmed!');
    }

    public function index()
    {
        $bookings = Booking::where('user_id', Auth::id())->get();
        return view('frontend.bookings.index', compact('bookings'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|confirmed|min:8',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('frontend.bookings.index')->with('success', 'Password updated successfully!');
    }
}
