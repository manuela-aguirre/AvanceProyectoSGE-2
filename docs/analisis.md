# Análisis de la Empresa

## 1. Datos Generales
- **Nombre:** LYRA
- **Giro del negocio:** Comercialización de productos de consumo y de oficina (tecnología, papelería, aseo, alimentos, ferretería y hogar). Compra la mercancía a proveedores, la almacena y la vende a clientes, con control del inventario en cada movimiento.
- **Tamaño:** Pequeña
- **Sistema:** SGE, un ERP web que integra inventario, clientes, ventas, proveedores y compras, con control de acceso por roles y permisos.

## 2. Procesos Clave

- **Compras:** LYRA abastece su inventario con proveedores como Mayorista Digital SAS, Papelería Mayorista, Químicos y Aseo Ltda. o Ferretería Central. Primero se registra una **orden de compra** (`OC-00001`) en estado *pendiente*. La orden **no mueve el inventario**: el stock solo sube cuando la mercancía llega a bodega. La recepción puede ser **total** o **parcial** (la orden pasa a *parcial* y luego a *recibida*) y nunca se puede recibir más de lo pedido. Una orden solo se anula si todavía no ha llegado mercancía. La orden se imprime en PDF.
- **Inventario:** Cada producto (`productos`) tiene código, precio, `stock`, categoría y proveedor. El stock **sube** al recibir una compra y **baja** al registrar una venta. Si una venta se anula, el stock se devuelve. Los productos con stock de 5 unidades o menos se marcan como stock bajo y se sugieren para reabastecer. Los productos, categorías, clientes y proveedores no se borran de forma definitiva: van a una **papelera** (Soft Delete) y se pueden restaurar.
- **Ventas:** Un vendedor registra una venta a un cliente con uno o varios productos. El sistema valida el stock, calcula subtotal, IVA (19 %) y total, asigna un número de factura secuencial (`FV-00001`), crea la cabecera y el detalle y descuenta el inventario, todo en **una sola transacción**. Si falta stock o un producto no tiene precio, no se registra nada. La factura se imprime en PDF y la venta puede anularse devolviendo el stock.
- **Seguridad y administración:** Los usuarios tienen roles según su puesto (administrador, vendedor y almacenista). Un usuario puede tener varios roles y elegir con cuál trabajar al iniciar sesión (rol activo), además de permisos directos. Desde Configuración el administrador gestiona usuarios, roles y la matriz de permisos.
- **Seguimiento (Dashboard):** Muestra indicadores según los permisos de cada usuario: ventas y compras del mes, órdenes por recibir, stock bajo, valor del inventario, clientes y proveedores activos y el Top 5 de productos más vendidos.

## 3. Cambios respecto al primer corte

| Primer corte | Segundo corte |
|---|---|
| CRUD de productos y categorías | Se añaden clientes, ventas (cabecera + detalle), proveedores y compras (cabecera + detalle) |
| Sin control de acceso | Spatie Permission: roles por puesto, permisos por módulo, rol activo y permisos directos |
| Eliminación definitiva | Soft Delete con papelera y restauración |
| Stock editado a mano | Stock que cambia por reglas: compra recibida (sube), venta (baja) y anulación (devuelve) |
| Sin documentos | PDF de factura de venta y de orden de compra |
| Sin indicadores | Dashboard con KPIs por permisos y Top 5 |
| Lógica en controladores | Servicios con `DB::transaction` (`SaleService`, `PurchaseService`, `AccessControlService`) |

## 4. Entidades Identificadas (Tablas)

| Tabla | Descripción |
|---|---|
| `users` | Usuarios del sistema (con `active`) |
| `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` | Roles y permisos (Spatie) |
| `categorias` | Categorías de productos |
| `productos` | Productos e inventario |
| `providers` | Proveedores |
| `purchases` | Órdenes de compra (cabecera) |
| `purchase_details` | Productos de cada orden de compra |
| `clients` | Clientes |
| `sales` | Ventas (cabecera) |
| `sale_details` | Productos de cada venta |



## 5. Roles del sistema

| Rol | Puesto | Responsabilidad |
|---|---|---|
| `admin` | Administrador | Todo el sistema y la Configuración (usuarios, roles, permisos) |
| `vendedor` | Vendedor | Consultar productos, gestionar clientes y registrar ventas |
| `almacenista` | Almacenista / compras | Gestionar productos y proveedores, registrar órdenes de compra y recibir mercancía |