# Club MaiTeam - Panel Interno

Proyecto base en PHP para la gestion interna del club de patinaje artistico.
Los apoderados inscriben deportistas y estos toman clases por modalidades.
Un deportista puede tomar una o varias modalidades (por ejemplo, freeskating con profesora Maira y danza con Maite).
Cada modalidad tiene un costo mensual.
Ademas, cada apoderado paga una cuota de socio mensual fija de 3.000, independiente de la cantidad de deportistas.

## Planes y valores mensuales
Valores mensuales segun la frecuencia de clases:

- Freeskating: 3 veces por semana 80.000.
- Conito / principiante: 3 veces por semana 60.000.
- Freeskating: 4 veces por semana 100.000.
- Freeskating: 5 veces por semana 120.000.
- Danza: 80.000.
- Flex: 1 vez por semana 25.000.

## Entradas principales
- `index.php`: router simple por parametro `page`.
- `config/database.php`: ajustes locales de base de datos.
- `views/`: vistas base para los modulos principales.

## Conexion a base de datos
Define variables de entorno si necesitas cambiar credenciales:

```
DB_HOST=127.0.0.1
DB_NAME=maiteam
DB_USER=maiteam_app
DB_PASS=tu_password
```

Luego puedes usar `db()` desde cualquier archivo para obtener un `PDO`.

## Modelo de datos (base)
- Apoderados: responsables de pago.
- Deportistas: pertenecen a un apoderado.
- Coaches: dictan clases.
- Clases: cada clase pertenece a un deportista y un coach.
- Pagos: un apoderado paga a un coach por un periodo.
- pagos_clases: detalle de clases incluidas en un pago.
- Modalidades: costos mensuales por tipo de clase.
- Inscripciones: deportistas asociados a modalidades.
- Cuotas socios: cuota fija mensual por apoderado.

El esquema inicial esta en `interno/sql/schema.sql`.
