all: install run

install:
	docker run --rm -v `pwd`:`pwd` -w `pwd` -t "wyrihaximusnet/php:7.3-zts-alpine3.9-dev" composer install

run:
	docker run --rm -p 7331:7331 -v `pwd`:`pwd` -w `pwd` -t "wyrihaximusnet/php:7.3-zts-alpine3.9" php server.php
