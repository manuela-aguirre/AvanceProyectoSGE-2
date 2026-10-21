# SGE - Sistema de Gestión de Biblioteca COTECNOVA

Proyecto Laravel para la gestión del catálogo bibliográfico y la administración básica de una biblioteca universitaria. El sistema permite gestionar libros, editoriales, géneros y usuarios autenticados, con un módulo CRUD para la entidad principal y una base de datos estructurada para soportar procesos de catalogación y consulta.

## 1. Descripción del proyecto

La aplicación fue desarrollada como entregable del primer. Su objetivo principal es digitalizar y organizar el proceso de gestión del inventario bibliográfico, facilitando la administración del catálogo, la relación entre libros y sus categorías, y la operación básica de una biblioteca institucional.

## 2. Requisitos del sistema

- Docker Desktop
- WSL2 (si se trabaja desde Windows)
- Composer
- Laravel Sail
- Git

## 3. Instalación y configuración

```bash
# Clonar el repositorio
git clone <URL-del-repositorio>
cd sge

# Instalar dependencias de PHP
composer install

# Copiar variables de entorno
cp .env.example .env

# Iniciar contenedores con Sail
./vendor/bin/sail up -d

# Generar la clave de la aplicación
./vendor/bin/sail artisan key:generate

# Ejecutar migraciones y sembrar datos de prueba
./vendor/bin/sail artisan migrate:fresh --seed
```

Luego se puede acceder a la aplicación desde el navegador y crear una cuenta en /register. Si se desea entrar rápidamente, el seeder crea un usuario de prueba con la cuenta test@example.com.

## 4. Estructura del proyecto

El proyecto sigue el patrón MVC de Laravel y está organizado en las carpetas estándar del framework:

- app/ — modelos, controladores, componentes y lógica de negocio
- database/migrations/ — migraciones de la base de datos
- database/seeders/ — sembrado inicial de datos de prueba
- resources/views/ — vistas Blade
- routes/ — definiciones de rutas
- docs/ — análisis de negocio, diccionario de datos y diagramas

## 5. Entidades principales

| Entidad | Descripción |
|---|---|
| User | Usuarios de la aplicación y autenticación Laravel |
| Editorial | Casa editorial que publica los libros |
| Genero | Categoría o temática del libro |
| Libro | Entidad principal del sistema, representa el catálogo bibliográfico |

## 6. Relaciones del negocio

- Editorial tiene muchos Libro.
- Genero tiene muchos Libro.
- Cada Libro pertenece a una Editorial y a un Genero.

## 7. Módulo CRUD de libros

El sistema cuenta con un módulo completo para la administración del catálogo de libros.

### Rutas

```php
Route::resource('libros', LibroController::class);
```

### Controlador

El controlador principal es LibroController, y maneja las operaciones de:
- listado index
- creación create
- almacenamiento store
- edición edit
- actualización update
- eliminación destroy

### Vistas Blade

- resources/views/libros/index.blade.php
- resources/views/libros/create.blade.php
- resources/views/libros/edit.blade.php

### Eager loading

Se usa with(['editorial', 'genero']) para evitar el problema N+1 al listar libros y cargar sus relaciones.

### Scopes reutilizables

En el modelo Libro se definen scopes para reutilizar consultas:
- scopeBuscar($query, $texto)
- scopeConStock($query)

## 8. Sembrado de datos de prueba

Los seeders crean registros iniciales para apoyar la validación del negocio y del flujo CRUD.

```bash
./vendor/bin/sail artisan db:seed
```

## 9. Comandos útiles

```bash
# Ejecutar migraciones desde cero
./vendor/bin/sail artisan migrate:fresh --seed

# Ver estado de migraciones
./vendor/bin/sail artisan migrate:status

# Abrir el shell de Laravel
./vendor/bin/sail artisan tinker

# Revisar errores de la aplicación
./vendor/bin/sail artisan route:list
```

## 10. Documentación del entregable

- [docs/analisis.md](docs/analisis.md) — análisis del negocio y procesos clave
- [docs/diccionario.md](docs/diccionario.md) — diccionario de datos
- [docs/diagrama_mer.png](docs/diagrama_mer.png) — diagrama entidad-relación

## 11.Integración de módulos y Dashboard con KPIs

Se conectan los módulos existentes de productos y categorías con los módulos de clientes y ventas mediante transacciones y reglas de negocio, esto permite que al registrar una venta se descuente del inventario automáticamente, además, se construye un Dashboard con indicadores clave (KPIs).

### 11.1 ¿Qué se implementó? 

