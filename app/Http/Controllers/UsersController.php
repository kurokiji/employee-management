<?php

namespace App\Http\Controllers;

use App\Mail\RecoverPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    //$user->password = Hash::make($request->password);
    public $error = "There's a server error. Try again.";
    public $permissionError = "You do not have the necessary permissions to perform this operation";
    public $idNotFound = "There is no employee with the entered id";
    public $success = "Data collected correctly";
    public $employee = 'employee';
    public $humanresources = 'humanresources';
    public $executive = 'executive';


    public function add(Request $request)
    {
        $answer = ['status' => 1, 'msg' => ''];
        $data = $request->getContent();
        try {
            $data = json_decode($data, true);
            if ($request->loggedUser->job == $this->employee) {
                $answer['status'] = 0;
                $answer['msg'] = $this->permissionError;
            } else {
                $validator = Validator::make(
                    $data,
                    [
                        'email' => 'required|email:rfc|unique:App\Models\User,email|max:30',
                        'name' => 'required|max:30',
                        'password' => 'regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
                        'job' => ['required', 'regex:/\b(?:employee|executive|humanresources)\b/'],
                        'salary' => 'required|numeric',
                        'biography' => 'required|max:500',
                        'profileImgUrl' => 'required'
                    ],
                    [
                        'name.required' => 'The name field is required',
                        'name.max' => 'The name cannot be longer than 30 characters',
                        'email.required' => 'The email field is required',
                        'email.email' => 'The email field is not in email format',
                        'email.unique' => 'The email entered already exists',
                        'password.regex' => 'The password must have an uppercase,a lowercase,a number,a special character and have at least 6 characters.',
                        'job.regex' => 'The job title must be employee,executive or humanresources',
                        'salary.required' => 'Salary is mandatory',
                        'salary.numeric' => 'Salary must be a number',
                        'biography.required' => 'Biography is mandatory',
                        'biography.max' => 'Biography cannot exceed 500 characters',
                        'profileImgUrl.required' => 'Profiles photo es mandatory'
                    ]
                );

                if ($validator->fails()) {
                    $answer['status'] = 0;
                    $answer['msg'] = implode(", ",$validator->errors()->all());
                } else {
                    $user = new User();
                    $user->name = $data['name'];
                    $user->email = $data['email'];
                    if (isset($data['password'])){
                        $user->password = Hash::make($data['password']);
                    } else {
                        $faker = Faker::create('es_ES');
                        $password = $faker->password;
                        $user->password = Hash::make($password);
                        Mail::to($user->email)->send(new RecoverPassword(
                            "Contraseña para tu cuenta en etico",
                            "Solicitud de nueva contraseña",
                            [
                                "Ya formas parte del equipo etico.",
                                "Para poder acceder a todas nuestras plataformas necesitarás tu correo electrónico y la siguiente contraseña.",
                                "Nueva contraseña: $password",
                                "Te recomendamos que la cambies en cuanto puedas."
                            ]
                        ));
                    }
                    $user->job = $data['job'];
                    $user->salary = $data['salary'];
                    $user->biography = $data['biography'];
                    $user->profileImgUrl = $data['profileImgUrl'];
                    $user->save();
                    $answer['msg'] = "User successfully saved with id $user->id";
                }
            }
        } catch (\Exception $e) {
            $answer['status'] = 0;
            $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
        }
        return response()->json($answer);
    }

    public function delete(Request $request, $id)
    {
        $answer = ['status' => 1, 'msg' => ''];
        $employee = User::where('id', '=', $id)->first();
        try {
            if ($employee) {
                $employeeJob = $employee->job;
                $loggedJob = $request->loggedUser->job;
                if (($loggedJob == $this->humanresources && $employeeJob == $this->employee)
                    || ($loggedJob == $this->executive && $employeeJob != $this->executive)
                    || ($request->loggedUser->id == $employee->id && $loggedJob == $this->executive)
                ) {
                    DB::table('users')->where('id', '=', $id)->delete();
                    $answer['msg'] = "User $employee->id has been successfully removed";
                }
            } else {
                $answer['status'] = 0;
                $answer['msg'] = $this->idNotFound;
            }
        } catch (\Exception $e) {
            $answer['status'] = 0;
            $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
        }
        return response()->json($answer);
    }

    public function modify(Request $request, $id)
    {
        $answer = ['status' => 1, 'msg' => ''];
        $data = $request->getContent();
        try {
            $data = json_decode($data, true);
            $employee = User::where('id', '=', $id)->first();
            if ($employee) {
                $loggedJob = $request->loggedUser->job;
                $employeeJob = $employee->job;
                if (($loggedJob == $this->humanresources && $employeeJob == $this->employee)
                    || ($loggedJob == $this->executive && $employeeJob != $this->executive)
                    || ($request->loggedUser->id == $employee->id && $loggedJob == $this->executive)
                ) {
                    $validator = Validator::make(
                        $data,
                        [
                            'email' => 'email:rfc|max:30',
                            'name' => 'max:30',
                            'password' => 'regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
                            'job' => ['regex:/\b(?:employee|executive|humanresources)\b/'],
                            'salary' => 'numeric',
                            'biography' => 'max:500'
                        ],
                        [
                            'name.max' => 'The name cannot be longer than 30 characters',
                            'email.email' => 'The email field is not in email format',
                            'password.regex' => 'The password must have an uppercase,a lowercase,a number,a special character and have at least 6 characters.',
                            'job.regex' => 'The job title must be employee,executive or humanresources',
                            'salary.numeric' => 'Salary must be a number',
                            'biography.max' => 'Biography cannot exceed 500 characters'
                        ]
                    );

                    if ($validator->fails()) {
                        $answer['status'] = 0;
                        $answer['msg'] = implode(", ",$validator->errors()->all());
                    } else if (isset($data['email']) && DB::table('users')->where('email', $data['email'])->exists() && $employee->email != $data['email']) {
                        $answer['status'] = 0;
                        $answer['msg'] = "There is already another employee with this email";
                    } else {
                        if (isset($data['name'])) {
                            $employee->name = $data['name'];
                        }
                        if (isset($data['email'])) {
                            $employee->email = $data['email'];
                        }
                        if (isset($data['password'])) {
                            $employee->password = Hash::make($data['password']);
                        }
                        if (isset($data['job'])) {
                            $employee->job = $data['job'];
                        }
                        if (isset($data['salary'])) {
                            $employee->salary = $data['salary'];
                        }
                        if (isset($data['biography'])) {
                            $employee->biography = $data['biography'];
                        }
                        $employee->save();
                        $answer['msg'] = "User $employee->id has been modified successfully";
                    }
                } else {
                    $answer['status'] = 0;
                    $answer['msg'] = $this->permissionError;
                }
            } else {
                $answer['status'] = 0;
                $answer['msg'] = $this->idNotFound;
            }
        } catch (\Exception $e) {
            $answer['status'] = 0;
            $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
        }
        return response()->json($answer);
    }

    public function getAll(Request $request)
    {
        $answer = ['status' => 1, 'msg' => ''];
        $data = $request->getContent();
        try {
            $data = json_decode($data);
            if ($request->loggedUser->job == $this->humanresources) {
                $employees = User::where('job', '=', $this->employee)->get();
                $answer['data'] = [];
                foreach ($employees as $employee) {
                    $employee->makeHidden('email_verified_at')->makeHidden('password')->makeHidden('api_token')->makeHidden('remember_token')->makeHidden('created_at')->makeHidden('updated_at');
                    array_push($answer['data'], $employee);
                }
                $answer['msg'] = $this->success;
            } else if ($request->loggedUser->job == $this->executive) {
                $employees = User::where('job', '=', $this->employee)->orWhere('job', '=', $this->humanresources)->get();
                $answer['data'] = [];
                foreach ($employees as $employee) {
                    $employee->makeHidden('email_verified_at')->makeHidden('password')->makeHidden('api_token')->makeHidden('remember_token')->makeHidden('created_at')->makeHidden('updated_at');
                    array_push($answer['data'], $employee);
                }
                $answer['msg'] = $this->success;
            } else {
                $answer['status'] = 0;
                $answer['msg'] = $this->permissionError;
            }
        } catch (\Exception $e) {
            $answer['status'] = 0;
            $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
        }
        return response()->json($answer);
    }

    public function get(Request $request, $id)
    {
        $answer = ['status' => 1, 'msg' => ''];
        try {
            $employee = User::where('id', '=', $id)->first();
            $employee->makeHidden('id')->makeHidden('email_verified_at')->makeHidden('password')->makeHidden('api_token')->makeHidden('remember_token')->makeHidden('created_at')->makeHidden('updated_at');
            if ($employee) {
                if ($request->loggedUser->job == $this->humanresources) {
                    if ($employee->job == $this->employee) {
                        $answer['data'] = $employee;
                        $answer['msg'] = $this->success;
                    } else {
                        $answer['status'] = 0;
                        $answer['msg'] = $this->permissionError;
                    }
                } else if ($request->loggedUser->job == $this->executive) {
                    if ($employee->job == $this->executive) {
                        $answer['status'] = 0;
                        $answer['msg'] = $this->permissionError;
                    } else {
                        $answer['data'] = $employee;
                        $answer['msg'] = $this->success;
                    }
                } else {
                    $answer['status'] = 0;
                    $answer['msg'] = $this->permissionError;
                }
            } else {
                $answer['status'] = 0;
                $answer['msg'] = $this->idNotFound;
            }
        } catch (\Exception $e) {
            $answer['status'] = 0;
            $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
        }
        return response()->json($answer);
    }

    public function profile(Request $request)
    {
        $answer = ['status' => 1, 'msg' => ''];
        try {
            $loggedUser = User::where('id', '=', $request->loggedUser->id)->first();
            $loggedUser->makeHidden('id')->makeHidden('email_verified_at')->makeHidden('password')->makeHidden('api_token')->makeHidden('remember_token')->makeHidden('created_at')->makeHidden('updated_at');
            $answer['data'] = $loggedUser;
            $answer['msg'] = $this->success;
        } catch (\Exception $e) {
            $answer['status'] = 0;
            $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
        }
        return response()->json($answer);
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email:rfc',
                'password' => 'required'
            ],
            [
                'email.required' => 'The email field is required',
                'email.email' => 'The email field is not in email format',
                'password.required' => 'The password is mandatory',
            ]
        );
        if ($validator->fails()) {
            $answer['status'] = 0;
            //$answer['msg'] = $validator->errors();
            $answer['msg'] = implode(", ",$validator->errors()->all());
        } else {
            $answer = ['status' => 1, 'msg' => ''];
            try {
                $user = User::where('email', $request->email)->first();
                if ($user) {
                    if (Hash::check($request->password, $user->password)) {
                        $token = Hash::make(now() . $user->id);
                        $user->api_token = $token;
                        $user->save();
                        $user->makeHidden('email_verified_at')->makeHidden('remember_token')->makeHidden('created_at')->makeHidden('updated_at');
                        $answer['user'] = $user;
                        $answer['msg'] = "Login succesfull";
                    } else {
                        $answer['status'] = 0;
                        $answer['msg'] = "Incorrect password";
                    }
                } else {
                    $answer['status'] = 0;
                    $answer['msg'] = "There is no user with that email";
                }
            } catch (\Exception $e) {
                $answer['status'] = 0;
                $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
            }
        }
        return response()->json($answer);
    }

    public function passwordRecover(Request $request)
    {
        $answer = ['status' => 1, 'msg' => ''];
        $data = $request->getContent();
        try {
            $data = json_decode($data);
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $faker = Faker::create('es_ES');
                $password = $faker->password;
                $user->password = Hash::make($password);
                Mail::to($user->email)->send(new RecoverPassword(
                    "Recuperación de contraseña",
                    "Solicitud de nueva contraseña",
                    [
                        "Se ha solicitado la recuperación de tu contraseña.",
                        "Si no has sido tu, escribe al administrador de sistemas.",
                        "En cualquier caso a continuación, te presentamos tu nueva contraseña.",
                        "Nueva contraseña: $password",
                        "Te recomendamos que la cambies en cuanto puedas."
                    ]
                ));
                $answer['msg'] = 'El cambio de contraseña se ha procesado correctamente, comprueba tu email';
                $user->save();
            } else {
                $answer['status'] = 0;
                $answer['msg'] = $this->idNotFound;
            }
        } catch (\Exception $e) {
            $answer['status'] = 0;
            $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
        }
        return response()->json($answer);
    }

    public function checkToken(Request $request) {
        $answer = ['status' => 1, 'msg' => ''];
        $data = $request->getContent();
        try {
            $data = json_decode($data);
            $user = User::where('api_token', $request->token)->first();
            if ($user) {
                $answer['msg'] = 'User correctly logged';
                $user->save();
            } else {
                $answer['status'] = 0;
                $answer['msg'] = "There's no user with that token";
            }
        } catch (\Exception $e) {
            $answer['status'] = 0;
            $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
        }
        return response()->json($answer);
    }
}
