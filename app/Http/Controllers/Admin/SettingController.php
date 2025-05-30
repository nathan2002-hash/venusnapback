<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Continent;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function countries()
    {
        $countries = Country::orderBy('created_at', 'desc')->paginate(190);
        $continents = Continent::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.settings.country', [
           'countries' => $countries,
           'continents' => $continents,
        ]);
    }

    public function continents()
    {
        $continents = Continent::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.settings.continent', [
           'continents' => $continents,
        ]);
    }

    public function storeCountry(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:countries,code',
        ]);

        $country = new Country();
        $country->name = $request->name;
        $country->code = $request->code;
        $country->sample_phone = $request->sample_phone;
        $country->phone_number_length = $request->phone_number_length;
        $country->continent_id = $request->continent_id;
        $country->capital = $request->capital;
        $country->currency = $request->currency;
        $country->currency_code = $request->currency_code;
        $country->flag = $request->flag;
        $country->description = $request->description;
        $country->phone_code = $request->phone_code;
        $country->save();
        return redirect('/admin/settings/countries')->with('success', 'Country created successfully.');
    }


    public function storeContinent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:continents,code',
        ]);

        $continent = new Continent();
        $continent->name = $request->name;
        $continent->code = $request->code;
        $continent->save();
        return redirect('/admin/settings/continents')->with('success', 'Continent created successfully.');
    }
}