- **Módulo de Clientes:** CRUD con búsqueda, filtro por estado, paginación, papelera y restauración (Soft Delete).
- **Módulo de Ventas:** listado con filtros por texto y fechas, formulario de nueva venta con filas dinámicas (JavaScript), factura y anulación.
- **`SaleService`:** encapsula la lógica de la venta dentro de `DB::transaction`.
- **Regla inversa:** al anular una venta se devuelve el stock a los productos.
- **Dashboard con KPIs:** tarjetas de indicadores y Top 5 de productos más vendidos, filtrados por permisos.
- **Permisos nuevos:** clientes, ventas y reportes, asignados por rol.

### 11.2 Flujo de una venta

```
Vendedor → "Nueva venta" → selecciona cliente → agrega productos y cantidades
        → el formulario calcula subtotal, IVA y total en tiempo real
        → al guardar (todo dentro de UNA transacción):
              1. valida el stock de cada producto
              2. crea la venta (cabecera)  → tabla sales
              3. crea los detalles         → tabla sale_details
              4. descuenta el stock        → tabla productos
        → redirige a la factura con mensaje de éxito
        → el Dashboard refleja los nuevos datos
```

Si algún paso falla (por ejemplo, stock insuficiente), la venta se cancela, restaurando los datos, haciendo que no queden ventas huérfanas ni stock descontado sin venta.

### 11.3 Modelo de datos

```
Clients (1) ──< Sales (N) ──< SaleDetails (N) >── (1) Productos
Users   (1) ──< Sales (N)
```

| Tabla | Campos principales |
|---|---|
| `clients` | `document_type` (CC/NIT/CE/PP), `document_number` (único), `name`, `email`, `phone`, `address`, `active`, `deleted_at` |
| `sales` | `invoice_number` (único, `FV-00001`), `client_id`, `user_id`, `sale_date`, `subtotal`, `tax`, `total`, `status` (pendiente / pagada / anulada), `notes`, `deleted_at` |
| `sale_details` | `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal` |

Cálculo: `subtotal = Σ (cantidad × precio_unitario)` · `IVA = subtotal × 0.19` · `total = subtotal + IVA`.

### 11.4 Reglas de negocio

- No se puede vender más cantidad que el stock disponible (`SaleService`).
- No se puede vender un producto sin precio configurado (`SaleService` y `StoreSaleRequest`).
- La venta debe tener al menos un producto y cantidades enteras ≥ 1 (`StoreSaleRequest`).
- El cliente debe existir, estar activo y no estar en la papelera (`StoreSaleRequest`).
- Si un producto se repite en dos filas, las cantidades se suman antes de validar el stock.
- `lockForUpdate()` bloquea la fila del producto durante la transacción para que dos vendedores no vendan el mismo stock a la vez.
- Cada venta queda asociada a un cliente y al usuario vendedor autenticado.
- El número de factura es secuencial (`FV-00001`, `FV-00002`, …).
- Al anular una venta se devuelve el stock y la venta queda con `status = anulada` y *soft delete* (`SaleController::destroy`).
- No se puede eliminar un cliente que tenga ventas asociadas (`ClientController::destroy`).

Las reglas viven en servicios, controladores y Form Requests, no en las vistas.

### 11.5 Roles y permisos

| Rol | Permisos relevantes |
|---|---|
| `admin` | Todos |
| `vendedor` | ver-productos, ver/crear/editar-clientes, ver/crear-ventas |
| `almacenista` | ver/crear/editar-productos, ver-categorias, ver/crear-compras (no ve Clientes ni Ventas) |

Cada controlador declara su middleware `permission:*` y las vistas usan `@can(...)` para mostrar u ocultar enlaces.

### 11.6 Rutas

```php
Route::patch('clients/{client}/restore', [ClientController::class, 'restore'])
    ->withTrashed()->name('clients.restore');
Route::resource('clients', ClientController::class)->except('show');
Route::resource('sales', SaleController::class)
    ->only(['index', 'create', 'store', 'show', 'destroy']);
```

### 11.7 Dashboard con KPIs

| KPI | Permiso requerido |
|---|---|
| Productos registrados | `ver-productos` |
| Stock bajo (≤ 5 unidades) | `ver-productos` |
| Unidades en inventario | `ver-productos` |
| Clientes activos | `ver-clientes` |
| Ventas del mes (cantidad y valor) | `ver-ventas` |
| Top 5 productos más vendidos del mes | `ver-ventas` o `ver-reportes` |

Las tarjetas de acceso a cada módulo solo aparecen si el usuario tiene el permiso `ver-*` correspondiente.

### 11.8 Archivos principales de la clase

