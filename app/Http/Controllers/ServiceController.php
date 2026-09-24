<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $q = Service::query();

        if ($search = $request->input('q')) {
            $q->where('name', 'like', "%{$search}%");
        }

        $services = $q->orderBy('name')->paginate(20)->withQueryString();

        return view('services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:120', 'unique:services,name'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $data['active'] = true;
        Service::create($data);

        return redirect()->route('services.index')
            ->with('ok', "Service '{$data['name']}' added.");
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:120', Rule::unique('services', 'name')->ignore($service->id)],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $data['active'] = $request->boolean('active');
        $service->update($data);

        return redirect()->route('services.index')
            ->with('ok', 'Service updated.');
    }
}