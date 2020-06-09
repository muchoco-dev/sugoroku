FROM mysql:8.0

COPY ./docker/mysql/config/my.cnf /etc/mysql/conf.d/my.cnf
COPY ./docker/mysql/logs/*.log /var/log/mysql/

RUN chmod 777 /var/log/mysql/error.log && \
  chmod 777 /var/log/mysql/query.log && \
  chmod 777 /var/log/mysql/slow.log
