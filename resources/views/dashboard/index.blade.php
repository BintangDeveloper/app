@extends('_layouts.master')

@section('body')
    <div class="mb-8">
        <h3 class="text-gray-200 text-4xl font-semibold">Dashboard</h3>
        <p class="text-gray-400 mt-2">Welcome back to your dashboard!</p>
    </div>
    
    <div class="flex flex-col mt-6 p-4 bg-gray-800 rounded-lg shadow-md">
        <h4 class="text-xl text-gray-300 font-semibold">User Information</h4>
        <div class="mt-2 text-gray-400">
            <p><span class="font-medium text-gray-300">Name:</span> {{ Auth::user()->name }}</p>
            <p><span class="font-medium text-gray-300">Email:</span> {{ Auth::user()->email }}</p>
        </div>
    </div>
@endsection

