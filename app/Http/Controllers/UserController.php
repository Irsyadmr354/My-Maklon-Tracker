<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Progress;
use App\Models\Bukti;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only([
            'index', 'admin', 'updateProgress'
        ]);
    }

    public function masuk()
    {
        return view('login');
    }

    public function logika_masuk(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'no_hp' => 'required|string|regex:/^[0-9]{10,15}$/',
        ]);
        
        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'no_hp' => $request->no_hp,
                'role'  => 'user',
            ]
        );

        $user->no_hp = $request->no_hp;

        $adminPhone = config('maklon.admin_phone');
        if ($adminPhone !== null && hash_equals($adminPhone, $request->no_hp)) {
            $user->role = 'admin';
        }

        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'admin') {
            return redirect()->route('admin.index');
        }

        return redirect()->route('tracker.index');
    }

    public function admin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $data = $this->loadTrackerData((int) Auth::id());

        return view('admin', $data);
    }

    public function index()
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'Admin tidak boleh akses order-tracker.');
        }

        $data = $this->loadTrackerData((int) Auth::id());

        $stages = [
            'konsultasi'     => 'gambar1.png',
            'pembayaran'     => 'gambar4.png',
            'desain label'   => 'gambar5.png',
            'produksi'       => 'gambar7.png',
            'pengemasan'     => 'gambar9.png',
            'pengiriman'     => 'gambar10.png',
            'foto dan video' => 'gambar8.png',
            'kesimpulan'     => 'gambar2.png',
        ];

        return view('utama', array_merge($data, ['stages' => $stages]));
    }

    private function loadTrackerData(int $userId): array
    {
        return [
            'user'      => User::findOrFail($userId),
            'progress'  => Progress::firstOrCreate(['user_id' => $userId]),
            'buktiList' => Bukti::where('user_id', $userId)->get()->keyBy('step'),
        ];
    }

    public function updateProgress(Request $request)
    {
        $user     = Auth::user();
        $progress = Progress::firstOrCreate(['user_id' => $user->id]);

        $tahapan = [
            1 => 'konsultasi',    2 => 'pembayaran',
            3 => 'desain label',  4 => 'produksi',
            5 => 'pengemasan',    6 => 'pengiriman',
            7 => 'foto dan video',8 => 'kesimpulan',
        ];

        $rules = [];
        foreach (array_keys($tahapan) as $i) {
            $rules["status{$i}"]     = 'nullable|in:done,on_progress,hold';
            $rules["tanggal{$i}"]    = 'nullable|date';
            $rules["keterangan{$i}"] = 'nullable|string|max:255';
            $rules["bukti{$i}"]      = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048';
        }
        $request->validate($rules);

        $buktiPerStep = Bukti::where('user_id', $user->id)->get()->keyBy('step');

        DB::transaction(function () use ($request, $user, $progress, $tahapan, $buktiPerStep) {
            foreach ($tahapan as $i => $defaultKet) {
                $existing = $buktiPerStep->get($i);
                $bukti    = $existing ?? new Bukti(['user_id' => $user->id, 'step' => $i]);

                if ($request->hasFile("bukti{$i}")) {
                    $oldPath = $existing->path ?? null;
                    $path    = $request->file("bukti{$i}")->store('bukti', 'public');

                    if ($path === false) {
                        throw ValidationException::withMessages([
                            "bukti{$i}" => "Gagal mengunggah bukti untuk tahap {$i}.",
                        ]);
                    }

                    if ($oldPath) {
                        Storage::disk('public')->delete($oldPath);
                    }

                    $bukti->path = $path;
                }

                $bukti->status     = $request->input("status{$i}", 'hold');
                $bukti->tanggal    = $request->input("tanggal{$i}");
                $bukti->keterangan = $request->input("keterangan{$i}", $defaultKet);
                $bukti->save();

                $progress->{"status{$i}"}  = $bukti->status;
                $progress->{"tanggal{$i}"} = $bukti->tanggal;
            }

            $progress->save();
        });

        return back()->with('success',
            'Progress & Bukti berhasil diperbarui.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login.form')
            ->with('success', 'Berhasil logout');
    }
}
