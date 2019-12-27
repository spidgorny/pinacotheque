FROM php:7.3

WORKDIR /app

VOLUME .:/app

CMD ['php', '-S', 'locahost:80', 'router.php']