- Modelos: `Client`, `Sale`, `SaleDetail` (nuevos) y `Product` (relación `saleDetails`)
- Servicio: `app/Services/SaleService.php`
- Controladores: `ClientController`, `SaleController`, `DashboardController` (KPIs)
- Form Requests: `StoreClientRequest`, `UpdateClientRequest`, `StoreSaleRequest`
- Migraciones: `create_clients_table`, `create_sales_table`, `create_sale_details_table`
- Vistas: `resources/views/clients/*`, `resources/views/sales/*`, `dashboard.blade.php`, `layouts/app.blade.php` (`@stack('scripts')`) y `layouts/navigation.blade.php`
- Rutas: `routes/web.php`

### 11.9 Adaptaciones respecto a la guía

- La tabla de productos se llama `productos` y sus columnas están en español (`nombre`, `precio`, `stock`, `codigo`), por eso `sale_details.product_id` referencia `productos`.
- `productos` no tiene columna `active`; en lugar de validar "producto activo" se exige que el producto tenga precio.
- El KPI "Productos activos" pasó a **Productos registrados** y se agregó **Unidades en inventario**.
- El módulo de ventas anterior (`ventas`) fue reemplazado por el flujo `sales` / `sale_details`.


## 12. Clase 10 – Compras y PDF
 
Se completa el ciclo del inventario con los módulos de proveedores y compras: al registrar una orden de compra y recibir la mercancía, el stock aumenta automáticamente (total o parcialmente). Además se generan documentos en PDF (orden de compra y factura de venta) con DomPDF y se amplía el Dashboard con KPIs de compras.
 
### 12.1 ¿Qué se implementó?
 
- **Módulo de Proveedores:** CRUD con búsqueda, paginación, papelera y restauración (Soft Delete).
- **Módulo de Compras:** listado con filtros por texto, estado y fechas, formulario de nueva orden con filas dinámicas (JavaScript), detalle de la orden, recepción total o parcial y anulación.
- **`PurchaseService`:** encapsula la lógica de la compra dentro de `DB::transaction` (`register`, `receive` y `cancel`).
- **Regla clave:** pedir no es recibir; el stock solo aumenta al recibir la mercancía.
- **Reportes PDF (DomPDF):** orden de compra y factura de venta sobre una plantilla base compartida (`pdf/layout.blade.php`).
- **Dashboard con KPIs de compras:** tarjetas nuevas y tabla de productos para reabastecer, filtradas por permisos.
- **Permisos nuevos:** proveedores y `recibir-compras`, asignados por rol.
 
### 12.2 Modelo de datos
 
```
Providers (1) ──< Purchases (N) ──< PurchaseDetails (N) >── (1) Productos
Users     (1) ──< Purchases (N)
```
 
| Tabla | Campos principales |
|---|---|
| `providers` | `document_type` (NIT/CC/CE/PP), `document_number` (único), `name`, `contact_name`, `email`, `phone`, `address`, `active`, `deleted_at` |
| `purchases` | `purchase_number` (único, `OC-00001`), `provider_id`, `user_id`, `purchase_date`, `provider_invoice`, `subtotal`, `tax`, `total`, `status` (pendiente / parcial / recibida / anulada), `received_at`, `notes`, `deleted_at` |
| `purchase_details` | `purchase_id`, `product_id`, `quantity` (pedida), `received_quantity` (recibida), `unit_cost`, `subtotal` |
 
Cálculo: `subtotal = Σ (cantidad_pedida × costo_unitario)` · `IVA = subtotal × 0.19` · `total = subtotal + IVA`.
 
### 12.3 Reglas de negocio
 
- La orden debe tener un proveedor existente, activo y fuera de la papelera (`StorePurchaseRequest`).
- La orden debe tener al menos un producto, con cantidades enteras ≥ 1 y costo unitario mayor que cero (`StorePurchaseRequest`).
- Un producto no puede repetirse en dos filas de la misma orden (`distinct` en `StorePurchaseRequest`).
- La fecha de la orden no puede ser futura (`StorePurchaseRequest`).
- El número de orden es secuencial (`OC-00001`, `OC-00002`, …).
- El stock aumenta al recibir, no al crear la orden (`PurchaseService::receive`).
- Solo se reciben órdenes en estado pendiente o parcial (`PurchaseService`).
- No se puede recibir más de lo pedido: pendiente = pedida − recibida (`PurchaseService`).
- Una recepción debe traer al menos una unidad (`PurchaseService`).
- `lockForUpdate()` bloquea la orden durante la recepción para que dos almacenistas no registren la misma entrega a la vez.
- Solo se puede anular una orden pendiente; si ya llegó mercancía no se anula (`PurchaseService::cancel`). La orden anulada queda con `status = anulada` y *soft delete*.
- No se puede eliminar un proveedor que tenga compras asociadas (`ProviderController::destroy`).
Las reglas viven en servicios, controladores y Form Requests, no en las vistas.
 
### 12.4 Roles y permisos
 
