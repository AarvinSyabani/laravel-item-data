<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak sesuai dengan data kami.'],
            ]);
        }

        // Log login attempt
        activity()
            ->causedBy($user)
            ->log('User logged in via API');

        // Create token with abilities based on user permissions
        $deviceName = $request->device_name ?? $request->ip();
        $token = $user->createToken($deviceName, $this->getTokenAbilities($user));

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * Get the logged in user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json($request->user()->only(['id', 'name', 'email']));
    }

    /**
     * Logout user (revoke token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Log logout
        activity()
            ->causedBy($request->user())
            ->log('User logged out from API');
            
        // Delete current token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout']);
    }

    /**
     * Get token abilities based on user permissions
     *
     * @param User $user
     * @return array
     */
    private function getTokenAbilities(User $user): array
    {
        $abilities = ['*']; // Default for super-admin
        
        if (!$user->hasRole('super-admin')) {
            $abilities = [];
            
            // Add specific abilities based on permissions
            if ($user->can('view_category')) $abilities[] = 'categories:view';
            if ($user->can('create_category')) $abilities[] = 'categories:create';
            if ($user->can('update_category')) $abilities[] = 'categories:update';
            if ($user->can('delete_category')) $abilities[] = 'categories:delete';
            
            if ($user->can('view_supplier')) $abilities[] = 'suppliers:view';
            if ($user->can('create_supplier')) $abilities[] = 'suppliers:create';
            if ($user->can('update_supplier')) $abilities[] = 'suppliers:update';
            if ($user->can('delete_supplier')) $abilities[] = 'suppliers:delete';
            
            if ($user->can('view_item')) $abilities[] = 'items:view';
            if ($user->can('create_item')) $abilities[] = 'items:create';
            if ($user->can('update_item')) $abilities[] = 'items:update';
            if ($user->can('delete_item')) $abilities[] = 'items:delete';
            if ($user->can('update_item_stock')) $abilities[] = 'items:stock';
            
            if ($user->can('view_transaction')) $abilities[] = 'transactions:view';
            if ($user->can('create_transaction')) $abilities[] = 'transactions:create';
            if ($user->can('update_transaction')) $abilities[] = 'transactions:update';
            if ($user->can('delete_transaction')) $abilities[] = 'transactions:delete';
            if ($user->can('process_incoming_transaction')) $abilities[] = 'transactions:incoming';
            if ($user->can('process_outgoing_transaction')) $abilities[] = 'transactions:outgoing';
        }
        
        return $abilities;
    }
}
