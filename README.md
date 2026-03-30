# LunchForge

LunchForge es una aplicación web para gestionar menús de almuerzo semanales en un packing de fruta. El proveedor publica alternativas por día, los trabajadores seleccionan su almuerzo y los perfiles de proveedor o superadministración revisan resúmenes diarios y semanales.

## Stack

- Laravel 12
- PHP 8.3+
- Inertia.js
- React + TypeScript
- Tailwind CSS + daisyUI con tema `cupcake`
- MySQL
- Laravel Socialite para Google OAuth
- Spatie Laravel Permission para roles y permisos
- Laravel Storage con disco `public`

## Requisitos

- PHP 8.3 o superior
- Composer
- Node.js 20 o superior
- npm
- MySQL 8 o superior

## Instalación

1. Instala dependencias PHP y frontend.

```bash
composer install
npm install
```

2. Crea el archivo de entorno.

```bash
cp .env.example .env
php artisan key:generate
```

3. Configura MySQL en `.env`.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lunchforge
DB_USERNAME=root
DB_PASSWORD=
```

4. Ejecuta migraciones y seeders demo.

```bash
php artisan migrate --seed
```

5. Crea el enlace simbólico para imágenes.

```bash
php artisan storage:link
```

6. Inicia la aplicación.

```bash
php artisan serve
npm run dev
```

## Variables de entorno relevantes

Además de la conexión MySQL, configura estas variables:

```env
APP_NAME=LunchForge
APP_URL=http://127.0.0.1:8000
FILESYSTEM_DISK=public

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
GOOGLE_ALLOWED_DOMAIN=greenex.cl
```

## Google OAuth

- El login con Google está restringido al dominio `greenex.cl`.
- Si una persona inicia sesión con Google y ya existe un usuario con ese correo, se vincula `google_id` si aún no estaba registrado.
- Si no existe, se crea automáticamente un usuario activo con rol `Worker`.
- Si la cuenta está inactiva, el acceso se bloquea.

Configura tu aplicación OAuth en Google con esta URL de callback:

```text
http://127.0.0.1:8000/auth/google/callback
```

## Roles y flujos

### Worker

- Ve el menú disponible por día.
- Selecciona una única alternativa por fecha.
- Puede cambiar su selección mientras la semana no esté cerrada y el día esté publicado.
- Revisa su historial de selecciones.

### Supplier

- Administra el menú semanal.
- Crea días y alternativas por día.
- Publica o mantiene borradores según estado.
- Sube imágenes de opciones al disco `public`.
- Revisa reportes diarios y semanales.

### SuperAdmin

- Gestiona usuarios.
- Asigna roles.
- Activa o desactiva cuentas.
- Revisa menús y reportes globales.

## Credenciales demo

Todos los usuarios demo usan la contraseña:

```text
password
```

Usuarios semilla:

- SuperAdmin: `admin@greenex.cl`
- Supplier: `supplier@greenex.cl`
- Worker ejemplo: `worker1@greenex.cl`

El seeder crea además 9 trabajadores adicionales, una semana demo publicada, 5 días, entre 2 y 4 alternativas por día y selecciones de ejemplo.

## Comandos útiles

```bash
php artisan test
npm run build
php artisan migrate:fresh --seed
php artisan storage:link
```

## Estructura principal

```text
app/
  Http/
  Models/
  Policies/
  Services/
database/
  factories/
  migrations/
  seeders/
resources/
  css/
  js/
    Components/
    Layouts/
    Pages/
routes/
```

## Notas de producción

- Usa `APP_DEBUG=false` en producción.
- Ejecuta `php artisan config:cache` y `php artisan route:cache` al desplegar.
- Asegura permisos sobre `storage/` y `bootstrap/cache/`.
- Configura un proveedor de correo real si vas a usar recuperación de contraseña fuera de desarrollo.
