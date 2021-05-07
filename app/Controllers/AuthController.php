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
            $IPA = $this->request->getIPAddress();
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
                $this->setUserSession($user,$IPA);
                return redirect()->to(base_url($user['slug']));
                // Redirecting to dashboard after login
               // return redirect()->to(base_url('dashboard'));
                // Redirecting to dashboard after login
                //if($user['role'] == "adm"){
                //    return redirect()->to(base_url('adm'));
                //}elseif($user['role'] == "lab_instructor"){
                //    return redirect()->to(base_url('usr'));
                //}
            }
        }
        return view('backend/login');
    }

    private function setUserSession($user,$IPA)
    {
        $model = new AuthModel();
        $data = [
            'id' => $user['id'],
            'user_name' => $user['username'],            
            'rtslug' => $user['slug'],
            'mobile_number' => $user['mobile_number'],
            'email' => $user['email'],
            'isLoggedIn' => true,
            "role" => $user['role'],            
            "ip" => $IPA,
        ];

        $newData = [
            'Is_online' => 1,
            'login_ip'  => $IPA,
        ];      

        $model->update( $user['id'],$newData);

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
        $model = new AuthModel();
        $upData = [
            'Is_online' => 0,
            'last_login_ip'  => session()->get('ip'),
        ];      
        $model->update( session()->get('id'),$upData);
        session()->destroy();
        return redirect()->to(base_url('/login'));
    }
}
