CRUD generator
==============================

create crud files by:

    $ sqlite3 cache/demo.db < data/schema.sql
    $ chmod 666 cache/demo.db
    $ ./bin/cli gen all customers

nginx configuration example:

    server {
        listen 80;
        root /path/to/admin-gen/public;
        index index.php index.html index.htm;
        try_files $uri $uri/ @rewrite;

        location @rewrite {
            rewrite ^/(.*)$ /index.php?_url=/$1;
        }
        
        location ~ \.php {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index /index.php;
            include fastcgi_params;
            fastcgi_split_path_info       ^(.+\.php)(/.+)$;
            fastcgi_param PATH_INFO       $fastcgi_path_info;
            fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }

This project is inspired by [CRUD Admin Generator](http://crud-admin-generator.com/)
