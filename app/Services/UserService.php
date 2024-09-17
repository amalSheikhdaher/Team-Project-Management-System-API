<?php
namespace App\Services;

use App\Models\User;

use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Create a new user with the provided data.
     *
     * @param array $data An array containing user data.
     * @return User The created user model.
     * @throws Exception If an error occurs while creating the user.
     */
    public function createUser($data)
    {
        try {
			$user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'])  // Hashing password
            ]);
            return $user;
		}catch(Exception $e ){
            Log::error($e);
			throw new Exception('Failed to created user: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing user with the provided data.
     *
     * @param User $user The user model to update.
     * @param array $data An array containing updated user data.
     * @return User The updated user model.
     * @throws Exception If an error occurs while updating the user.
     */
    public function updateUser(User $user, array $data)
    {
		try{
			$user->update([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'password' => Hash::make($data['password'])?? $user->password,
			]);
        return $user;
		}catch(Exception $e ){
            Log::error($e);
			throw new Exception('Failed to updated user: ' . $e->getMessage());
        }
    }

    /**
     * Delete a specific user from the database.
     *
     * @param User $user The user model to delete.
     * @return void
     * @throws Exception If an error occurs while deleting the user.
     */
    public function deleteUser(User $user)
    {
		try{
			$user->delete();
		}catch(Exception $e ){
            Log::error($e);
			throw new Exception('Failed to deleted user: ' . $e->getMessage());
        }
    }
}