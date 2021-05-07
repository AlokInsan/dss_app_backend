<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AuthModel; 

class AuthController extends BaseController
{
	public function login()
    {
        $data = [];

        if ($this->request->getMethod() == 'post') {

            $rules = [
                'email' => 'required|min_length[6]|max_length[50]|valid_email',
                'password' => 'required|min_length[8]|max_length[255]|validateUser[email,password]',
            ];

            $errors = [
                'password' => [
                    'validateUser' => "Email or Password don't match",
                ],
            ];

            if (!$this->validate($rules, $errors)) {

                return view('backend/login', [
                    "validation" => $this->validator,
                ]);

            } else {
                $model = new AuthModel();

                $user = $model->where('email', $this->request->getVar('email'))
                    ->first();

                // Stroing session values
                $this->setUserSession($user);
                // Redirecting to dashboard after login
               // return redirect()->to(base_url('dashboard'));
                // Redirecting to dashboard after login
                if($user['role'] == "super_admin"){
                    return redirect()->to(base_url('admin'));
                }elseif($user['role'] == "lab_instructor"){
                    return redirect()->to(base_url('labinstructor'));
                }
            }
        }
        return view('login');
    }

    private function setUserSession($user)
    {
        $data = [
            'id' => $user['id'],
            'user_name' => $user['user_name'],
            'mobile_number' => $user['mobile_number'],
            'email' => $user['email'],
            'isLoggedIn' => true,
            "role" => $user['role'],
        ];

        session()->set($data);
        return true;
    }

   /*  public function register()
    {
        $data = [];
        
        if ($this->request->getMethod() == 'post') {
           //let's do the validation here
            $rules = [
                'user_name' => 'required|min_length[3]|max_length[20]',
                'mobile_number' => 'required|min_length[9]|max_length[20]',
                'email' => 'required|min_length[6]|max_length[50]|valid_email|is_unique[tbl_users.email]',
                'password' => 'required|min_length[8]|max_length[255]',
                'password_confirm' => 'matches[password]'
            ];
            $errors = [
                'email' => [
                    'is_unique' => "The Email-ID field must contain a unique value.",
                ]
            ];

            if (!$this->validate($rules)) {

                return view('register', [
                    "validation" => $this->validator,
                ]);
            } else {
                $model = new UserModel();

                $newData = [
                    'user_name' => $this->request->getVar('user_name'),
                    'mobile_number' => $this->request->getVar('mobile_number'),
                    'email' => $this->request->getVar('email'),
                    'password' => $this->request->getVar('password'),
                    'plain_password' => $this->request->getVar('password'),
                ];
             
                $model->save($newData); 
                $session = session();
                $session->setFlashdata('message', 'Successful Registration');
                return redirect()->to(base_url('login'));
            }
        }
        return view('register');
    }
 */
    public function profile()
    {

        $data = [];
        $model = new AuthModel();

        $data['user'] = $model->where('id', session()->get('id'))->first();
        return view('profile', $data);
    }



    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'));
    }
}
