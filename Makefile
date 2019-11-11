start: stop
	cd laradock; docker-compose up -d nginx mysql phpmyadmin redis workspace

stop:
	cd laradock; docker-compose down

bash:
	cd laradock; docker-compose exec workspace bash

status:
	cd laradock; docker-compose ps

build: stop
	cd laradock; docker-compose build nginx mysql phpmyadmin redis workspace redis-webui php-worker
