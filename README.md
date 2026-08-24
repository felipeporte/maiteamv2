# Club MaiTeam

Aplicacion PHP del sitio y panel interno del Club MaiTeam.

## Desarrollo local

Requisitos:

- Docker Desktop

Inicializa las variables locales y levanta los servicios:

```bash
cp .env.example .env
docker compose up -d --build
```

URLs locales:

- Sitio publico: http://localhost:8080/
- Panel interno: http://localhost:8080/interno/

MariaDB importa `maiteam.sql` solo al crear el volumen por primera vez.

Para detener los servicios:

```bash
docker compose down
```

Para eliminar tambien la base de datos local y volver a importar el SQL:

```bash
docker compose down -v
docker compose up -d --build
```

El ultimo comando elimina los datos guardados en la base local. No afecta la instancia de produccion.

## Produccion

En la instancia AWS el proyecto se mantiene con Git y la configuracion sensible vive en un archivo `.env` en la raiz del sitio.

Flujo de despliegue:

```bash
git pull --ff-only origin main
```

Si cambian tablas o columnas:

```bash
mysql -u <usuario> -p <base> < interno/migration/003_asistencia_clases.sql
```

El archivo `.env` no se versiona. Debe existir en cada servidor con las credenciales reales de la base de datos.

## Estructura

- `index.php`: sitio publico.
- `interno/`: panel PHP y modulos del club.
- `gala/`: modulo de gastos y pagos.
- `certificados/`: generacion de certificados.
- `interno/migration/`: migraciones de base de datos.
- `compose.yaml`: servicios locales PHP y MariaDB.

Los respaldos, exportaciones SQL, credenciales, recibos y datos de produccion estan excluidos de Git.
