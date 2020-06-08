FROM mysql:8.0

RUN mkdir /var/log/mysql
RUN chown mysql:mysql /var/log/mysql

COPY ./docker/mysql/config/my.cnf /etc/mysql/conf.d/my.cnf
COPY ./docker/mysql/logs/error.log /var/log/mysql/error.log
COPY ./docker/mysql/logs/query.log /var/log/mysql/query.log
COPY ./docker/mysql/logs/slow.log /var/log/mysql/slow.log

USER mysql

CMD ["mysqld"]
