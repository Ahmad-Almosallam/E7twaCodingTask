<?php

namespace App\Controllers;


use App\Models\User_model;
use App\Models\Client_model;
use CodeIgniter\RESTful\ResourceController;
use JWT\src\TokenDecoded;
use JWT\src\TokenEncoded;
use JWT\src\JWT;

class API extends ResourceController
{

    public function __construct()
    {
        $this->user = new User_model();
        $this->client = new Client_model();
    }


    public function register($api_key)
    {

        // Check if the api key exsist and valid

        $res = $this->client->where('api_key', $api_key)->where('is_valid', 1)->find();
        if (!$res) {
            $output = [
                'status' => 400,
                'message' => 'API key is wrong or invaild'
            ];
            return $this->respond($output, 400);
        }


        $name = $this->request->getPost('name');
        $age = $this->request->getPost('age');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        helper(['form']);
        $rules = [
            'name' => 'required|min_length[3]',
            'age' => 'required',
            'email' => 'required|max_length[50]|valid_email|is_unique[users.email,id,{id}]',
            'password' => 'required|min_length[3]',
        ];
        if (!$this->validate($rules)) {
            $output = [
                'status' => 400,
                'message' => $this->validator->getErrors(),
            ];
            return $this->respond($output, 400);
        } else {
            $res = $this->user->save([
                'name' => $name,
                'age' => $age,
                'email' => $email,
                'password' => $password,
                'is_admin' => 0, // means it is volunteer
            ]);

            if ($res) {
                $output = [
                    'status' => 200,
                    'message' => 'volunteer Information created'
                ];
                return $this->respond($output, 200);
            } else {
                $output = [
                    'status' => 400,
                    'message' => 'Something went wrong'
                ];
                return $this->respond($output, 400);
            }
        }
    }


    public function login($api_key)
    {
        // Check if the api key exsist and valid
        $res = $this->client->where('api_key', $api_key)->where('is_valid', 1)->find();
        if (!$res) {
            $output = [
                'status' => 400,
                'message' => 'API key is wrong or invaild'
            ];
            return $this->respond($output, 400);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $res = $this->user->where('email', $email)->find();
        if ($res) {
            // becaue the data are in the index 0 and the array size is 1
            $res = $res[0];
            // Next is to check if the logged in is an admin or volunteer because the password of the admin is not hashed
            if ($res['is_admin'] == 1) {  // means it is admin next is to create token
                $issuedat_claim = time(); // issued at
                $notbefore_claim = $issuedat_claim + 10; //not before in seconds
                $expire_claim = $issuedat_claim + 3600; // expire time in seconds
                $payload = array(
                    "iat" => $issuedat_claim,
                    "nbf" => $notbefore_claim,
                    "exp" => $expire_claim,
                    "data" => array(
                        "id" => $res['id'],
                        "name" => $res['name'],
                        "age" => $res['age'],
                        "email" => $res['email'],
                        "is_admin" => 1
                    )
                );
                $td = new TokenDecoded([], $payload);
                $token = $td->encode($this->privateKey(), 'HS256')->__toString();
                $output = [
                    'status' => 200,
                    'message' => 'Token created',
                    'Token' => $token
                ];
                return $this->respond($output, 200);
            } else {
                // If it is volunteer check the password
                if ($this->user->validateUser($res['password'], $password)) {
                    // View vlounteer`s data
                    $output = [
                        'status' => 200,
                        'message' => "Volunteer information retrieved",
                        'volunteers' => $this->user->select('id,name,email,age')->where('id', $res['id'])->find()
                    ];
                    return $this->respond($output, 200);
                } else {
                    $output = [
                        'status' => 400,
                        'message' => 'Password Wrong!'
                    ];
                    return $this->respond($output, 400);
                }
            }
        } else {
            $output = [
                'status' => 400,
                'message' => 'Email Wrong!'
            ];
            return $this->respond($output, 400);
        }
    }


    public function volunteers($api_key)
    {
        $res = $this->client->where('api_key', $api_key)->where('is_valid', 1)->find();
        if (!$res) {
            $output = [
                'status' => 400,
                'message' => 'API key is wrong or invaild'
            ];
            return $this->respond($output, 400);
        }
        // Get the token
        $authHeader = $this->request->getServer('HTTP_AUTHORIZATION');
        $token = substr($authHeader, 7);
        $te = new TokenEncoded($token);
        // Validate the token
        $te->validate($this->privateKey(), 'HS256');
        // Decode the token after confirming that the token is validated
        $tokendDecoded = $te->decode();
        // Get the data
        $data = $tokendDecoded->getPayload();

        // Check if the user is admin and view list of all volunteers

        if ($data['data']['is_admin'] == 1) {
            $output = [
                'status' => 200,
                'message' => "List of all the volunteers",
                'volunteers' => $this->user->select('id,name,email,age')->where('is_admin', 0)->findAll()
            ];
            return $this->respond($output, 200);
        } else {
            $output = [
                'status' => 400,
                'message' => "Only Admin allowed to view the list",
            ];
            return $this->respond($output, 400);
        }
    }


    public function privateKey()
    {
        return "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";
    }
}
