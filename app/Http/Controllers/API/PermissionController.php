<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class PermissionController extends BaseController
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
        $how=Permission::count();

        //ako je broj 500 koji oznacava prikaz sviju ili ako je broj veci od ukupnog broja prikazi samo max broj iz baze
        if($perPage==500 or $perPage>$how){
            $perPage = $how;
        }

        $all = Permission::where(function($query) use ($search){
            $query->where('name','LIKE',"%$search%")
                ->orWhereHas('roles', function ($query) use ($search) {
                    $query->where('name','LIKE',"%$search%");
                });
        })->withCount('roles')
            ->with('roles')
            ->orderBy($sortBy,$sortDesc)
            ->paginate($perPage);

        return $all;

    }



    public function index_all(){

        return Permission::select('id','name')->get();
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

        if(!$this->user->can('permission-create')){
            Log::alert('NEMATE OVLASTI ZA UNOS STAVKE.  Korisnik: ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
            return $this->sendResponseError('GRE??KA', 'Nemate dozvole. Obratite se Administratoru');
        }

        $this->validate($request,[
            'name' => 'required|unique:permissions|string|max:191',
            'roles' => 'sometimes|array|min:1',
        ],
            [
                'name.required' => 'Niste unijeli naziv!',
                'name.unique' => 'Odaberite drugi naziv. Taj naziv ve?? postoji!',
                'name.string' => 'Naziv mora biti tekst!',
                'name.max' => 'Naziv mo??e najvi??e sadr??avati :max slova!',
                'roles.required' => 'Niste odabrali razinu prava!',
                'roles.array' => 'Niste odabrali razinu prava!',
                'roles.min' => 'Minimalno morate odabrali :min razinu prava za dozvola koju unosite!',
            ]);

        $permission = new Permission($request->all());

        if ($permission->save()) {

            $message .= 'Uspje??no unesena dozvola "' . $request['name']. '"<br>';

            if($permission->syncRoles($request->roles)){
                $r=Role::select('name')->find($request->roles)->toArray();
                $roles= Arr::flatten($r);

                $message .= 'Uspje??no povezana dozvola s razinom prava"' . json_encode($roles). '"<br>';
            }

            Log::info($message.'Korisnik: ' .$this->name .' '.$this->last_name .' - ' .$this->email.'');

            return $this->sendResponse('USPJE??AN UNOS', $message);
        } else {
            $message .= 'Nauspjeh - Dozvola "' . $request['name']. '" nije unesena.<br>';
            Log::error('Nauspjeh - Dozvola "' . $request['name']. '" nije unesena.  Korisnik: ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
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
    public function update(Request $request, Permission $permission)
    {
        global $message;

        if(!$this->user->can('permission-edit')){
            Log::alert('NEMATE OVLASTI ZA A??URIRANJE STAVKE.  Korisnik: ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
            return $this->sendResponseError('GRE??KA', 'Nemate ovlasti. Obratite se Administratoru');
        }

        $this->validate($request,[
            'name' => 'required|unique:permissions,name,'.$permission->id.'|string|max:191',
            'roles' => 'required|array|min:1',
        ],
            [
                'name.required' => 'Niste unijeli naziv!',
                'name.unique' => 'Odaberite drugi naziv. Taj naziv ve?? postoji!',
                'name.string' => 'Naziv mora biti tekst!',
                'name.max' => 'Naziv mo??e najvi??e sadr??avati :max slova!',
                'roles.required' => 'Niste odabrali razinu/e prava!',
                'roles.array' => 'Niste odabrali razinu/e prava!',
                'roles.min' => 'Minimalno morate odabrali :min razinu prava kako bi dodjelili dozvolu!',
            ]);


        if ($permission->update($request->all())) {
            Log::info('Uspje??no a??urirana dozvola"' . $request['name']. '". Korisnik: ' .$this->name .' '.$this->last_name .' - ' .$this->email.'');

            $message .= 'Uspje??no a??urirana dozvola "' . $request['name']. '"<br>';

            if($permission->syncRoles($request->roles)){
                $r=Role::select('name')->find($request->roles)->toArray();
                $roles= Arr::flatten($r);

                //$message .= 'Uspje??no povezana dozvola s "' . $request['name']. '" razinom/nama prava"' . json_encode($permissions). '"<br>';
                Log::info('Uspje??no povezana dozvola s "' . $request['name']. '" razinom/nama prava"' . json_encode($roles). '');
            }

            return $this->sendResponse('A??URIRANO', $message);

        } else {
            $message .= 'Nauspjeh - Dozvola "' . $request['name']. '" nije a??urirana.<br>';
            Log::error('Nauspjeh - Dozvola "' . $request['name']. '" nije a??urirana.  Korisnik: ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
            return $this->sendResponseError('GRE??KA A??URIRANJA', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        if(!$this->user->can('permission-delete')){
            Log::alert('NEMATE OVLASTI ZA BRISANJE STAVKE.  Korisnik: ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
            return $this->sendResponseError('GRE??KA', 'Nemate ovlasti. Obratite se Administratoru');
        }

        $check = $permission->roles()->count();

        if($check != 0){
            return $this->sendResponseError('GRE??KA BRISANJA', 'Dozvola "'.$permission->name. '" se ne mo??e obrisati zbog restrikcija');

        }else {
            if ($permission->delete()) {
                Log::info('Uspje??no obrisana dozvola "' . $permission['name'] . '".  Korisnik : ' .$this->name .' '.$this->last_name.' - ' .$this->email.'');
                return $this->sendResponse('OBRISANO','Dozvola "'.$permission->name. '" je uspje??no obrisana');
            } else {
                Log::error('Nauspjeh - Dozvola "' . $permission['name'] . '" nije obrisana . Korisnik : '.$this->name .' '.$this->last_name.' - '.$this->email.'');
                return $this->sendResponseError('GRE??KA BRISANJA', 'Dozvola "'.$permission['name']. '" nije obrisana');
            }
        }
    }
}
