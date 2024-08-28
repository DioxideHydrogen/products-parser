# Open Food Facts API

**RESTFUL API para os produtos da Open Food Facts.**

Tecnologias utilizadas:
- PHP 8.2
- Laravel 11
- MongoDB Data Cloud
- PHPUnit
  
## Como instalar o projeto

### Etapa 1 - Dependências

Instalar as dependências com o composer

```
composer install --require-dev
```

### Etapa 2 - Arquivo de ambiente

Configurar o arquivo .env

```
mv .env.example .env
```

Após renomear o arquivo deve-se configurar a URI do MongoDB

```bash
# ...
DB_CONNECTION=mongodb
DB_URI= ...
DB_DATABASE=openfoodfacts
#...
```

### Etapa 3 - Geração da chave da aplicação

Gerar a chave do app

```
php artisan key:generate
```

### Etapa 4 - Criação das tabelas

Rodar as migrations do laravel

```
php artisan migrate
```

### Etapa 5 - Execução dos testes

Execução dos testes

```
./vendor/bin/phpunit
```

### Etapa 6 (caso não esteja utilizando um Virtual Host)

Iniciar o projeto

```bash
php artisan serve
```



>  This is a challenge by [Coodesh](https://coodesh.com/)
