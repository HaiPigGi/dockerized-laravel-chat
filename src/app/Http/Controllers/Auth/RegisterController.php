<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\RedisHelper;
use App\Helpers\CheckUserHelper;
use App\Http\Controllers\API\FonnteController;
use App\Models\profileModel;
use Illuminate\Support\Facades\DB;
use App\Helpers\JWTHelper;  
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /**
     * get User Request Data
     * 
     * @param request $request
     */
    public function getUserData (Request $request) {
        try {
            // validate user data
            $validateData = $request->validate([
                'name' => 'required|string',
                'phone_number' => 'required|string',
                'second_phone_number' => 'nullable|string',
                'address' => 'nullable|string'
            ]);

             // Check if user already exists
             $userExistsResponse = CheckUserHelper::checkUserExist($validateData);

                // Return error response if user already exists
                if ($userExistsResponse == true) {
                    return response()->json([
                        'message' => 'User with phone number already exists'
                    ], 400);
                }
            //store validate data to session with Redis
            RedisHelper::set(env('REDIS_KEYS_USER_DATA_SESSION'), json_encode($validateData), 600); // 10 minutes
            //redirect to send verification code
           $sendVerification =  $this->sendVerificationCode();
            
           if ($sendVerification == true ) {
                return response()->json([
                     'message' => 'Verification code sent successfully'
                ], 200);
              } else {
                return response()->json([
                     'message' => 'Failed to send verification code'
                ], 500);
           }
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

         /**
         * Create Send verification code
         * 
         * @return bool
         */
        private function sendVerificationCode () {
            try {
                // Get user data from session
                $userData = RedisHelper::get(env('REDIS_KEYS_USER_DATA_SESSION'));
                
                // Check if already expired or not found
                if (!$userData) {
                    return false;
                }

                // Decode the user data from JSON
                $userDataObject = json_decode($userData);

                // Create some verification code 
                $verificationCode = CheckUserHelper::generateVerificationCode();
                $phone_number = $userDataObject->phone_number;

                // Prepare data for FonnteController
                $fonnteRequestData = [
                    'phone_number' => $phone_number,
                    'verification_code' => $verificationCode
                ];
                
                // Send verification code to user phone number via Fonnte API
                $fonnteRequest = new Request($fonnteRequestData);
                $fonnte = new FonnteController();
                $fonnte->sendVerificationCode($fonnteRequest);
                Log::info('Verification code sent successfully: ' . $verificationCode);

                $userDataObject->verification_code = $verificationCode;
                RedisHelper::set(env('REDIS_KEYS_USER_DATA_SESSION'), json_encode($userDataObject), 120); // Store updated user data with 2 minutes expiration
                // Check result
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        /**
         * Check verification code from user input and if true create user
         * 
         * @param Request $request
         */
        public function checkVerifyCode(Request $request) {
            // Get user input verification code
            $inputCode = $request->input('code');

            // Get user data from session
            $userData = RedisHelper::get(env('REDIS_KEYS_USER_DATA_SESSION'));

            // Check if user data is found
            if (!$userData) {
                return response()->json([
                    'message' => 'Verification code not found'
                ], 400);
            }

            // Decode the user data from JSON
            $userDataObject = json_decode($userData);

            // Get the verification code from user data
            $verificationCode = $userDataObject->verification_code ?? null;
            
           $check =  CheckUserHelper::checkVerificationCode($verificationCode, $inputCode);

            // Check if the verification code is correct
            if ($check == true) {
                // Create user
                return $this->createUser();
            } else {
                return response()->json([
                    'message' => 'Verification code is incorrect'
                ], 400);
            }
        }


     /**
     * Create a new user and their profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function createUser()
    {
        try {
            // Get user data from session
            $userData = RedisHelper::get(env('REDIS_KEYS_USER_DATA_SESSION'));

            // Check if user data exists
            if (!$userData) {
                return response()->json([
                    'message' => 'User data not found'
                ], 400);
            }

            // Start a database transaction
            DB::beginTransaction();


            // Create user
            $user = new User();
            $user->name = json_decode($userData)->name;
            $user->phone_number = json_decode($userData)->phone_number;
            $user->save();

            // Create user profile
            $profile = new profileModel();
            $profile->user_id = $user->user_id; // Set the user_id
            $profile->second_phone_number = json_decode($userData)->second_phone_number;
            $profile->address = json_decode($userData)->address;
            $profile->save();

            // Commit the transaction if all operations succeed
            DB::commit();

            // Delete session data
            RedisHelper::delete(env('REDIS_KEYS_USER_DATA_SESSION'));
            RedisHelper::delete(env('REDIS_KEYS_USERS'));
            // Generate JWT token for Login user 
            //get id from user
            $jwt = new JWTHelper();
            $token = $jwt->generateLoginJWT($user->user_id);

            //configure session login with redis JWT
            RedisHelper::set(env('REDIS_KEYS_LOGIN'), $token, 3600); // 1 hour
            
            // Return success response
            return response()->json([
                'message' => 'User created successfully',
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }


}