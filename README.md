# SGE – Sistema de Gestión Empresarial (ERP)

**Asignatura:** Software de Gestión Empresarial – COTECNOVA 2026
**Empresa:** Lyra
**Integrantes:** Manuela Aguirre - Alejandra Díaz

---

## 1. Módulos del sistema

| Módulo | Qué hace |
|---|---|
| Productos y categorías | CRUD con búsqueda, paginación, papelera y restauración (Soft Delete) |
| Clientes | CRUD con filtros, papelera y restauración |
| Ventas | Cabecera + detalle, número secuencial `FV-00001`, descuento de stock, anulación con devolución de stock, factura en PDF |
| Proveedores | CRUD con papelera y restauración |
| Compras | Órdenes `OC-00001`, recepción total o parcial que aumenta el stock, anulación, orden en PDF |
| Dashboard | KPIs filtrados por permisos y Top 5 de productos más vendidos |
| Configuración | Usuarios (varios roles y permisos directos), roles, matriz de permisos y rol activo al iniciar sesión |

## 2. Requisitos

- Docker Desktop (con WSL2 si se trabaja en Windows)
- Git
- Composer y PHP 8.3 (opcional; ver el paso 2 si no se tienen)

## 3. Instalación desde cero

```bash
# 1. Clonar el repositorio y entrar a la carpeta
git clone <URL-DEL-REPOSITORIO> sge
cd sge

# (Opcional) ubicarse en la versión entregada del segundo corte
git checkout avance-2

# 2. Instalar dependencias de PHP (la carpeta vendor/ no viene en el repositorio)
composer install
#    Si no tienes Composer instalado, usa este contenedor temporal:
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php83-composer:latest composer install --ignore-platform-reqs

# 3. Copiar las variables de entorno (el archivo .env no viene en el repositorio)
cp .env.example .env

# 4. Levantar los contenedores
./vendor/bin/sail up -d

# 5. Generar la clave de la aplicación
./vendor/bin/sail artisan key:generate

# 6. Crear las tablas y cargar los datos de prueba
./vendor/bin/sail artisan migrate --seed

# 7. Instalar y compilar los assets (Tailwind / Vite)
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

Abrir <http://localhost> en el navegador.

> **Si ya existe una base de datos con datos y quieres reiniciarla:** `./vendor/bin/sail artisan migrate:fresh --seed`

> **Si el puerto 80 está ocupado:** agrega `APP_PORT=8080` en el `.env` y usa <http://localhost:8080>.

### Configuración de la base de datos en `.env`

Para Sail con MySQL, verifica que estas variables estén así:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=sge
DB_USERNAME=sail
DB_PASSWORD=password
```

## 4. Usuarios de prueba

Todos los usuarios tienen la contraseña **`password`**.

| Rol | Correo | Qué puede hacer |
|---|---|---|
| `admin` | `admin@test.com` | Todo, incluida la Configuración (usuarios, roles, permisos) |
| `vendedor` | `vendedor@test.com` | Ver productos; ver, crear y editar clientes; ver y crear ventas |
| `almacenista` | `almacenista@test.com` | Productos (ver, crear, editar), ver categorías, proveedores (ver, crear, editar), compras (ver, crear, recibir) |
| `vendedor` + `almacenista` | `supervisor@test.com` | Tiene **dos roles**: al iniciar sesión elige el rol activo. Además tiene el **permiso directo** `ver-reportes` |
| `admin` | `test@example.com` | Administrador creado por el `DatabaseSeeder` |

### Rol activo

Si un usuario tiene más de un rol (por ejemplo `supervisor@test.com`), después del login se le pide elegir con qué rol quiere trabajar. Esa elección se guarda en la sesión.

### Permisos

Los permisos siguen la convención `accion-modulo` (`ver-productos`, `recibir-compras`, etc.) y se administran desde **Configuración → Permisos** (matriz módulo × acción).

## 5. Flujo de prueba sugerido

1. Iniciar sesión como `almacenista@test.com`, crear una **orden de compra** a un proveedor.
2. **Recibir parcialmente** la orden: el stock del producto sube.
3. Intentar recibir **más de lo pedido**: el sistema lo rechaza.
4. Iniciar sesión como `vendedor@test.com` y registrar una **venta**: el stock baja y se genera el número `FV-xxxxx`.
5. Intentar vender **más que el stock disponible**: el sistema lo rechaza.
6. Abrir el **PDF** de la factura y de la orden de compra.
7. **Anular** la venta: el stock se devuelve.
8. Revisar el **Dashboard**: los KPIs reflejan los cambios.
9. Con `vendedor@test.com`, entrar a `/purchases`: responde **403** por falta de permiso.
10. Con `admin@test.com`, revisar **Configuración** y la matriz de permisos.

