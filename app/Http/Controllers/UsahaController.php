<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ExpenseCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsahaController extends Controller
{
    public function showLogin()
    {
        return view('usaha.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages(['email' => 'Akun Anda dinonaktifkan. Hubungi pemilik usaha.']);
        }

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function showRegister()
    {
        return view('usaha.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|max:150|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        Auth::login(User::create($data + ['role' => 'owner', 'is_active' => true]));

        return redirect()->route('usaha.create')->with('success', 'Akun berhasil dibuat. Silakan lengkapi profil usaha.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function profil()
    {
        return view('usaha.profil', ['user' => auth()->user()->load('business')]);
    }

    public function profilUpdate(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id_user, 'id_user')],
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function passwordForm()
    {
        return view('usaha.password');
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'Kata sandi lama tidak sesuai.']);
        }

        $request->user()->update(['password' => $request->password]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }

    public function usahaCreate()
    {
        if (auth()->user()->id_business) {
            return redirect()->route('usaha.show');
        }

        return view('usaha.usaha-form', ['business' => new Business()]);
    }

    public function usahaStore(Request $request)
    {
        abort_if(auth()->user()->id_business, 403);

        $data = $this->validateBusiness($request);

        DB::transaction(function () use ($request, $data) {
            $user = $request->user();

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('logos', 'public');
            }

            $business = Business::create($data + ['id_owner' => $user->id_user]);
            $user->update(['id_business' => $business->id_business]);

            Unit::create(['id_business' => $business->id_business, 'unit_name' => 'pcs']);
            foreach (['Bahan', 'Gaji', 'Sewa', 'Listrik'] as $name) {
                ExpenseCategory::create(['id_business' => $business->id_business, 'category_name' => $name]);
            }
        });

        return redirect()->route('dashboard')->with('success', 'Profil usaha berhasil dibuat. Selamat datang!');
    }

    public function usahaShow()
    {
        return view('usaha.usaha', ['business' => auth()->user()->business]);
    }

    public function usahaEdit()
    {
        return view('usaha.usaha-form', ['business' => auth()->user()->business]);
    }

    public function usahaUpdate(Request $request)
    {
        $business = auth()->user()->business;
        $data     = $this->validateBusiness($request);

        if ($request->hasFile('logo')) {
            if ($business->logo) {
                Storage::disk('public')->delete($business->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $business->update($data);

        return redirect()->route('usaha.show')->with('success', 'Profil usaha berhasil diperbarui.');
    }

    private function validateBusiness(Request $request): array
    {
        return $request->validate([
            'business_name' => 'required|string|max:150',
            'business_type' => 'required|string|max:100',
            'address'       => 'nullable|string|max:1000',
            'phone'         => 'nullable|string|max:20',
            'logo'          => 'nullable|image|max:2048',
        ]);
    }

    public function userIndex(Request $request)
    {
        $users = User::where('id_business', $this->businessId())
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")))
            ->orderBy('role')->orderBy('name')
            ->paginate(10)->withQueryString();

        return view('usaha.pengguna', compact('users'));
    }

    public function userCreate()
    {
        return view('usaha.pengguna-form', ['user' => new User(['role' => 'cashier', 'is_active' => true])]);
    }

    public function userStore(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|max:150|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:owner,cashier',
        ]);

        User::create($data + ['id_business' => $this->businessId(), 'is_active' => true]);

        return redirect()->route('pengguna.index')->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function userShow(User $pengguna)
    {
        $this->ensureSameBusiness($pengguna);

        return view('usaha.pengguna-detail', ['user' => $pengguna->load('business')]);
    }

    public function userEdit(User $pengguna)
    {
        $this->ensureSameBusiness($pengguna);

        return view('usaha.pengguna-form', ['user' => $pengguna]);
    }

    public function userUpdate(Request $request, User $pengguna)
    {
        $this->ensureSameBusiness($pengguna);

        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($pengguna->id_user, 'id_user')],
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'role'     => 'required|in:owner,cashier',
        ]);

        if ($pengguna->is(auth()->user())) {
            $data['role'] = 'owner';
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $pengguna->update($data);

        return redirect()->route('pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function userToggle(User $pengguna)
    {
        $this->ensureSameBusiness($pengguna);

        if ($pengguna->is(auth()->user())) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        $pengguna->update(['is_active' => ! $pengguna->is_active]);

        return back()->with('success', 'Akun ' . ($pengguna->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    public function userDestroy(User $pengguna)
    {
        $this->ensureSameBusiness($pengguna);

        if ($pengguna->is(auth()->user())) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $pengguna->delete();

        return redirect()->route('pengguna.index')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    private function ensureSameBusiness(User $user): void
    {
        abort_unless($user->id_business === auth()->user()->id_business, 404);
    }
}
