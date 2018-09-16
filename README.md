# RSPhp Core
A real simple Php Framework

http://rsphp.espino.info/

Un framework para Php verdaderamente simple.
Escribí este framework por que ningún otro se acoplaba a mis necesidades: Quería que fuera ligero y simple,
y que evitara o tuviese la capacidad mejor dicho, de eliminar la necesidad de código spaguetti.

Sus objetivos son:
* Simplicidad
* Mantenibilidad
* Eliminar uso de código spaguetti
* Ligereza
* Desarrollo rápido de aplicaciones

Su principal fortaleza es que es simple, es realmente simple, cualquiera puede entender y modificar su core, no hay
nada complicado en él y su utilización es simple también.

Puedes utilizarlo con el patrón de diseño MVC o sin él, simplemente importando sus librerías y no te obliga a trabajar
de una forma determinada.

Cuenta con las siguentes características:

* Manejo simple de Bases de datos
* Modelos como objetos ORM
* Generación dinámica de SQL en el lenguaje
* Vistas
* Controladores
* Encriptación
* Asistente para Fechas
* Push Notifications
* Asistente para la generación de Html
* Clase Input que engloba todas las entradas (get, post, etc)
* Registro de eventos
* Mailer
* Rutas
* Sesiones
* Cadenas
* Urls
* Validaciones de entrada de datos
* Fácil, rápido y simple desarrollo de aplicaciones Rest

La documentación completa la puedes encontrar en: http://rsphp.espino.info/

## Getting started

./rsphp app --name="My app's name"
./rsphp connection add
  --name=[connection name, if not specified the name is 'default']
  --driver=[mysql|pgsql|dblib|sqlsrv]
  --hostName=[hostname|ipaddress]
  --databaseName=[databaseName]
  --userName="[user name]"
  --password=[password]
./rsphp model create all-tables
./rsphp login create --users-table=[my user's table] --users-key-field=[by default is 'email'] --users-pwd-field=[by default is ¿pwd']
