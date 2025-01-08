#!/bin/bash

if [ $# -ne 2 ]; then
    echo "Usage: $0 <worker-id> <worker-access-token>"
    exit 1
fi

curl -H "X-Worker-Id: $1" -H "X-Worker-Token: $2" http://localhost/api/task/request-task
