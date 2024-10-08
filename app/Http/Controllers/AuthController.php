<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Enseignant;
use App\Models\Admin; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // registerStudent
    public function registerStudent(Request $request)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'niveau_classe' => 'required|string|max:255',
                'email' => 'required|email|unique:students,email',
                'telephone' => 'nullable|string|max:15',
                'mot_de_passe' => 'required|string|min:6',
                'date_naissance' => 'required|date',
                'genre' => 'nullable|string|max:10',
            ]);

            // Hash the password
            $validated['mot_de_passe'] = Hash::make($validated['mot_de_passe']);

            // Create and return the student record
            $student = Student::create($validated);

            // Generate token
            $token = $student->createToken('StudentToken')->plainTextToken;

            // Return student and token
            return response()->json([
                'student' => $student,
                'token' => $token
            ], 200);

        } catch (ValidationException $e) {
            // Handle validation exceptions
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()
            ], 422);
        }
    }
    // registerEnseignant
    public function registerEnseignant(Request $request)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:enseignants,email',
                'telephone' => 'nullable|string|max:15',
                'mot_de_passe' => 'required|string|min:6',
                'date_naissance' => 'required|date',
                'genre' => 'nullable|string|max:10',
                'niveau_etude' => 'nullable|string|max:255',
                'photo_diplome' => 'nullable|string|max:255',
                'matiere_a_enseigner' => 'nullable|string|max:255',
                'photo_profile' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            // Set 'is_active' to false by default
            $validated['is_active'] = false;

            // Hash the password
            $validated['mot_de_passe'] = Hash::make($validated['mot_de_passe']);

            // Create and return the enseignant record
            $enseignant = Enseignant::create($validated);

            // Generate token
            $token = $enseignant->createToken('EnseignantToken')->plainTextToken;

            // Return enseignant and token
            return response()->json([
                'enseignant' => $enseignant,
                'token' => $token
            ], 200);

        } catch (ValidationException $e) {
            // Handle validation exceptions
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->validator->errors()
            ], 422);
        }
    }
    // login student 
    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'mot_de_passe' => 'required|string'
        ]);

        // Check email
        $student = Student::where('email', $fields['email'])->first();

        // Check password
        if(!$student || !Hash::check($fields['mot_de_passe'], $student->mot_de_passe)) {
            return response([
                'message' => 'Bad creds'
            ], 401);
        }

        $token = $student->createToken('StudentToken')->plainTextToken;

        $response = [
            'student' => $student,
            'token' => $token
        ];

        return response($response, 201);
    }
    // Login Enseignant
    public function loginEnseignant(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'mot_de_passe' => 'required|string'
        ]);

        // Check email
        $enseignant = Enseignant::where('email', $fields['email'])->first();

        // Check password
        if(!$enseignant || !Hash::check($fields['mot_de_passe'], $enseignant->mot_de_passe)) {
            return response([
                'message' => 'Bad creds'
            ], 401);
        }

        $token = $enseignant->createToken('EnseignantToken')->plainTextToken;

        $response = [
            'enseignant' => $enseignant,
            'token' => $token
        ];

        return response($response, 201);
    }
    // admin login 
    public function loginAdmin(Request $request)
    {
        // Validate the request data
        $fields = $request->validate([
            'email' => 'required|string|email',
            'mot_de_passe' => 'required|string'
        ]);

        // Attempt to find the admin by email
        $admin = Admin::where('email', $fields['email'])->first();

        // Check if admin exists and the password is correct
        if(!$admin || !Hash::check($fields['mot_de_passe'], $admin->mot_de_passe)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Create a token for the admin
        $token = $admin->createToken('AdminToken')->plainTextToken;

        // Return a successful response with the admin details and token
        return response()->json([
            'admin' => $admin,
            'token' => $token
        ], 200);
    }

    public function logout (Request $req){
        auth()->user()->tokens()->delete();

        return[
            'message'=>'logged out' 
        ];
    }
}
