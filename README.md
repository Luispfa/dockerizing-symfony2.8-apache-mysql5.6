Dockerizing Symfony Applications
================================


Step 1 :
-------
Detener los servicios en tu maquina local e Apache y Mysql, si los tuviras

 sudo service apache2 stop
--------------------------
 sudo service mysql stop
------------------------


Step 2 :
-------
Construir la imagenes DOCKER

 docker-compose build
 ---------------------


Step 3 :
-------
Levantar los servicios CADA VEZ QUE MODIFIQUEMOS CODIGO
 
 docker-compose up -d --remove-orphans
 -------------------------------------

Puedes usar 

 docker-compose up -d --remove-orphans --build
 ---------------------------------------------


COMANDOS MAS USADOS :
-------

Ingresar a la consola de las imagenes levantadas

1. docker exec -it sf28_apache bash
   --------------------------------
2. docker exec -it sf28_mysql bash
   -------------------------------


Step 5 :
-------
Para la imagen sf28_apache instalar dependencias

docker exec -it sf28_apache bash
--------------------------------

cd sf28
--------

COMPOSER_MEMORY_LIMIT=-1 composer install -n
--------------------------------------------

SUGERENCIAS
------------
Si queremos añadir una dependencia:

COMPOSER_MEMORY_LIMIT=-1 composer require symfony/assetic-bundle --prefer-dist
------------------------------------------------------------------------------


MODIFICACION HECHA ERROR TIMEZONE
---------------------------------

Entrar a /app/AppKernel.php y añadir el constructor

class AppKernel extends Kernel
{
    // Other methods and variables


    // Append this init function below

    public function __construct($environment, $debug)
    {
        date_default_timezone_set( 'Europe/Paris' );
        parent::__construct($environment, $debug);
    }

}
