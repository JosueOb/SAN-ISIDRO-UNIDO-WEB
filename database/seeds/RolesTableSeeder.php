<?php

use Caffeinated\Shinobi\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Roles del sistema web
         */
        //Administrador
        $adminRole = Role::create([
            'name'=>'Administrador',
            'slug'=>'admin',
            'description'=> 'Rol administrativo del sistema',
            'mobile_app'=>false,
        ]);
        $adminRole->save();
        $adminRole->permissions()->attach([1,2,3,4,5,6,7,8,9,10,11,12,23,24,25,26,27,28]);
        //Directivo
        $directiveRole = Role::create([
            'name'=>'Directivo',
            'slug'=>'directivo',
            'description'=> 'Rol para los directivos del barrio',
            'mobile_app'=>false,
        ]);
        $directiveRole->save();
        $directiveRole->permissions()->attach([4,5,6,7,8,13,14,15,16,17,18,19,20,21,22,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,56,57,58,59,60,61]);
        //Moderador
        $moderatorRole = Role::create([
            'name'=>'Moderador',
            'slug'=>'moderador',
            'description'=> 'Rol para los moderadores del barrio',
            'mobile_app'=>false,
        ]);
        $moderatorRole->save();
        $moderatorRole->permissions()->attach([13,14,15,16,17,45,46,47,48,49,50,51,52,53,54,55]);

        /**
         * Roles de la aplicación móvil
         */
        //Morador
        Role::create([
            'name'=>'Morador',
            'slug'=>'morador',
            'description'=> 'Rol para los moradores afiliados del barrio',
            'mobile_app'=>true,
        ]);
        //Invitado
        Role::create([
            'name'=>'Invitado',
            'slug'=>'invitado',
            'description'=> 'Rol para los moradores del barrio',
            'mobile_app'=>true,
        ]);
        //Policia
        Role::create([
            'name'=>'Policia',
            'slug'=>'policia',
            'description'=> 'Rol para los policias del barrio',
            'mobile_app'=>true,
        ]);
    }
}
