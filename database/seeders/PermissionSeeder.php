<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Module;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'LEER_USUARIOS',
            'CREAR_USUARIOS',
            'ACTUALIZAR_USUARIOS',
            'ARCHIVAR_USUARIOS',
            'RESTAURAR_USUARIOS',
            'ELIMINAR_USUARIOS',
            'LEER_ROLES',
            'CREAR_ROLES',
            'ACTUALIZAR_ROLES',
            'ARCHIVAR_ROLES',
            'RESTAURAR_ROLES',
            'ELIMINAR_ROLES',
            'LEER_PERMISOS',

            'LEER_OFICIOS',
            'CREAR_OFICIOS',
            'ARCHIVAR_OFICIOS',
            'RESTAURAR_OFICIOS',
            'ELIMINAR_OFICIOS',
            'COMPARTIR_OFICIOS',
            'COMENTAR_OFICIOS',
            'CANCELAR_OFICIOS',
            'APROBAR_OFICIOS',

            'LEER_ARCHIVO',
            'ELIMINAR_ARCHIVO',
            'DESCARGAR_ARCHIVO',
            'SUBIR_ARCHIVO',

            'LEER_COMENTARIO',
            'CREAR_COMENTARIO',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
