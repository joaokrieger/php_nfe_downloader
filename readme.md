docker build -t sped-nfe .
docker run --name sped-nfe -p 80:80 -v c/downloadNFE:/var/www/html/ -it -d sped-nfe