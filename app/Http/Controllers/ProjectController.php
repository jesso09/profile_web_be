<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Tech;
use App\Models\TechProject;
use Carbon\Carbon;
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
            'techs_id' => 'nullable|array', // techs_id is now optional
            'techs_id.*' => 'exists:teches,id', // Validate each tech ID exists in techs table if provided
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
            $request->project_images->storeAs('public/', $generated_name);

            $newImage = $newData->projectImages()->create([
                'name' => $generated_name,
                'status' => "Active",
            ]);
        } else {
            $generated_name = null;
        }

        if ($newData) {
            if ($request->has('techs_id')) {
                foreach ($request->techs_id as $techId) {
                    $newData->techsProject()->create([
                        'techs_id' => $techId,
                    ]);
                }
            }
        }

        return response([
            'message' => 'Data added successfully',
            'data' => $newData,
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

    public function updateProject(Request $request, $id)
    {
        $data = Project::find($id);

        if (is_null($data)) {
            return response([
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $update = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'desc' => 'required',
            // 'status' => 'required',
            'techs_id' => 'nullable|array', // techs_id is now optional
            'techs_id.*' => 'exists:teches,id', // Validate each tech ID exists in techs table if provided
        ], [
            'project_images.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
        ]);

        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        $data->name = $update['name'];
        $data->desc = $update['desc'];

        if ($request->has('techs_id')) {
            foreach ($data->techsProject as $tech) {
                $tech->delete();
            }
        }

        if ($request->has('techs_id')) {
            foreach ($request->techs_id as $techId) {
                $data->techsProject()->create([
                    'techs_id' => $techId,
                ]);
            }
        }

        if ($request->project_images == null) {
            if ($data->save()) {
                return response([
                    'message' => 'Data Updated Success',
                    'data' => $data
                ], 200);
            }
        } else if ($request->project_images != null) {
            foreach ($data->projectImages as $image) {
                $imagePath = public_path('storage/' . $image->name);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $image->delete();
            }
            if ($data->project_images == null) {
                $original_name = $request->project_images->getClientOriginalName();
                $generated_name = 'images' . '-' . time() . '.' . $request->project_images->extension();

                // menyimpan gambar
                $request->project_images->storeAs('public/', $generated_name);
                $newImage = $data->projectImages()->create([
                    'name' => $generated_name,
                    'status' => "Active",
                ]);


            } 
            // else if ($data->project_images != null) {

            //     // unlink(public_path('storage/public/lab/' . $data->project_images));
            //     $original_name = $request->project_images->getClientOriginalName();
            //     $generated_name = 'images' . '-' . time() . '.' . $request->project_images->extension();
            //     // menyimpan gambar
            //     $request->project_images->storeAs('public/', $generated_name);
            //     $data->project_images = $generated_name;
            // }
        }

        $data->updated_at = Carbon::now()->toDateTimeString();
        $data->save();
        if ($data->save()) {
            return response([
                'message' => 'Data Updated Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Failed to update data',
            'data' => $data->techsProject
        ], 400);
    }

    public function deleteProject($id)
    {
        $targetData = Project::find($id);

        if (!$targetData) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        // Loop through each project image and delete the file if it exists
        foreach ($targetData->projectImages as $image) {
            $imagePath = public_path('storage/' . $image->name);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Delete the project data
        $targetData->delete();

        return response()->json([
            'message' => 'Data and associated images deleted successfully',
        ], 200);
    }
    
    public function updateTechs(Request $request, $id)
    {
        $data = Tech::find($id);

        if (is_null($data)) {
            return response([
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $update = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }

        $data->name = $update['name'];
        $data->status = $update['status'];

        $data->updated_at = Carbon::now()->toDateTimeString();
        $data->save();
        if ($data->save()) {
            return response([
                'message' => 'Data Updated Success',
                'data' => $data
            ], 200);
        }

        return response([
            'message' => 'Failed to update data',
            'data' => $data,
        ], 400);
    }

    public function deleteTechs($id)
    {
        $targetData = Tech::find($id);

        if (!$targetData) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $targetData->delete();

        return response()->json([
            'message' => 'Data and associated images deleted successfully',
        ], 200);
    }

}
