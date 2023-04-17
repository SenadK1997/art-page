<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admins;
use App\Models\Product;
use App\Models\Tags;
use Illuminate\Support\Facades\Redirect;

class AdminController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $tags = Tags::all();
        return view('admin.dashboard', compact('products', 'tags'));
    }
    public function products()
    {
        $products = Product::all();
        return view ('/admin/product/products', compact('products'));
    }
    public function create()
    {
        return view ('admin/product/create');
    }
    public function edit($id)
    {
        // Retrieve the product with the given ID
        $product = Product::findOrFail($id);
        // $tags_id = [];
        // foreach ($product->tags as $tag){
        //     array_push($tags_id, $tag->pivot->tags_id);
        // }
        // dd($tags_id);
        $allTags = Tags::all();
        
        $currentTags = $product->tags->pluck('id')->toArray(); // store id

        $allTagsId = Tags::all()->pluck('id')->toArray();

        // dd(array_diff($allTagsId, $currentTags));
        $tagsIdToShow = array_diff($allTagsId, $currentTags);

        $tags = $allTags->whereIn('id', $tagsIdToShow);

        return view('admin.product.edit', compact('product', 'tags'));
    }
    public function update(Request $request, $id)
    {
        $newData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'amount' => 'required|integer|min:0',
            'url' => 'required|url',
            'price' => 'required|numeric|min:0',
            'selected_tags' => 'sometimes'
        ]);
            $product = Product::findOrFail($id);
            $product->title = $newData['title'];
            $product->description = $newData['description'];
            $product->amount = $newData['amount'];
            $product->url = $newData['url'];
            $product->price = $newData['price'];
            $product->save();
            $selectedTags = explode(',', $request->input('selected_tags', []));
                if(!empty($request->input('selected_tags'))) {
                    // Check currect tags
                    $currentTags = $product->tags->pluck('id')->toArray();
                    // Get new tags to attach
                    $tagsToAttach = array_diff($selectedTags, $currentTags);
                    // Attach difference
                    $product->tags()->attach($tagsToAttach);
                }
            return redirect()->route('admin.product.edit', $product->id);
    }
    public function delete($id) {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Uspjesno obrisan artikal',
            'reload' => true
        ]);
    }
    public function delete_tags($id, $name) {
        $product = Product::findOrFail($id);
        $product->tags()->detach($name);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Uspjesno obrisan artikal',
            'reload' => true
        ]);
    }

    public function showLoginForm()
    {
        return view('admin.login');
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        $credentials['password'] = bcrypt($credentials['password']);
        // dd($credentials['password']);
        // dd(Auth::guard('admins')->attempt($credentials));
        if (Auth::guard('admins')->attempt($credentials)) {
            return redirect()->intended(route('admin.dashboard'));
        }
    
        return redirect()->route('admin.login')
            ->withErrors(['username' => 'Invalid username or password'])
            ->withInput($request->except('password'));

        //     $credentials = $request->validate([
        //         'username' => ['required', 'string'],
        //         'password' => ['required', 'string'],
        //     ]);
        // $user = Admins::where('username', $credentials['username'])->first();
        // if (!$user || !$credentials['password'] === $user->password) {
        //     return redirect('/admin/login')->withErrors(['username' => 'Invalid username or password']);
        // }

        // if ($user && $credentials['password'] === $user->password) {
        //     Auth::guard('admins')->login($user);
        //     // dd(Auth::guard('admins')->user());
        //     return redirect()->route('admin.dashboard');
        // }        
    }

    // FOR TAGS IN WEB.PHP
    public function tags()
    {
        $tags = Tags::all();
        return view ('/admin/tag/tags', compact('tags'));
    }
    public function make()
    {
        return view ('admin/tag/create');
    }
    public function save(Request $request)
    {
        $newData = $request->validate([
            'name' => 'required|max:255',
        ]);
            $tag = new Tags;
            $tag->name = $newData['name'];
            $tag->save();

            return redirect()->route('admin.tag.tags');
    }
    public function remake($id)
    {
        $tag = Tags::findOrFail($id);
        return view('admin/tag/edit', compact('tag'));
    }
    public function update_tags(Request $request, $id)
    {
        $tag = Tags::findOrFail($id);
        $tag->name = $request->input('name');
        $tag->save();
        return redirect()->route('admin.tag.tags');
    }
    public function trash($id)
    {
        $tag = Tags::findOrFail($id);
        $tag->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Uspjesno obrisan tag',
            'reload' => true
        ]);
        return redirect()->route('admin.tag.tags');
    }
    public function logout()
    {
        Auth::guard('admins')->logout();

        return redirect()->route('admin.login');
    }
}

// public function login(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'username' => ['required', 'string'],
    //         'password' => ['required', 'string'],
    //     ]);
    //     $admin = new Admins([
    //         'username' => $request->input('username'),
    //         'password' => Hash::make($request->input('password')),
    //     ]);
    
    //     $admin->save();

    //     dd(Auth::guard('admins')->attempt($credentials));
    //     if (Auth::guard('admins')->attempt($credentials)) {
    //         return redirect()->route('admin.dashboard');
    //         return view('/about');
    //     }
    //     return back()->withErrors(['username' => 'Invalid username or password.'])->withInput();
    //     dd('sucmadik');
    // }

    // dd(Auth::guard('admins')->attempt($credentials));
        // if (Auth::guard('admins')->attempt($credentials)) {
        //     $user = Auth::guard('admins')->user();
        //     Auth::guard('admins')->login($user);
            
        //     if (Auth::guard('admins')->check()) {
                
        //         return redirect()->route('admin.dashboard');
        //     }
        // }
        // return back()->withErrors(['username' => 'Invalid username or password.'])->withInput();