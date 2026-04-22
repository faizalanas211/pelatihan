<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        $title = 'login';
        return view('auth.login', compact('title'));
    }

    public function loginAction(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Bisa NIP atau name
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        
        // Cek apakah login berupa NIP atau name
        // Prioritaskan login sebagai NIP dulu
        $credentials = [];
        
        // Coba cari user berdasarkan NIP
        $userByNip = User::where('nip', $login)->first();
        
        if ($userByNip) {
            $credentials = [
                'nip' => $login,
                'password' => $request->input('password'),
            ];
        } else {
            // Jika tidak ditemukan, coba berdasarkan name
            $credentials = [
                'name' => $login,
                'password' => $request->input('password'),
            ];
        }

        try {
            if (!Auth::attempt($credentials)) {
                return back()->with('error', 'NIP atau Password salah');
            }

            // Regenerate session untuk keamanan
            $request->session()->regenerate();
            
            return redirect()->route('dashboard')->with('success', 'Selamat datang kembali!');
            
        } catch (\Throwable $th) {
            return back()->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
        }
    }

    public function register()
    {
        $title = 'register';
        return view('auth.register', compact('title'));
    }

    public function registerAction(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:pegawai,nip|unique:users,nip',
            'jabatan' => 'required|string|max:255',
            'password' => 'required|min:6',
        ]);

        try {
            // Buat data pegawai
            $pegawai = Pegawai::create([
                'nama' => $request->name,
                'nip' => $request->nip,
                'jabatan' => $request->jabatan,
                'foto' => null,
                'tanggal_lahir' => null,
                'is_pejabat' => 0,
                'jenis_pejabat' => null,
                'status' => 'aktif',
            ]);

            // Buat user account dengan role ADMIN
            $user = User::create([
                'name' => $request->name,
                'nip' => $request->nip,
                'pegawai_id' => $pegawai->id,
                'role' => 'admin', // ✅ DIUBAH JADI ADMIN
                'password' => Hash::make($request->password),
            ]);

            // Auto login setelah register
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Registrasi berhasil! Selamat datang ' . $user->name);

        } catch (\Exception $e) {
            return back()->with('error', 'Registrasi gagal: ' . $e->getMessage())->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Anda telah logout');
    }
}