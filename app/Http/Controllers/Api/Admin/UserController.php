<?php
// File: app/Http/Controllers/Api/Admin/UserController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a paginated list of all users.
     * Admins can see all users.
     */
    public function index()
    {
        // We use pagination to handle large numbers of users gracefully.
        $users = User::latest()->paginate(15);

        return response()->json($users);
    }

    /**
     * Reset a user's password to a new random password.
     * This is a secure way to handle password resets from an admin panel.
     */
    public function resetPassword(User $user)
    {
        // Generate a new, secure random password.
        $newPassword = Str::random(12);

        // Update the user's password in the database.
        $user->password = Hash::make($newPassword);
        $user->save();

        // Return the new password to the admin so they can communicate it to the user.
        // In a real-world scenario, you might send an email to the user instead.
        return response()->json([
            'message' => "Password for {$user->email} has been reset.",
            'new_password' => $newPassword,
        ]);
    }

    public function toggleStatus(User $user)
    {
        // Prevent an admin from deactivating their own account.
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'You cannot deactivate your own account.'], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "User {$user->email} has been {$status}.",
            'user' => $user,
        ]);
    }
}
