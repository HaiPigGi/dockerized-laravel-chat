<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\RedisHelper;
use App\Models\User;
use App\Helpers\CheckUserHelper;
use App\Http\Controllers\API\FonnteController;
class LoginController extends Controller
{
    /**
     * Handle an authentication attempt.
     * 
     * @param  \Illuminate\Http\Request  $request
     */

     public function getData(Request $request) {
        try {
            // validate user data
            $validateData = $request->validate([
                'phone_number' => 'required|string',
            ]);
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

                $userDataObject->verification_code = $verificationCode;
                RedisHelper::set(env('REDIS_KEYS_USER_DATA_SESSION'), json_encode($userDataObject), 120); // Store updated user data with 2 minutes expiration
                // Check result
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

}