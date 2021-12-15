help:
	@echo "\033[0;33mhelp\t\t\t- show this menu\033[0m"
	@echo "\033[0;33mbuild\t\t\t- build project (from scratch; creates containers)\033[0m"
	@echo "\033[0;33mup\t\t\t- project containers up\033[0m"
	@echo "\033[0;33mdown\t\t\t- project containers down (deletes containers)\033[0m"
	@echo "\033[0;33mstart\t\t\t- start project containers\033[0m"
	@echo "\033[0;33mstop\t\t\t- stop project containers\033[0m"
	@echo "\033[0;33mto-php\t\t\t- enter php container\033[0m"
	@echo "\033[0;33mtest-unit\t\t- run unit tests (phpunit)\033[0m"

build:
	@echo "\n\033[1;mBuilding project\033[0m"
	@bash -c "docker-compose build --no-cache;"
	@bash -c "docker-compose up -d;"

up:
	@echo "\n\033[1;mProject up\033[0m"
	@bash -c "docker-compose up -d;"

down:
	@echo "\n\033[1;mProject down\033[0m"
	@bash -c "docker-compose down --remove-orphans;"

start:
	@echo "\n\033[1;mStarting project\033[0m"
	@bash -c "docker-compose start;"

stop:
	@echo "\n\033[1;mStopping project\033[0m"
	@bash -c "docker-compose stop;"

to-php:
	@echo "\n\033[1;mEntering php container\033[0m"
	@bash -c "docker-compose exec php-fpm bash;"

test:
	@echo "\n\033[1;mRunning unittests\033[0m"
	@bash -c "docker-compose exec php-fpm php bin/phpunit;"