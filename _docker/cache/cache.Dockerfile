FROM mongo:5.0.21
RUN mkdir /init
COPY _docker/cache/build_artifacts/cache.json /init/cache.json.gz
COPY _docker/cache/build_artifacts/connection.yaml /init/connection.yaml
COPY _docker/cache/build_artifacts/load.sh /docker-entrypoint-initdb.d/load.sh