| Rol | Permisos relevantes |
|---|---|
| `admin` | Todos |
| `vendedor` | Sin cambios (no ve Proveedores ni Compras; sí puede abrir el PDF de sus ventas) |
| `almacenista` | ver/crear/editar-proveedores, ver/crear/recibir-compras (no elimina proveedores, no anula compras y no abre el PDF de ventas) |
 
Se agrega el permiso `recibir-compras`, porque no siempre quien pide la mercancía es quien la recibe en bodega. Cada controlador declara su middleware `permission:*` y las vistas usan `@can(...)`. El PDF de la orden de compra requiere `ver-compras` y el de la factura requiere `ver-ventas`.
 
### 12.5 Rutas
 
```php
Route::patch('providers/{provider}/restore', [ProviderController::class, 'restore'])
    ->withTrashed()->name('providers.restore');
Route::resource('providers', ProviderController::class)->except('show');
 
Route::patch('purchases/{purchase}/receive', [PurchaseController::class, 'receive'])
    ->name('purchases.receive');
Route::get('purchases/{purchase}/pdf', [PurchaseController::class, 'pdf'])
    ->name('purchases.pdf');
Route::resource('purchases', PurchaseController::class)
    ->only(['index', 'create', 'store', 'show', 'destroy']);
 
Route::get('sales/{sale}/pdf', [SaleController::class, 'pdf'])->name('sales.pdf');
```
 
### 12.6 Dashboard con KPIs de compras
 
| KPI | Permiso requerido |
|---|---|
| Compras del mes (cantidad y valor) | `ver-compras` |
| Órdenes por recibir (pendientes o parciales) | `ver-compras` |
| Proveedores activos | `ver-proveedores` |
| Ventas − Compras del mes | `ver-reportes` |
| Tabla de productos para reabastecer | `crear-compras` |
 
Las tarjetas de Proveedores y Compras solo aparecen si el usuario tiene el permiso `ver-*` correspondiente.
 
### 12.7 Archivos principales de la clase
 
- Modelos: `Provider`, `Purchase`, `PurchaseDetail` (nuevos) y `Product` (relación `purchaseDetails`)
- Servicio: `app/Services/PurchaseService.php`
- Controladores: `ProviderController`, `PurchaseController`, `SaleController` (método `pdf`), `DashboardController`
- Form Requests: `StoreProviderRequest`, `UpdateProviderRequest`, `StorePurchaseRequest`, `ReceivePurchaseRequest`
- Migraciones: `create_providers_table`, `create_purchases_table`, `create_purchase_details_table`
- Seeders: `ProviderSeeder` y `RolePermissionSeeder` (permisos nuevos)
- Vistas: `resources/views/providers/*`, `resources/views/purchases/*`, `resources/views/pdf/*`, `dashboard.blade.php`, `layouts/navigation.blade.php`
- Rutas: `routes/web.php`
### 12.8 Adaptaciones respecto a la guía
 
- La tabla de productos se llama `productos` y sus columnas están en español, por eso `purchase_details.product_id` referencia `productos`.

### 12.9 Evidencias
 
| Evidencia | Archivo |
|---|---|
| Listado de Proveedores | `docs/clase_10/capturas/proveedores.png` |
| Formulario de nueva orden de compra con productos y totales | `docs/clase_10/capturas/compra_nueva.png` |
| Detalle de una orden en estado Parcial | `docs/clase_10/capturas/compra_parcial.png` |
| Error «No se puede recibir más de lo pedido» | `docs/clase_10/capturas/error_recepcion.png` |
| MySQL: compra, detalles y stock aumentado | `docs/clase_10/capturas/mysql_compra.png` |
| PDF de la orden de compra | `docs/clase_10/capturas/pdf_orden.png` |
| PDF de la factura de venta | `docs/clase_10/capturas/pdf_factura.png` |
| Dashboard con los KPIs nuevos | `docs/clase_10/capturas/dashboard_compras.png` |
 
![Proveedores](docs/clase_10/capturas/proveedores.png)
![Nueva orden de compra](docs/clase_10/capturas/compra_nueva.png)
![Orden parcial](docs/clase_10/capturas/compra_parcial.png)
![Error de recepción](docs/clase_10/capturas/error_recepcion.png)
![MySQL](docs/clase_10/capturas/mysql_compra.png)
![PDF orden de compra](docs/clase_10/capturas/pdf_orden.png)
![PDF factura](docs/clase_10/capturas/pdf_factura.png)
![Dashboard](docs/clase_10/capturas/dashboard_compras.png)
 
## 13. Tecnologías utilizadas
 
- Laravel
- Laravel Sail
- Docker
- MySQL
- Blade
- Bootstrap/Tailwind
- Laravel Breeze (autenticación)
- Spatie Laravel Permission (roles y permisos)
- DomPDF (barryvdh/laravel-dompdf, reportes PDF)
- Git