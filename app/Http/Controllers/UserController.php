<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserProfile;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::with('profile')
            ->where('id', '!=', auth()->user()->id);

        if ($request->filled('search')) {
            $users->where('email', 'like', '%' . $request->input('search') . '%');

            $users->orWhereHas('profile', function ($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('last_name', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('primary_phone', 'like', '%' . $request->input('search') . '%');

            });
        }

        if ($request->filled('active_status')) {
            if ($request->input('active_status') === 'active') {
                $users->OnlyActive();
            } elseif ($request->input('active_status') === 'blocked') {
                $users->OnlyBlocked();
            }
        } else {
            // Load only active users by default
            $users->OnlyActive();
        }

        $users = $users->latest()
            ->paginate($request->filled('per_page') ? (int)$request->input('per_page') : 10);


        return view('users.index', compact('users'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $user->load(['profile', 'roles']);
        $availableRoles = Role::all();
        return view('users.edit', compact('user', 'availableRoles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ProfileUpdateRequest $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(ProfileUpdateRequest $request, User $user)
    {
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only(
                ['first_name', 'last_name', 'primary_phone']
            )
        );

        alert()->success('User profile was updated successfully.');
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function updateRoles(Request $request, User $user)
    {
        $this->validate($request, [
            'roles' => 'required|array|exists:roles,id',
        ]);

        $user->syncRoles($request->input('roles'));

        alert()->success('User roles were updated successfully.');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //todo check for purchase history before deleting
        $user->delete();
        alert()->success('User was deleted successfully.');
        return redirect()->route('admin.users.index');
    }

    /**
     * Toggle user's account locked status.
     *
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleBlockedStatus(User $user)
    {
        $user->blocked_at = $user->blocked_at ? null : now();
        $user->save();

        alert()->success('User status was changed successfully.');
        return back();
    }

}
