#!/usr/bin/env bash
docker-compose exec --user docker web parallel -j 2 --verbose --lb ::: 'yarn dev-server' 'yarn pdf-watch'
