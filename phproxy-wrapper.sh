#!/bin/sh

set -axupe

PROXY_PORT=4269
PACPORT=4270
LOCAL_IP=$(ip -o route get to 8.8.8.8 | sed -n 's/.*src \([0-9.]\+\).*/\1/p')

echo "Listening on ${LOCAL_IP}:${PROXY_PORT}"
echo "Autoconfiguration url : http://${LOCAL_IP}:${PACPORT}/"
$(dirname ${0})/bin/phproxy run 0.0.0.0:${PROXY_PORT} --pac 0.0.0.0:${PACPORT}:${LOCAL_IP}:${PROXY_PORT} "$@"
