<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Progress;
use App\Models\Bukti;
use App\Models\ProgressHistory;
use Throwable;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only([
            'index', 'admin', 'updateProgress', 'customers', 'customerShow',
            'tambahCustomer', 'lihatBukti',
        ]);
    }

    public function masuk()
    {
        return view('login');
    }

    public function logika_masuk(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'no_hp'    => 'nullable|string|max:20',
        ]);

        $pesanLoginGagal = fn () => ValidationException::withMessages([
            'login' => 'Email atau kata sandi salah.',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (! $user) {
            throw $pesanLoginGagal();
        }

        $adminPhone = config('maklon.admin_phone');
        $cocokAdmin = false;

        if ($adminPhone === null || $adminPhone === '') {
            Log::warning('ADMIN_PHONE kosong/tidak diatur: tidak ada admin yang diakui pada percobaan login ini.');
        } elseif ($request->filled('no_hp')) {
            $cocokAdmin = hash_equals((string) $adminPhone, (string) $request->input('no_hp'));
        }

        if ($user->password === null) {
            if (! $request->filled('no_hp')
                || ! hash_equals((string) $user->no_hp, (string) $request->input('no_hp'))) {
                throw $pesanLoginGagal();
            }

            $user->password = Hash::make($request->input('password'));
            $user->role     = $cocokAdmin ? 'admin' : 'user';
        } elseif (! Hash::check($request->input('password'), $user->password)) {
            throw $pesanLoginGagal();
        } elseif ($cocokAdmin && $user->role !== 'admin') {
            $user->role = 'admin';
        }

        if ($user->isDirty()) {
            $user->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'admin') {
            return redirect()->route('admin.index');
        }

        return redirect()->route('tracker.index');
    }

    public function tambahCustomer(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email|unique:users,email',
            'no_hp'    => 'required|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        $adminPhone = config('maklon.admin_phone');
        if ($adminPhone && hash_equals((string) $adminPhone, (string) $data['no_hp'])) {
            return back()->withErrors(['no_hp' => 'Nomor ini digunakan untuk akun admin. Gunakan nomor lain.'])->withInput();
        }

        User::create([
            'email'    => $data['email'],
            'no_hp'    => $data['no_hp'],
            'role'     => 'user',
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Customer ditambahkan.');
    }

    public function lihatBukti(Bukti $bukti)
    {
        abort_unless($bukti->path, 404);

        $userId = Auth::id();

        abort_unless(
            ($userId !== null && (int) $userId === (int) $bukti->user_id)
            || $this->adminAktif(),
            403
        );

        $relatif = str_starts_with($bukti->path, 'bukti/')
            ? substr($bukti->path, strlen('bukti/'))
            : $bukti->path;

        return Storage::disk('bukti')->response($relatif);
    }

    public function admin()
    {
        return $this->renderTracker(Auth::user());
    }

    public function customers()
    {
        $customers = User::orderByDesc('created_at')->get();

        // View membaca $c->progress, sedangkan relasi di model bernama
        // progresses(). Suntikkan hasil prefetch sebagai relasi ter-load
        // agar tanpa N+1 dan tanpa mengubah model/view.
        $progresses = Progress::whereIn('user_id', $customers->modelKeys())
            ->get()
            ->keyBy('user_id');

        $customers->each(function (User $customer) use ($progresses) {
            $customer->setRelation('progress', $progresses->get($customer->id));
        });

        return view('customers', compact('customers'));
    }

    public function customerShow(User $target)
    {
        return $this->renderTracker($target);
    }

    private function renderTracker(User $target)
    {
        $progress  = Progress::firstOrCreate(['user_id' => $target->id]);
        $buktiList = Bukti::where('user_id', $target->id)->get()->keyBy('step');

        return view('admin', [
            'user'      => $target,
            'progress'  => $progress,
            'buktiList' => $buktiList,
        ]);
    }

    public function index()
    {
        if ($this->adminAktif()) {
            return redirect()->route('admin.index');
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
        $user = Auth::user();

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
            $rules["keterangan{$i}"]   = 'nullable|string|max:255';
            $rules["uploaded_by{$i}"] = 'nullable|string|max:100';
            $rules["assigned_to{$i}"] = 'nullable|string|max:50';
            $rules["bukti{$i}"]       = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048';
        }
        $rules['user_id'] = 'nullable|integer|exists:users,id';
        $request->validate($rules);

        $targetId = $request->input('user_id') ?? $user->id;

        $progress = Progress::firstOrCreate(['user_id' => $targetId]);

        $buktiPerStep = Bukti::where('user_id', $targetId)->get()->keyBy('step');

        $statusAwal = [];
        foreach ($tahapan as $i => $defaultKet) {
            $statusAwal[$i] = $buktiPerStep->get($i)->status ?? null;
        }

        $fileBaru = [];
        $fileLama = [];

        try {
            DB::transaction(function () use ($request, $progress, $tahapan, $buktiPerStep, $targetId, $statusAwal, &$fileBaru, &$fileLama) {
                foreach ($tahapan as $i => $defaultKet) {
                    $existing = $buktiPerStep->get($i);
                    $bukti    = $existing ?? new Bukti(['user_id' => $targetId, 'step' => $i]);

                    if ($request->hasFile("bukti{$i}")) {
                        $tersimpan = $request->file("bukti{$i}")->store('', 'bukti');

                        if ($tersimpan === false) {
                            throw ValidationException::withMessages([
                                "bukti{$i}" => "Gagal mengunggah bukti untuk tahap {$i}.",
                            ]);
                        }

                        $fileBaru[] = $tersimpan;

                        if ($existing && $existing->path) {
                            $fileLama[] = $existing->path;
                        }

                        $bukti->path = 'bukti/' . $tersimpan;
                    }

                    $bukti->status      = $request->input("status{$i}", 'hold');
                    $bukti->tanggal     = $request->input("tanggal{$i}");
                    $bukti->keterangan  = $request->input("keterangan{$i}", $defaultKet);
                    $bukti->uploaded_by = $request->input("uploaded_by{$i}", $bukti->uploaded_by);
                    $bukti->assigned_to = $request->input("assigned_to{$i}", $bukti->assigned_to);
                    $bukti->save();

                    if ($bukti->status !== $statusAwal[$i]) {
                        ProgressHistory::create([
                            'user_id'     => $targetId,
                            'step'        => $i,
                            'status_lama' => $statusAwal[$i],
                            'status_baru' => $bukti->status,
                            'changed_by'  => Auth::id(),
                        ]);
                    }

                    $progress->{"status{$i}"}  = $bukti->status;
                    $progress->{"tanggal{$i}"} = $bukti->tanggal;
                }

                $progress->save();
            });
        } catch (Throwable $e) {
            foreach ($fileBaru as $relatif) {
                Storage::disk('bukti')->delete($relatif);
            }

            throw $e;
        }

        foreach ($fileLama as $lama) {
            $this->hapusBerkasBukti($lama);
        }

        return back()->with('success',
            'Progress & Bukti berhasil diperbarui.');
    }

    private function hapusBerkasBukti(?string $path): void
    {
        if (! $path) {
            return;
        }

        $diDiskPrivat = str_starts_with($path, 'bukti/')
            ? substr($path, strlen('bukti/'))
            : $path;

        Storage::disk('bukti')->delete($diDiskPrivat);

        if ($path !== $diDiskPrivat) {
            Storage::disk('public')->delete($path);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form')
            ->with('success', 'Berhasil logout');
    }

    private function adminAktif(): bool
    {
        $adminPhone = config('maklon.admin_phone');

        return ($adminPhone !== null && $adminPhone !== '')
            && hash_equals((string) $adminPhone, (string) Auth::user()->no_hp);
    }
}
