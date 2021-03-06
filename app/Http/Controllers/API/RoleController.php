<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RoleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request['q'];
        $sortBy=$request['sort'];
        $sortDesc = $request['desc']== 'false' ? 'asc' : 'desc' ;
        $perPage = $request['perPage'];

        //ako nema broja po stranici stavi na najmanji
        if (empty($perPage)){
            $perPage=$this->pageOptions[0];
        }

        //ako nema sortiranja sortiraj po nazivu
        if(empty($sortBy)){
            $sortBy='name';
        }
        //provjeri koliko stavki ima u bazi
        $how=Role::count();

        //ako je broj 500 koji oznacava prikaz sviju ili ako je broj veci od ukupnog broja prikazi samo max broj iz baze
        if($perPage==500 or $perPage>$how){
            $perPage = $how;
        }

        $all = Role::where(function($query) use ($search){
            $query->where('name','LIKE',"%$search%")
                ->orWhereHas('permissions', function ($query) use ($search) {
                    $query->where('name','LIKE',"%$search%");
                });
        })->withCount('permissions')
            ->with('permissions')
            ->orderBy($sortBy,$sortDesc)
            ->paginate($perPage);

        return $all;
    }

    public function index_all(){

        return Role::select('id','name')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        global $message;

        if(!$this->user->can('role-create')){
            Log::alert('NEMATE OVLASTI ZA UNOS STAVKE.  Korisnik: ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
            return $this->sendResponseError('GRE??KA', 'Nemate ovlasti. Obratite se Administratoru');
        }

        $this->validate($request,[
            'name' => 'required|unique:roles|string|max:191',
            'permissions' => 'required|array|min:1',
        ],
            [
                'name.required' => 'Niste unijeli naziv!',
                'name.unique' => 'Odaberite drugi naziv. Taj naziv ve?? postoji!',
                'name.string' => 'Naziv mora biti tekst!',
                'name.max' => 'Naziv mo??e najvi??e sadr??avati :max slova!',
                'permissions.required' => 'Niste odabrali ovlasti!',
                'post_code.array' => 'Niste odabrali ovlasti!',
                'permissions.min' => 'Minimalno morate odabrali :min ovlasti za razinu prava!',
            ]);

        $role = new Role($request->all());

        if ($role->save()) {
            $message .= 'Uspje??no unesena razina prava "' . $request['name']. '"<br>';

            if($role->syncPermissions($request->permissions)){
                $p=Permission::select('name')->find($request->permissions)->toArray();
                $permissions= Arr::flatten($p);

                $message .= 'Uspje??no povezana razina prava s ovlastima"' . json_encode($permissions). '"<br>';
            }

            Log::info('Uspje??no unesena razina prava "' . $request['name']. '". Korisnik: ' .$this->name .' '.$this->last_name .' - ' .$this->email.'');

            return $this->sendResponse('USPJE??AN UNOS', $message);
        } else {
            $message .= 'Nauspjeh - Razina prava "' . $request['name']. '" nije unesena.<br>';
            Log::error('Nauspjeh - Razina prava "' . $request['name']. '" nije unesena.  Korisnik: ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
            return $this->sendResponseError('GRE??KA UNOSA', $message);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        global $message;

        if(!$this->user->can('role-edit')){
            Log::alert('NEMATE OVLASTI ZA A??URIRANJE STAVKE.  Korisnik: ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
            return $this->sendResponseError('GRE??KA', 'Nemate ovlasti. Obratite se Administratoru');
        }

        $this->validate($request,[
            'name' => 'required|unique:roles,name,'.$role->id.'|string|max:191',
            'permissions' => 'required|array|min:1',
        ],
            [
                'name.required' => 'Niste unijeli naziv!',
                'name.unique' => 'Odaberite drugi naziv. Taj naziv ve?? postoji!',
                'name.string' => 'Naziv mora biti tekst!',
                'name.max' => 'Naziv mo??e najvi??e sadr??avati :max slova!',
                'permissions.required' => 'Niste odabrali ovlasti!',
                'post_code.array' => 'Niste odabrali ovlasti!',
                'permissions.min' => 'Minimalno morate odabrali :min ovlasti za razinu prava!',
            ]);


        if ($role->update($request->all())) {
            Log::info('Uspje??no a??urirana razina prava "' . $request['name']. '". Korisnik: ' .$this->name .' '.$this->last_name .' - ' .$this->email.'');

            $message .= 'Uspje??no a??urirana razina prava "' . $request['name']. '"<br>';

            if($role->syncPermissions($request->permissions)){
                $p=Permission::select('name')->find($request->permissions)->toArray();
                $permissions= Arr::flatten($p);

                //$message .= 'Uspje??no povezana razina prava s "' . $request['name']. '" ovlastima"' . json_encode($permissions). '"<br>';
                Log::info('Uspje??no povezana razina prava s "' . $request['name']. '" ovlastima"' . json_encode($permissions). '');
            }

            return $this->sendResponse('A??URIRANO', $message);

        } else {
            $message .= 'Nauspjeh - Razina prava "' . $request['name']. '" nije a??urirana.<br>';
            Log::error('Nauspjeh - Razina prava "' . $request['name']. '" nije a??urirana.  Korisnik: ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
            return $this->sendResponseError('GRE??KA A??URIRANJA', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {

        if(!$this->user->can('role-delete')){
            Log::alert('NEMATE OVLASTI ZA BRISANJE STAVKE.  Korisnik: ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
            return $this->sendResponseError('GRE??KA', 'Nemate ovlasti. Obratite se Administratoru');
        }

        $check = $role->users()->count();

        if($check != 0){
            return $this->sendResponseError('GRE??KA BRISANJA', 'Razina prava "'.$role->name. '" se ne mo??e obrisati zbog restrikcija');

        }else {
            if ($role->delete()) {
                Log::info('Uspje??no obrisana razina prava "' . $role['name'] . '".  Korisnik : ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
                return $this->sendResponse('OBRISANO','Razina prava "'.$role->name. '" je uspje??no obrisana');
            } else {
                Log::error('Nauspjeh - Razina prava "' . $role['name'] . '" nije obrisana . Korisnik : '.$this->name .' '.$this->last_name.' - '.$this->email.'');
                return $this->sendResponseError('GRE??KA BRISANJA', 'Razina prava "'.$role['name']. '" nije obrisana');
            }
        }


    }
}