## 6. Datos de prueba (seeders)

`php artisan migrate --seed` ejecuta `DatabaseSeeder`, que llama a:

| Seeder | Contenido |
|---|---|
| `RolePermissionSeeder` | Permisos y roles `admin`, `vendedor`, `almacenista` |
| `ProviderSeeder` | Proveedores |
| `CategoriaSeeder` | Categorías |
| `ProductSeeder` | Productos con stock y precio |
| `CompraSeeder` | Órdenes de compra de ejemplo |
| `ClientSeeder` | Clientes |
| `TestUserSeeder` | Usuarios de prueba por rol (sección 4) |
| `SaleSeeder` | Ventas de ejemplo |


### Modelo de datos


| Tabla | Campos principales |
|---|---|
| `clients` | `document_type`, `document_number` (único), `name`, `email`, `phone`, `address`, `active`, `deleted_at` |
| `sales` | `invoice_number` (único, `FV-00001`), `client_id`, `user_id`, `sale_date`, `subtotal`, `tax`, `total`, `status`, `notes`, `deleted_at` |
| `sale_details` | `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal` |
| `providers` | `document_type`, `document_number` (único), `name`, `contact_name`, `email`, `phone`, `address`, `active`, `deleted_at` |
| `purchases` | `purchase_number` (único, `OC-00001`), `provider_id`, `user_id`, `purchase_date`, `provider_invoice`, `subtotal`, `tax`, `total`, `status` (pendiente / parcial / recibida / anulada), `received_at`, `deleted_at` |
| `purchase_details` | `purchase_id`, `product_id`, `quantity` (pedida), `received_quantity`, `unit_cost`, `subtotal` |

Cálculo: `subtotal = Σ (cantidad × precio)` · `IVA = subtotal × 0.19` · `total = subtotal + IVA`.


## 7. Seguridad

- **Spatie Laravel Permission:** roles por puesto de trabajo y permisos por módulo.
- **Middleware en controladores:** cada acción declara `permission:*`; sin permiso el sistema responde 403.
- **`@can` en vistas:** los botones y enlaces solo aparecen a quien tiene el permiso.
- **Rol activo:** `EnsureActiveRole` obliga a elegir rol a los usuarios con varios roles y cierra la sesión de usuarios desactivados.
- **Permisos directos:** se asignan a un usuario desde Configuración → Usuarios, además de los de sus roles.
- **Soft Delete:** productos, categorías, clientes, proveedores, ventas y compras se envían a la papelera y se pueden restaurar.

## 8. Dashboard

| KPI | Permiso requerido |
|---|---|
| Productos, stock bajo (≤ 5) y valor del inventario | `ver-productos` |
| Clientes activos | `ver-clientes` |
| Ventas del mes (cantidad y valor) | `ver-ventas` |
| Compras del mes y órdenes por recibir | `ver-compras` |
| Proveedores activos | `ver-proveedores` |
| Ventas − Compras del mes | `ver-reportes` |
| Top 5 productos más vendidos del mes | `ver-ventas` o `ver-reportes` |
| Productos para reabastecer | `crear-compras` |

## 9. Documentación y evidencias

- [`docs/avance2/`](docs/avance2/) – documento PDF del avance y capturas del segundo corte
- [`docs/analisis.md`](docs/analisis.md) – análisis del negocio
- [`docs/diccionario.md`](docs/diccionario.md) – diccionario de datos
- [`docs/diagrama_mer.jpeg`](docs/diagrama_mer.jpeg) – diagrama entidad-relación
- [`docs/clase10/capturas/`](docs/clase10/capturas/) – capturas de compras, PDF y dashboard

## 10. Comandos útiles

```bash
./vendor/bin/sail up -d                      # levantar contenedores
./vendor/bin/sail down                       # apagar contenedores
./vendor/bin/sail artisan migrate:fresh --seed   # reiniciar la base de datos
./vendor/bin/sail artisan route:list         # ver rutas
./vendor/bin/sail artisan tinker             # consola de Laravel
./vendor/bin/sail artisan view:clear         # limpiar caché de vistas
./vendor/bin/sail mysql                      # abrir MySQL (ver cambios de stock)
./vendor/bin/sail test                       # ejecutar pruebas
```

## 11. Tecnologías

Laravel 13 · PHP 8.3 · MySQL · Laravel Sail (Docker) · Blade · Tailwind CSS + Vite · Laravel Breeze · Spatie Laravel Permission · DomPDF (`barryvdh/laravel-dompdf`)

