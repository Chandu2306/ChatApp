<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    private $API_URL = "http://localhost:4000";

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url','cookie']);
        $this->load->library('session');
    }

    public function register()
    {
        $this->load->view('register_view');
    }

    public function register_submit()
    {
        $postData = [
            "username" => $this->input->post("username"),
            "password" => $this->input->post("password")
        ];

        $ch = curl_init($this->API_URL . "/register");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode($postData)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($result && $result['success']) {
            redirect('auth/login');
        } else {
            $data["error"] = $result ? $result["message"] : "Registration failed";
            $this->load->view("register_view", $data);
        }

    }

    public function login()
    {
        $this->load->view('login_view');
    }

    public function login_submit()
    {
        $username = $this->input->post("username");
        $password = $this->input->post("password");

        $postData = [
            "username" => $username,
            "password" => $password
        ];

        $apiUrl = $this->API_URL . "/login";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        
        if ($result && isset($result['success']) && $result['success']) {
            $token = $result['token'];
            
            $this->session->set_userdata([
                "jwt_token" => $token,
                "user_data" => [
                    "username" => $result["username"],
                    "userId" => $result['userId'] ?? null
                ]
            ]);
            
            set_cookie('jwt_token', $token, 86400 * 7);

            redirect("auth/dashboard");
        } else {
            $data['error'] = $result['message'] ?? "Login failed";
            $this->load->view('login_view', $data);
        }
    }

    public function dashboard()
{
    if (!$this->session->userdata("jwt_token")) {
        redirect("auth/login");
        return;
    }

    $token = $this->session->userdata("jwt_token");
    $apiUrl = $this->API_URL . "/dashboard";
    
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);

    if ($httpCode !== 200 || !$result || !$result['success']) {
        if ($httpCode === 401) {
            $this->logout();
            return;
        }
        
        $data["error"] = "Failed to load dashboard";
        $this->load->view("login_view", $data);
        return;
    }

    $userData = $this->session->userdata("user_data");
    
    $data = [
        "loggedIn" => true,
        "user" => $userData["username"] ?? "User",
        "activeUsers" => isset($result["activeUsers"]) ? $result["activeUsers"] : [],
        "totalActiveUsers" => isset($result["totalUsers"]) ? $result["totalUsers"] : 0
    ];

    $this->load->view("dashboard_view", $data);
}

    

    public function logout()
    {
        $this->session->unset_userdata(["jwt_token", "user_data"]);
        $this->session->sess_destroy();
        delete_cookie('jwt_token');
        redirect("auth/login");
    }
}