## . Diccionario de Datos

### Tabla: clients
| Campo | Tipo | Descripción |
|---|---|---|
| id | BIGINT, PK | Identificador del cliente |
| document_type | ENUM(CC, NIT, CE, PP) | Tipo de documento, por defecto CC |
| document_number | VARCHAR(30), único | Número de documento |
| name | VARCHAR(150) | Nombre o razón social |
| email | VARCHAR(150), nullable | Correo electrónico |
| phone | VARCHAR(30), nullable | Teléfono |
| address | VARCHAR(200), nullable | Dirección |
| active | BOOLEAN, default true | Cliente activo |
| created_at, updated_at | TIMESTAMP | Control de registro |
| deleted_at | TIMESTAMP, nullable | Soft Delete (papelera) |

### Tabla: sales
| Campo | Tipo | Descripción |
|---|---|---|
| id | BIGINT, PK | Identificador de la venta |
| invoice_number | VARCHAR(20), único | Número de factura secuencial (FV-00001) |
| client_id | BIGINT, FK → clients.id | Cliente (restrict) |
| user_id | BIGINT, FK → users.id | Vendedor que registra la venta (restrict) |
| sale_date | DATE | Fecha de la venta |
| subtotal | DECIMAL(14,2) | Suma de las líneas |
| tax | DECIMAL(14,2) | IVA 19 % |
| total | DECIMAL(14,2) | Subtotal + IVA |
| status | ENUM(pendiente, pagada, anulada) | Estado, por defecto pagada |
| notes | TEXT, nullable | Observaciones |
| created_at, updated_at | TIMESTAMP | Control de registro |
| deleted_at | TIMESTAMP, nullable | Soft Delete (al anular) |

### Tabla: sale_details
| Campo | Tipo | Descripción |
|---|---|---|
| id | BIGINT, PK | Identificador del detalle |
| sale_id | BIGINT, FK → sales.id | Venta (cascade) |
| product_id | BIGINT, FK → productos.id | Producto vendido (restrict) |
| quantity | INTEGER | Cantidad vendida |
| unit_price | DECIMAL(12,2) | Precio unitario al momento de la venta |
| subtotal | DECIMAL(14,2) | quantity × unit_price |
| created_at, updated_at | TIMESTAMP | Control de registro |

### Tabla: providers
| Campo | Tipo | Descripción |
|---|---|---|
| id | BIGINT, PK | Identificador del proveedor |
| document_type | ENUM(NIT, CC, CE, PP) | Tipo de documento, por defecto NIT |
| document_number | VARCHAR(30), único | Número de documento |
| name | VARCHAR(150) | Razón social |
| contact_name | VARCHAR(150), nullable | Asesor comercial |
| email | VARCHAR(150), nullable | Correo electrónico |
| phone | VARCHAR(30), nullable | Teléfono |
| address | VARCHAR(200), nullable | Dirección |
| active | BOOLEAN, default true | Proveedor activo |
| created_at, updated_at | TIMESTAMP | Control de registro |
| deleted_at | TIMESTAMP, nullable | Soft Delete (papelera) |

### Tabla: purchases
| Campo | Tipo | Descripción |
|---|---|---|
| id | BIGINT, PK | Identificador de la orden |
| purchase_number | VARCHAR(20), único | Número de orden secuencial (OC-00001) |
| provider_id | BIGINT, FK → providers.id | Proveedor (restrict) |
| user_id | BIGINT, FK → users.id | Usuario que registra la orden (restrict) |
| purchase_date | DATE | Fecha de la orden |
| provider_invoice | VARCHAR(50), nullable | Factura del proveedor |
| subtotal, tax, total | DECIMAL(14,2) | Totales de la orden (IVA 19 %) |
| status | ENUM(pendiente, parcial, recibida, anulada) | Estado, por defecto pendiente |
| received_at | TIMESTAMP, nullable | Fecha en que se completó la recepción |
| notes | TEXT, nullable | Observaciones |
| created_at, updated_at | TIMESTAMP | Control de registro |
| deleted_at | TIMESTAMP, nullable | Soft Delete (al anular) |

### Tabla: purchase_details
| Campo | Tipo | Descripción |
|---|---|---|
| id | BIGINT, PK | Identificador del detalle |
| purchase_id | BIGINT, FK → purchases.id | Orden de compra (cascade) |
| product_id | BIGINT, FK → productos.id | Producto pedido (restrict) |
| quantity | INTEGER | Cantidad pedida |
| received_quantity | INTEGER, default 0 | Cantidad recibida hasta el momento |
| unit_cost | DECIMAL(12,2) | Costo unitario de compra |
| subtotal | DECIMAL(14,2) | quantity × unit_cost |
| created_at, updated_at | TIMESTAMP | Control de registro |

### Tabla: productos
| Campo | Tipo | Descripción |
|---|---|---|
| id | BIGINT, PK | Identificador del producto |
| nombre | VARCHAR(150) | Nombre del producto |
| descripcion | TEXT, nullable | Descripción |
| imagen_url | VARCHAR(255), nullable | Imagen |
| stock | INTEGER, default 0 | Unidades disponibles |
| precio | DECIMAL(12,2), nullable | Precio de venta |
| codigo | VARCHAR(20), nullable | Código interno (PRD-0001) |
| proveedor_id | BIGINT, FK → providers.id | Proveedor principal (restrict) |
| categoria_id | BIGINT, FK → categorias.id | Categoría |
| created_at, updated_at | TIMESTAMP | Control de registro |
| deleted_at | TIMESTAMP, nullable | Soft Delete (papelera) |

### Tabla: categorias
| Campo | Tipo | Descripción |
|---|---|---|
| id | BIGINT, PK | Identificador de la categoría |
| codigo | VARCHAR(20), nullable | Código (CAT-01) |
| nombre | VARCHAR(50) | Nombre de la categoría |
| created_at, updated_at | TIMESTAMP | Control de registro |
| deleted_at | TIMESTAMP, nullable | Soft Delete (papelera) |

### Tabla: users
| Campo | Tipo | Descripción |
|---|---|---|
| id | BIGINT, PK | Identificador del usuario |
| name | VARCHAR(255) | Nombre |
| tipo_identificacion | VARCHAR(10), nullable | Tipo de documento |
| numero_identificacion | VARCHAR(30), nullable | Número de documento |
| email | VARCHAR(255), único | Correo electrónico (usuario de acceso) |
| email_verified_at | TIMESTAMP, nullable | Verificación del correo |
| password | VARCHAR(255) | Contraseña encriptada |
| active | BOOLEAN, default true | Un usuario inactivo no puede iniciar sesión |
| remember_token | VARCHAR(100), nullable | Sesión persistente |
| created_at, updated_at | TIMESTAMP | Control de registro |
