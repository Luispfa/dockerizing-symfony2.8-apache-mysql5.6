Symfony Standard Edition
========================

Clonar el repositorio

luego, desde la raiz del proyecto por consola hacer: 

composer install -n



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


apt-get update apt-get install git

Si da este error cuando quieras instalar dependecias dentro del contenedor, si no, no usar

Memory limit error to run composer into container

php -d memory_limit=-1 /usr/local/bin/composer install -n 
COMPOSER_MEMORY_LIMIT=-1 composer require symfony/assetic-bundle --prefer-dist


permisos a cache y logs

chmod 777 -R app/cache/ app/logs/
