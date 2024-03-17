#!/usr/bin/env bash
# set owner:group for required folders
#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

chown -R www-data:www-data \
  ${DIR}/../var/ \
  ${DIR}/../config/jwt/ \
  ${DIR}/../public/ \
  ${DIR}/../assets/js/

