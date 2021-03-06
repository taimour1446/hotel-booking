<?php

namespace HotelBooking\Http\Controllers\Hotel;

use DB;
use Auth;
use Session;
use Illuminate\Http\Request;
use HotelBooking\Room;
use HotelBooking\HotelRoomType;
use HotelBooking\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use HotelBooking\Http\Requests\Hotel\RoomFormRequest;

/**
 * RoomController.
 */
class RoomController extends HotelBaseController
{
    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::hotel();
        $this->middleware('auth.hotel');
    }

    /**
     * Load view with table of Room list
     *
     * @return view
     */
    public function index()
    {
        $hotelRoomTypes = HotelRoomType::select('id')
            ->where('hotel_id', $this->auth->get()->hotel_id)
            ->get();
        $columns = [
            'id',
            'hotel_room_type_id',
            'name',
            'status'
        ];
        $with['hotelRoomType'] = function ($query) {
            $query->select('id', 'name');
        };
        $rooms = Room::with($with)
            ->select($columns)
            ->whereIn('hotel_room_type_id', $hotelRoomTypes)
            ->paginate(20);
        return view('hotel.room.index', compact('rooms'));
    }

    /**
     * Load view with Room creating form
     *
     * @return view
     */
    public function create()
    {
        $hotelRoomTypes = DB::table('hotel_room_types')
            ->where('hotel_id', $this->auth->get()->hotel_id)
            ->lists('name', 'id');
        return view('hotel.room.create', compact('hotelRoomTypes'));
    }

    /**
     * Create new Room from request information and sotre into database
     *
     * @param $request
     *
     * @return redirect
     */
    public function store(RoomFormRequest $request)
    {
        if (Room::create($request->all())) {
            Session::flash('flash_success', trans('messages.create_success_room'));
        } else {
            Session::flash('flash_error', trans('messages.create_fail_room'));
        }
        return redirect(route('hotel.room.create'));
    }

    public function show($id)
    {
        return $id;
    }

    /**
     * Load view with Room editting form
     *
     * @param int $id
     *
     * @return view
     */
    public function edit($id)
    {
        $columns = [
            'id',
            'hotel_room_type_id',
            'name',
            'status'
        ];
        $room = Room::find($id, $columns);
        if ($room && $room->hotelRoomType->hotel_id == $this->auth->get()->hotel_id) {
            $hotelRoomTypes = DB::table('hotel_room_types')
                ->where('hotel_id', $this->auth->get()->hotel_id)
                ->lists('name', 'id');
            return view('hotel.room.edit', compact('room', 'hotelRoomTypes'));
        } else {
            return view('hotel.errors.404');
        }
    }

    /**
     * Update Room from request information into database
     *
     * @param $request
     * @param int $id
     *
     * @return redirect
     */
    public function update(RoomFormRequest $request, $id)
    {
        $room = Room::find($id, ['id','hotel_room_type_id']);
        if ($room && $room->hotelRoomType->hotel_id == $this->auth->get()->hotel_id) {
            if ($room->update($request->all())) {
                Session::flash('flash_success', trans('messages.edit_success_room'));
            } else {
                Session::flash('flash_error', trans('messages.edit_fail_room'));
            };
        }
        return redirect(route('hotel.room.edit', $id));
    }

    /**
     * Remove the specified room from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $room = Room::find($id, ['id','hotel_room_type_id']);
        if ($room && $room->hotelRoomType->hotel_id == $this->auth->get()->hotel_id) {
            $room->delete();
            Session::flash('flash_success', trans('messages.delete_success_room'));
        } else {
            Session::flash('flash_error', trans('messages.delete_fail_room'));
        }
        return redirect()->route('hotel.room.index');
    }
}
