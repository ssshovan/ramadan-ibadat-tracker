<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Profile Controller
 * 
 * Handles user profile management.
 * Edit profile, change password, update preferences.
 */
class ProfileController extends Controller
{
    /**
     * Display user profile.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Auth::user();
        
        // Load user with relationships
        $user->load(['streaks', 'milestones']);

        // Get profile stats
        $stats = [
            'member_since' => $user->created_at->format('F Y'),
            'total_logs' => \App\Models\IbadatLog::forUser($user->id)->count(),
            'total_milestones' => $user->milestones->count(),
            'best_streak' => $user->streaks->max('longest_streak') ?? 0,
        ];

        return view('profile.show', compact('user', 'stats'));
    }

    /**
     * Show edit profile form.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update user profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'bio' => 'nullable|string|max:500',
            'language' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'email_notifications' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Update user
        $user->name = $request->input('name');
        $user->phone = $request->input('phone');
        $user->date_of_birth = $request->input('date_of_birth');
        $user->gender = $request->input('gender');
        $user->bio = $request->input('bio');
        $user->language = $request->input('language', 'en');
        $user->timezone = $request->input('timezone', 'UTC');
        $user->email_notifications = $request->boolean('email_notifications', true);
        $user->save();

        return redirect()->route('profile.show')
                         ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show change password form.
     *
     * @return \Illuminate\View\View
     */
    public function showChangePassword()
    {
        return view('profile.change-password');
    }

    /**
     * Update password.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Validate request
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Verify current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->back()
                           ->with('error', 'Current password is incorrect.');
        }

        // Update password
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('profile.show')
                         ->with('success', 'Password changed successfully!');
    }

    /**
     * Update avatar.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        // Validate request
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator);
        }

        // Store avatar
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
            $user->save();
        }

        return redirect()->route('profile.show')
                         ->with('success', 'Avatar updated successfully!');
    }

    /**
     * Delete account.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        // Validate request
        $request->validate([
            'password' => 'required|string',
        ]);

        // Verify password
        if (!Hash::check($request->input('password'), $user->password)) {
            return redirect()->back()
                           ->with('error', 'Password is incorrect.');
        }

        // Logout user
        Auth::logout();

        // Delete user (cascade will handle related records)
        $user->delete();

        return redirect('/')
                         ->with('success', 'Your account has been deleted.');
    }

    /**
     * Get user stats for AJAX.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $user = Auth::user();

        $stats = [
            'total_logs' => \App\Models\IbadatLog::forUser($user->id)->count(),
            'perfect_days' => \App\Models\IbadatLog::forUser($user->id)->completed()->count(),
            'total_milestones' => $user->milestones()->count(),
            'streaks' => $user->streaks()->pluck('current_streak', 'streak_type'),
        ];

        return response()->json($stats);
    }
}
