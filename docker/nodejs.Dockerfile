FROM node:22.22.3

WORKDIR /var/www

ADD . /var/www

# RUN npm install

CMD tail -f /dev/null