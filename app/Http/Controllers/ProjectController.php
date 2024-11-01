<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Tech;
use App\Models\TechProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function login(Request $request)
    {
        $loginData = $request->all();
        $validate = Validator::make($loginData, [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => $validate->errors()->all()], 400);
        }

        if ($request->email == null) {
            if ($validate->fails()) {
                return response([
                    'status' => 401,
                    'message' => "Email/Username is Empty"
                ], 401);
            }
        } else if ($request->password == null) {
            if ($validate->fails()) {
                return response([
                    'status' => 401,
                    'message' => "Password is Empty"
                ], 401);
            }
        }

        $email = $request->email;
        $password = bcrypt($request->password);
        $user = User::where('email', '=', $email)->first();

        if (!Auth::attempt($loginData) && $request->fcm_token == null) {
            return response([
                'status' => 401,
                'message' => 'Wrong Email or Username',
            ], 401);
        } else {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response([
                'token' => $token,
                'message' => 'Login Successfully',
            ]);
        }
    }

    public function index()
    {
        $projectData = Project::with('techsProject.techs', 'projectImages')->latest()->get();
        if (is_null($projectData)) {
            return response([
                'message' => 'Data Empty',
                'data' => $projectData
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $projectData
        ], 200);
    }

    public function indexTech()
    {
        $techData = Tech::latest()->get();
        if (is_null($techData)) {
            return response([
                'message' => 'Data Empty',
                'data' => $techData
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $techData
        ], 200);
    }

    public function addProject(Request $request)
    {
        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'desc' => 'required',
            // 'status' => 'required',
        ], [
            'project_images.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }

        $newData = Project::create([
            'name' => $request->name,
            'desc' => $request->desc,
            // 'status' => $request->status,
            'status' => "Done",
        ]);

        // Simpan gambar dalam direktori 'storage/app/public/images'
        if ($request->project_images != null) {

            $original_name = $request->project_images->getClientOriginalName();
            $generated_name = 'images' . '-' . time() . '.' . $request->project_images->extension();

            // menyimpan gambar
            $request->project_images->storeAs('public/assets', $generated_name);

            $newImage = $newData->projectImages()->create([
                'name' => $generated_name,
                'status' => "Active",
            ]);
        } else {
            $generated_name = null;
        }

        return response([
            'message' => 'Data added successfully',
            'data' => $newData,
            'images' => $newImage,
        ], 201);
    }

    public function addTech(Request $request)
    {
        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }

        $newData = Tech::create([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return response([
            'message' => 'Data added successfully',
            'data' => $newData
        ], 201);
    }
}
