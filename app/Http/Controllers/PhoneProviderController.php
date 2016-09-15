<?php
/**
 * Created by PhpStorm.
 * User: cvgs
 * Date: 9/15/2016
 * Time: 3:33 PM
 */
namespace App\Http\Controllers;

use \DateTime;
use Illuminate\Http\Request;
use App\PhoneProvider;
use App\Lookup;

class PhoneProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $phoneProvider = PhoneProvider::paginate(10);
        return view('phoneProvider.index')->with('phoneProvider', $phoneProvider);
    }

    public function show($id)
    {
        $phoneProvider = PhoneProvider::find($id);
        return view('phoneProvider.show')->with('phoneProvider', $phoneProvider);
    }

    public function create()
    {
        $statusDDL = Lookup::where('category', '=', 'STATUS')->get()->pluck('description', 'code');

        return view('phoneProvider.create', compact('statusDDL'));
    }

    public function store(Request $data)
    {
        $this->validate($data, [
            'name'    => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
            'driver'          => 'required|string|max:255',
            'status'          => 'required',
            'remarks'         => 'required|string|max:255',

        ]);

        Truck::create([
            'name'    => $data['name'],
            'short_name' => $data['short_name'],
            'prefix'          => $data['prefix'],
            'status'          => $data['status'],
            'remarks'         => $data['remarks']
        ]);
        return redirect(route('db.master.phoneProvider'));
    }

    private function changeIsDefault()
    {

    }

    public function edit($id)
    {
        $phoneProvider= PhoneProvider::find($id);

        $statusDDL = Lookup::where('category', '=', 'STATUS')->get()->pluck('description', 'code');

        return view('phoneProvider.edit', compact('phoneProvider', 'statusDDL'));
    }

    public function update($id, Request $req)
    {
        PhoneProvider::find($id)->update($req->all());
        return redirect(route('db.master.PhoneProvider'));
    }

    public function delete($id)
    {
        PhoneProvider::find($id)->delete();
        return redirect(route('db.master.PhoneProvider'));
    }
}