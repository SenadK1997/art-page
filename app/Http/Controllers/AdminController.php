<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admins;
use App\Models\Product;
use App\Models\Tags;
use App\Models\Images;
use App\Models\Order;
use Illuminate\Console\View\Components\Alert;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Intervention\Image\Facades\Image;

use function App\Providers\cmToPx;

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
    public function store(Request $request)
    {
        $product = new Product;
        $product->title = $request->input('title');
        $product->description = $request->input('description');
        $product->amount = $request->input('amount');
        // Request od slika
        
        if ($request->file('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '_' . $image->getClientOriginalName();
            // $thumbName = uniqid() . '_' . $image->getClientOriginalName();
            $img = Image::make($image);
            $img->resize(1280, 720, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save(storage_path('app/public/images/' . $imageName), 80);
            $thumbImg = Image::make($image);
            $thumbImg->resize(426, 240, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumbImg->save(storage_path('app/public/thumbnail/' . $imageName), 80);
            // dd($imageName, $thumbName);
            $product->url = $imageName;
        }
        $product->save();

        return redirect()->route('admin.product.products');
    }
    public function edit($id)
    {
        // Retrieve the product with the given ID
        $product = Product::findOrFail($id);
        $imgs = Images::all();
        // dd($product->images);
        // Dimension Images
        $allImages = Images::all();
        $currentImages = $product->images->pluck('id')->toArray();
        $allImagesId = Images::all()->pluck('id')->toArray();
        $imagesIdToShow = array_diff($allImagesId, $currentImages);
        $images = $allImages->whereIn('id', $imagesIdToShow);
        // Tags
        $allTags = Tags::all();
        $currentTags = $product->tags->pluck('id')->toArray(); // store id
        $allTagsId = Tags::all()->pluck('id')->toArray();
        $tagsIdToShow = array_diff($allTagsId, $currentTags);
        $tags = $allTags->whereIn('id', $tagsIdToShow);

        return view('admin.product.edit', compact('product', 'tags', 'images', 'imgs'));
    }
    
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->title = $request->input('title');
        $product->description = $request->input('description');
        $product->amount = $request->input('amount');
        // image
        if ($request->file('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '_' . $image->getClientOriginalName();
            // $thumbName = uniqid() . '_' . $image->getClientOriginalName();
            $img = Image::make($image);
            $img->resize(1280, 720, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save(storage_path('app/public/images/' . $imageName), 80);
            $thumbImg = Image::make($image);
            $thumbImg->resize(426, 240, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumbImg->save(storage_path('app/public/thumbnail/' . $imageName), 80);
            // dd($imageName, $thumbName);
            $product->url = $imageName;
        }

        $product->save();
        $selectedTags = explode(',', $request->input('selected_tags', []));
            if(!empty($request->input('selected_tags'))) {
                // Check current tags
                $currentTags = $product->tags->pluck('id')->toArray();
                // Get new tags to attach
                $tagsToAttach = array_diff($selectedTags, $currentTags);
                // Attach difference
                $product->tags()->attach($tagsToAttach);
            }
            if (!empty($request->input('shownWidth') && $request->input('shownHeight') && $request->input('shownPrice'))) {
                $sirinaValues = $request->input('shownWidth');
                $duzinaValues = $request->input('shownHeight');
                $cijenaValues = $request->input('shownPrice');
                $images = [];
                foreach ($sirinaValues as $index => $sirinaValue) {
                    $duzinaValue = $duzinaValues[$index];
                    $cijenaValue = $cijenaValues[$index];
                    $image = new Images([
                        'width' => $sirinaValue,
                        'height' => $duzinaValue,
                        'price' => $cijenaValue,
                    ]);
                    $image->save();
                    $images[] = $image;
                }
                $image->save();
                $product->images()->saveMany($images);
            } else if (empty($product->images)) {
                return back()->with('error', 'Trebas dodati najmanje jednu dimenziju');
            }
            return redirect()->route('admin.product.edit', $product->id)->with('success', 'Product updated successfully');
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
    public function images()
    {
        $imgs = Images::all();
        
        return view ('admin/image/images', compact('imgs'));

    }
    public function createImage()
    {
        return view('admin/image/create');
    }
    public function saveImage(Request $request)
    {
        $imageWidth = $request->input('sirina');
        $imageHeight = $request->input('duzina');
        $imagePrice = $request->input('cijena');
        $imgs = new Images;
        $imgs->width = $imageWidth;
        $imgs->height = $imageHeight;
        $imgs->price = $imagePrice;
        $imgs->save();
        return back();
            // return redirect()->route('admin.image.images');
    }
    public function editImage($id)
    {
        $img = Images::findOrFail($id);
        return view('admin/image/edit', compact('img'));
    }
    public function remakeImage(Request $request, $id)
    {
        $img = Images::findOrFail($id);
        // Update the image dimensions here
        $imageWidth = $request->input('sirina');
        // dd($imageWidth);
        $imageHeight = $request->input('duzina');
        $imagePrice = $request->input('cijena');

        $img->width = $imageWidth;
        $img->height = $imageHeight;
        $img->price = $imagePrice;

        $img->save();

        return back();
    }
    public function deleteImages($id)
    {
        $img = Images::findOrFail($id);
        $img->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Uspjesno obrisan',
            'reload' => true
        ]);
        // return redirect()->route('admin.image.images');
    }
    public function orders()
    {
        $orders = Order::all();
        return view('admin/orders', compact('orders'));
    }
    public function updateStatus($id)
    {

        // dd('adasd');
        // Find the order by ID
        $order = Order::findOrFail($id);
        // dd($order);
        // Update the status to true (1)
        $order->status = !$order->status;
        $order->save();

        // You can also use the following query to update the status directly in the database:
        // DB::table('orders')->where('id', $id)->update(['status' => true]);

        return response()->json(['message' => 'Order status updated successfully']);
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