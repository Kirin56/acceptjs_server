build:
	docker build -t acceptjs_server:latest .
run:
	docker run --rm -d -p 12443:443 -v /Users/kirin/Projects/acceptjs/server:/var/www/html --name acceptjs_server acceptjs_server:latest
stop:
	docker stop acceptjs_server