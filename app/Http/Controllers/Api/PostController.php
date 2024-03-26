<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::latest()->paginate(5);
        return new PostResource(true, 'List data Posts', $posts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'=> 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'=> 'required',
            'content'=> 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());
        
        $post = Post::create([
            'image'=> $image->hashName(),
            'title'=> $request->title,
            'content'=> $request->content,
        ]);

        return new PostResource(true,'Data berhasil ditambah', $post);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::find($id);

        return new PostResource(true,'Detail data', $post);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        // if error
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        $post = Post::find($id);

        // if file has image keep or delete
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            Storage::delete('public/posts/'. basename($post->image));

            $post->update([
                'image'=> $image->hashName(),
                'title'=> $request->title,
                'content'=> $request->content
            ]);
        } else {
            $post->update([
                'title' => $request->title,
                'content'=> $request->content,
            ]);
        }

        return new PostResource(true,'Data berhasil diubah', $post);

    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::find($id);

        Storage::delete('public/posts/'. basename($post->image));

        $post->delete();

        return new PostResource(true,'Data telah dihapus', null);
    }
}
