<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ExpenseCategory;
use App\Models\Level;
use App\Models\LevelMenu;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsahaController extends Controller
{
    public function showLogin()
    {
        return view('usaha.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && ! $user->is_active) {
            return back()->withInput()->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi pemilik usaha.']);
        }

        if (! Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return back()->withInput()->withErrors(['email' => 'Email atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function showRegister()
    {
        return view('usaha.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|max:150',
            'email'    => 'required|email|max:150|unique:users,email',
            'phone'    => 'nullable|max:20',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'is_active' => 1,
        ]);

        Auth::login($user);

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
        $user = User::with(['business', 'level'])->find(auth()->id());

        return view('usaha.profil', compact('user'));
    }

    public function profilUpdate(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'  => 'required|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id_user . ',id_user',
            'phone' => 'nullable|max:20',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

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
            'password'         => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi lama tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }

    public function usahaCreate()
    {
        if (auth()->user()->id_business) {
            return redirect()->route('usaha.show');
        }

        $business = new Business();

        return view('usaha.usaha-form', compact('business'));
    }

    public function usahaStore(Request $request)
    {
        if (auth()->user()->id_business) {
            abort(403);
        }

        $request->validate([
            'business_name' => 'required|max:150',
            'business_type' => 'required|max:100',
            'address'       => 'nullable|max:1000',
            'phone'         => 'nullable|max:20',
            'logo'          => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();

        $logo = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo')->store('logos', 'public');
        }

        DB::beginTransaction();

        $business = Business::create([
            'id_owner'      => $user->id_user,
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'address'       => $request->address,
            'phone'         => $request->phone,
            'logo'          => $logo,
        ]);

        $levelPemilik  = Level::create(['id_business' => $business->id_business, 'level_name' => 'Pemilik', 'is_owner' => 1]);
        $levelKaryawan = Level::create(['id_business' => $business->id_business, 'level_name' => 'Karyawan', 'is_owner' => 0]);

        LevelMenu::create(['id_level' => $levelKaryawan->id_level, 'menu' => 'item']);
        LevelMenu::create(['id_level' => $levelKaryawan->id_level, 'menu' => 'transaksi']);

        $user->update(['id_business' => $business->id_business, 'id_level' => $levelPemilik->id_level]);

        Unit::create(['id_business' => $business->id_business, 'unit_name' => 'pcs']);

        foreach (['Bahan', 'Gaji', 'Sewa', 'Listrik'] as $nama) {
            ExpenseCategory::create(['id_business' => $business->id_business, 'category_name' => $nama]);
        }

        DB::commit();

        return redirect()->route('dashboard')->with('success', 'Profil usaha berhasil dibuat. Selamat datang!');
    }

    public function usahaShow()
    {
        $business = auth()->user()->business;

        return view('usaha.usaha', compact('business'));
    }

    public function usahaEdit()
    {
        $business = auth()->user()->business;

        return view('usaha.usaha-form', compact('business'));
    }

    public function usahaUpdate(Request $request)
    {
        $request->validate([
            'business_name' => 'required|max:150',
            'business_type' => 'required|max:100',
            'address'       => 'nullable|max:1000',
            'phone'         => 'nullable|max:20',
            'logo'          => 'nullable|image|max:2048',
        ]);

        $business = auth()->user()->business;

        $data = [
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'address'       => $request->address,
            'phone'         => $request->phone,
        ];

        if ($request->hasFile('logo')) {
            if ($business->logo) {
                Storage::disk('public')->delete($business->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $business->update($data);

        return redirect()->route('usaha.show')->with('success', 'Profil usaha berhasil diperbarui.');
    }

    public function userIndex(Request $request)
    {
        $query = User::with('level')->where('id_business', $this->businessId());

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('email', 'like', '%' . $request->q . '%');
            });
        }

        $users = $query->orderBy('id_level')->orderBy('name')->paginate(10)->withQueryString();

        return view('usaha.pengguna', compact('users'));
    }

    public function userCreate()
    {
        $user   = new User(['is_active' => 1]);
        $levels = Level::where('id_business', $this->businessId())->orderBy('level_name')->get();

        return view('usaha.pengguna-form', compact('user', 'levels'));
    }

    public function userStore(Request $request)
    {
        $bid = $this->businessId();

        $request->validate([
            'name'     => 'required|max:150',
            'email'    => 'required|email|max:150|unique:users,email',
            'phone'    => 'nullable|max:20',
            'password' => 'required|min:6|confirmed',
            'id_level' => 'required|exists:levels,id_level,id_business,' . $bid,
        ]);

        User::create([
            'id_business' => $bid,
            'id_level'    => $request->id_level,
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'password'    => Hash::make($request->password),
            'is_active'   => 1,
        ]);

        return redirect()->route('pengguna.index')->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function userShow($id)
    {
        $user = User::with(['business', 'level'])->where('id_business', $this->businessId())->findOrFail($id);

        return view('usaha.pengguna-detail', compact('user'));
    }

    public function userEdit($id)
    {
        $user   = User::where('id_business', $this->businessId())->findOrFail($id);
        $levels = Level::where('id_business', $this->businessId())->orderBy('level_name')->get();

        return view('usaha.pengguna-form', compact('user', 'levels'));
    }

    public function userUpdate(Request $request, $id)
    {
        $bid  = $this->businessId();
        $user = User::where('id_business', $bid)->findOrFail($id);

        $request->validate([
            'name'     => 'required|max:150',
            'email'    => 'required|email|max:150|unique:users,email,' . $user->id_user . ',id_user',
            'phone'    => 'nullable|max:20',
            'password' => 'nullable|min:6|confirmed',
            'id_level' => 'required|exists:levels,id_level,id_business,' . $bid,
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        if ($user->id_user != auth()->id()) {
            $data['id_level'] = $request->id_level;
        }

        $user->update($data);

        return redirect()->route('pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function userToggle($id)
    {
        $user = User::where('id_business', $this->businessId())->findOrFail($id);

        if ($user->id_user == auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status akun berhasil diubah.');
    }

    public function userDestroy($id)
    {
        $user = User::where('id_business', $this->businessId())->findOrFail($id);

        if ($user->id_user == auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('pengguna.index')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    public function levelIndex()
    {
        $levels = Level::withCount('users')->with('menus')
            ->where('id_business', $this->businessId())
            ->orderByDesc('is_owner')->orderBy('level_name')
            ->get();

        return view('usaha.level', compact('levels'));
    }

    public function levelStore(Request $request)
    {
        $bid = $this->businessId();

        $request->validate([
            'level_name' => 'required|max:100|unique:levels,level_name,NULL,id_level,id_business,' . $bid,
        ]);

        $level = Level::create([
            'id_business' => $bid,
            'level_name'  => $request->level_name,
            'is_owner'    => 0,
        ]);

        return redirect()->route('level.edit', $level->id_level)->with('success', 'Level dibuat. Sekarang pilih menu yang boleh dibuka.');
    }

    public function levelEdit($id)
    {
        $level = Level::with('menus')->where('id_business', $this->businessId())->findOrFail($id);

        if ($level->is_owner) {
            return redirect()->route('level.index')->with('error', 'Level pemilik selalu boleh membuka semua menu.');
        }

        $menus = config('menu.bisa_diatur');

        return view('usaha.level-form', compact('level', 'menus'));
    }

    public function levelUpdate(Request $request, $id)
    {
        $bid   = $this->businessId();
        $level = Level::where('id_business', $bid)->findOrFail($id);

        if ($level->is_owner) {
            abort(403);
        }

        $kodeMenu = implode(',', array_keys(config('menu.bisa_diatur')));

        $request->validate([
            'level_name' => 'required|max:100|unique:levels,level_name,' . $level->id_level . ',id_level,id_business,' . $bid,
            'menu'       => 'nullable|array',
            'menu.*'     => 'in:' . $kodeMenu,
        ]);

        $level->update(['level_name' => $request->level_name]);

        LevelMenu::where('id_level', $level->id_level)->delete();

        if ($request->menu) {
            foreach ($request->menu as $kode) {
                LevelMenu::create(['id_level' => $level->id_level, 'menu' => $kode]);
            }
        }

        return redirect()->route('level.index')->with('success', 'Level berhasil disimpan.');
    }

    public function levelDestroy($id)
    {
        $level = Level::where('id_business', $this->businessId())->findOrFail($id);

        if ($level->is_owner) {
            return back()->with('error', 'Level pemilik tidak bisa dihapus.');
        }

        if ($level->users()->count() > 0) {
            return back()->with('error', 'Level tidak bisa dihapus karena masih dipakai pengguna.');
        }

        $level->delete();

        return redirect()->route('level.index')->with('success', 'Level berhasil dihapus.');
    }
}
